<?php
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/repositories/AdminRepository.php';

class AdminController
{
    private AdminRepository $repo;

    public function __construct()
    {
        $this->repo = new AdminRepository();
    }

    // Show login form
    public function login(): void
    {
        require __DIR__ . '/../views/admin/login.php';
    }

    // Handle login
    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                echo "Username and password are required.";
                return;
            }

            try {
                $admin = $this->repo->findByUsername($username);

                if (!$admin) {
                    echo "No admin account found.";
                    return;
                }

                if (!password_verify($password, $admin['password'])) {
                    echo "Incorrect password.";
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                unset($admin['password']); // never store plain password

                $_SESSION['admin'] = [
                    'admin_id'   => $admin['admin_id'],
                    'username'   => $admin['username'],
                    'firstName'  => $admin['firstName'],
                    'middleName' => $admin['middleName'],
                    'lastName'   => $admin['lastName'],
                    'createdAt'  => $admin['created_at']
                ];

                header("Location: index.php?controller=Admin&action=showDashboard");
                exit;
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            $this->login();
        }
    }

    // Logout
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /Kislap/views/admin/login.php");
        exit;
    }

    public function showDashboard()
    {
        $pending = $this->repo->getPendingApplicationsCount('pending');
        $accepted = $this->repo->getAcceptedApplicationsCount('accepted');
        $rejected = $this->repo->getRejectedApplicationsCount('rejected');
        $userCount = $this->repo->getUserCount();

        require 'views/admin/dashboard.php';
    }

    public function viewPendingApplications() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            header("Location: /Kislap/views/admin/login.php");
            exit;
        }

        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $applications = $this->repo->getPendingApplications($limit, $offset, $search);
        $totalApplications = $this->repo->getPendingCount($search);
        $totalPages = ceil($totalApplications / $limit);

        require 'views/admin/application.php';
    }

    public function handleApplicationAction() {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';
        $applicationId = $data['application_id'] ?? 0;

        if (!$applicationId || !in_array($action, ['approve','reject'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $status = $action === 'approve' ? 'accepted' : 'rejected';
        try {
            $this->repo->updateApplicationStatus($applicationId, $status);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['application_id'] ?? null;
            $action = $data['status'] ?? null;

            if (!$id || !$action) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing application_id or status']);
                exit;
            }

            $status = $action === 'approve' ? 'ACCEPTED' : 'REJECTED';

            try {
                $this->repo->updateApplicationStatus($id, $status);

                if ($status === 'ACCEPTED') {
                    $application = $this->repo->findApplicationById($id);
                    if ($application) {
                        $workerData = [
                            'application_id' => $application['application_id'],
                            'lastName'       => $application['lastName'] ?? '',
                            'firstName'      => $application['firstName'] ?? '',
                            'middleName'     => $application['middleName'] ?? '',
                            'email'          => $application['email'] ?? '',
                            'phoneNumber'    => $application['phoneNumber'] ?? '',
                            'password'       => $application['password'] ?? null,
                            'address'        => $application['address'] ?? null,
                            'specialty'      => $application['service_type'] ?? ''
                        ];

                        $workerId = $this->repo->insertWorker($workerData);

                        if ($workerId) {
                            $this->insertWorkerWorks($application['application_id'], $workerId);
                            echo json_encode(['success' => true, 'message' => 'Worker inserted and works copied']);
                            exit;
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Failed to insert worker']);
                            exit;
                        }
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Application status updated']);
            } catch (Exception $e) {
                http_response_code(500);
                error_log($e->getMessage()); // log the error instead of printing it
                echo json_encode(['success' => false, 'message' => 'Server error']);
            }

            exit;
        }

        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    private function insertWorkerWorks(string $applicationId, int $workerId)
    {
        $works = $this->repo->getApplicationWorks($applicationId);

        if (empty($works)) {
            error_log("No works found for application ID: $applicationId");
            return;
        }

        $targetDir = __DIR__ . "/../uploads/workers/{$workerId}/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($works as $index => $work) {
            // Check if worksFilePath exists
            if (empty($work['worksFilePath'])) {
                error_log("Empty worksFilePath for work_id: " . ($work['work_id'] ?? 'unknown'));
                continue;
            }

            // Build source path - adjust this based on your actual file structure
            $source = __DIR__ . "/../" . $work['worksFilePath'];

            if (!file_exists($source)) {
                error_log("Source file does not exist: $source");
                continue;
            }

            // Get extension from worksFilePath, not image_path
            $ext = pathinfo($work['worksFilePath'], PATHINFO_EXTENSION);
            $newFileName = $this->generateWorkerWorkFileName($workerId, $index + 1, $ext);
            $newPath = $targetDir . $newFileName;

            // Copy the file
            if (!copy($source, $newPath)) {
                error_log("Failed to copy $source to $newPath");
                continue;
            }

            // Insert into DB using relative path
            $dbPath = "uploads/workers/{$workerId}/{$newFileName}";
            $result = $this->repo->insertWorkerWork($workerId, $dbPath);

            if (!$result) {
                error_log("Failed to insert worker work into DB: worker_id=$workerId, path=$dbPath");
            } else {
                error_log("Successfully inserted work: $dbPath"); // Success log for debugging
            }
        }
    }

    private function generateWorkerWorkFileName(int $workerId, int $index, string $ext): string
    {
        return "worker{$workerId}_work{$index}." . $ext;
    }


    public function applications()
    {
        $adminRepo = new AdminRepository();

        $search = $_GET['search'] ?? '';

        // This calls the repo method that handles DB logic
        $applications = $adminRepo->getApplications($search);

        require 'views/admin/application.php';
    }




}
