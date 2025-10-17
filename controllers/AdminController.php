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
            $id = $_POST['application_id'] ?? null;
            $action = $_POST['status'] ?? null;

            if (!$id || !$action) {
                echo "Missing data.";
                return;
            }

            // Convert to valid enum
            $status = strtoupper($action === 'approve' ? 'ACCEPTED' : 'REJECTED');

            // Update application status
            $this->repo->updateApplicationStatus($id, $status);

            // If approved, transfer applicant data to workers
            if ($status === 'ACCEPTED') {
                $application = $this->repo->findApplicationById($id);

                if ($application) {
                    $this->repo->insertWorker([
                        'lastName'      => $application['lastName'],
                        'firstName'     => $application['firstName'],
                        'middleName'    => $application['middleName'],
                        'email'         => $application['email'],
                        'phoneNumber'   => $application['phoneNumber'],
                        'password'      => $application['password'], // already hashed
                        'address'       => $application['address'],
                        'created_at'    => date('Y-m-d H:i:s')
                    ]);
                }
            }

            header("Location: index.php?controller=Admin&action=viewPendingApplications");
            exit;
        }

        echo "Invalid request.";
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
