<?php
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/repositories/AdminRepository.php';

class AdminController
{
    private AdminRepository $repo;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        $this->repo = new AdminRepository();
    }

    // ========================================
    // AUTHENTICATION
    // ========================================
    
    public function login(): void
    {
        require __DIR__ . '/../views/admin/login.php';
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Validator.php';
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (empty($username) || empty($password)) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Username and password are required.'
                ];
                header("Location: index.php?controller=Admin&action=login");
                exit;
            }

            $usernameValidation = Validator::validateUsername($username);
            if (!$usernameValidation['valid']) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Invalid username format.'
                ];
                header("Location: index.php?controller=Admin&action=login");
                exit;
            }

            try {
                $admin = $this->repo->findByUsername($username);

                if (!$admin) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Invalid username or password.'
                    ];
                    header("Location: index.php?controller=Admin&action=login");
                    exit;
                }

                if (!password_verify($password, $admin['password'])) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Invalid username or password.'
                    ];
                    header("Location: index.php?controller=Admin&action=login");
                    exit;
                }

                unset($admin['password']);

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
                error_log("Admin login error: " . $e->getMessage());
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Login failed. Please try again.'
                ];
                header("Location: index.php?controller=Admin&action=login");
                exit;
            }
        } else {
            $this->login();
        }
    }

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

    // ========================================
    // DASHBOARD
    // ========================================

    public function showDashboard()
    {
        $pending = $this->repo->getPendingApplicationsCount();
        $approved = $this->repo->getApprovedWorkersCount();
        $rejected = $this->repo->getRejectedApplicationsCount();
        
        $userCount = $this->repo->getUserCount();
        $totalUsers = $this->repo->getTotalUsers();
        $totalWorkers = $this->repo->getTotalWorkers();

        $activeBookings = $this->repo->getActiveBookingsCount();
        $completedToday = $this->repo->getCompletedBookingsTodayCount();
        $avgRating = $this->repo->getAverageRating();
        $growthRate = $this->repo->getBookingGrowthRate();
        
        $totalEarnings = $this->repo->getTotalEarnings();

        require 'views/admin/dashboard.php';
    }

    // ========================================
    // WORKER MANAGEMENT
    // ========================================
    
    public function viewApprovedWorkers()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            header("Location: /Kislap/views/admin/login.php");
            exit;
        }

        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $statusFilter = $_GET['status'] ?? 'all';

        $workers = $this->repo->getApprovedWorkers($limit, $offset, $search, $statusFilter);
        $totalWorkers = $this->repo->getApprovedWorkersCount($search, $statusFilter);
        $totalPages = ceil($totalWorkers / $limit);

        $statusCounts = [
            'all' => $this->repo->getApprovedWorkersCount('', 'all'),
            'active' => $this->repo->getApprovedWorkersCount('', 'active'),
            'suspended' => $this->repo->getApprovedWorkersCount('', 'suspended'),
            'banned' => $this->repo->getApprovedWorkersCount('', 'banned')
        ];

        require 'views/admin/approved_workers.php';
    }

    public function handleWorkerAction()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $workerId = $data['worker_id'] ?? null;
        $action = $data['action'] ?? null;

        if (!$workerId || !in_array($action, ['suspend', 'ban', 'activate'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid worker_id or action']);
            exit;
        }

        $statusMap = [
            'suspend' => 'suspended',
            'ban' => 'banned',
            'activate' => 'active'
        ];
        $newStatus = $statusMap[$action];
        $actionTitle = ucfirst($action);

        try {
            $success = $this->repo->updateWorkerStatus($workerId, $newStatus);

            if ($success) {
                echo json_encode(['success' => true, 'message' => "Worker ID #{$workerId} has been successfully {$actionTitle}d."]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update worker status in database.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    // ========================================
    // APPLICATION MANAGEMENT
    // ========================================
    
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
            $rejectionReason = $data['rejection_reason'] ?? null;

            if (!$id || !$action) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing application_id or status']);
                exit;
            }

            // Handle different status updates
            if ($action === 'pending') {
                $status = 'pending';
            } else {
                $status = $action === 'approve' ? 'ACCEPTED' : 'REJECTED';
            }

            try {
                // Update status with rejection reason if provided
                if ($status === 'REJECTED' && $rejectionReason) {
                    $this->repo->updateApplicationStatusWithReason($id, $status, $rejectionReason);
                } else {
                    $this->repo->updateApplicationStatus($id, $status);
                }

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

    public function deleteApplication() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['application_id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing application_id']);
                exit;
            }

            try {
                $success = $this->repo->deleteApplication($id);
                
                if ($success) {
                    echo json_encode(['success' => true, 'message' => 'Application deleted permanently']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                error_log($e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Server error']);
            }

            exit;
        }

        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    // ========================================
    // ADMIN MANAGEMENT
    // ========================================
    
    public function createAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            header("Location: /Kislap/views/admin/login.php");
            exit;
        }

        require 'views/admin/create_admin.php';
    }

    public function handleCreateAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            header("Location: /Kislap/views/admin/login.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Validator.php';
            
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['firstName'] ?? '');
            $middleName = trim($_POST['middleName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            $errors = [];

            $usernameValidation = Validator::validateUsername($username);
            if (!$usernameValidation['valid']) {
                $errors[] = $usernameValidation['message'];
            }

            $firstNameValidation = Validator::validateName($firstName, 'first name');
            if (!$firstNameValidation['valid']) {
                $errors[] = $firstNameValidation['message'];
            }

            $lastNameValidation = Validator::validateName($lastName, 'last name');
            if (!$lastNameValidation['valid']) {
                $errors[] = $lastNameValidation['message'];
            }

            if (!empty($middleName)) {
                $middleNameValidation = Validator::validateName($middleName, 'middle name');
                if (!$middleNameValidation['valid']) {
                    $errors[] = $middleNameValidation['message'];
                }
            }

            $passwordValidation = Validator::validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors[] = $passwordValidation['message'];
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            if (!empty($errors)) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Please fix the following errors: ' . implode(', ', $errors)
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Admin&action=createAdmin");
                exit;
            }

            $existingAdmin = $this->repo->findByUsername($username);
            if ($existingAdmin) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Username already exists. Please choose a different username.'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Admin&action=createAdmin");
                exit;
            }

            try {
                $adminData = [
                    'username' => Validator::sanitizeInput($username),
                    'firstName' => Validator::sanitizeInput($firstName),
                    'middleName' => Validator::sanitizeInput($middleName),
                    'lastName' => Validator::sanitizeInput($lastName),
                    'password' => $password
                ];

                $this->repo->signUp($adminData);

                unset($_SESSION['form_data']);

                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Admin account created successfully!'
                ];
                header("Location: index.php?controller=Admin&action=showDashboard");
                exit;

            } catch (Exception $e) {
                error_log("Admin creation error: " . $e->getMessage());
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error creating admin account. Please try again.'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Admin&action=createAdmin");
                exit;
            }
        } else {
            $this->createAdmin();
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
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
            if (empty($work['worksFilePath'])) {
                error_log("Empty worksFilePath for work_id: " . ($work['work_id'] ?? 'unknown'));
                continue;
            }

            $source = __DIR__ . "/../" . $work['worksFilePath'];

            if (!file_exists($source)) {
                error_log("Source file does not exist: $source");
                continue;
            }

            $ext = pathinfo($work['worksFilePath'], PATHINFO_EXTENSION);
            $newFileName = $this->generateWorkerWorkFileName($workerId, $index + 1, $ext);
            $newPath = $targetDir . $newFileName;

            if (!copy($source, $newPath)) {
                error_log("Failed to copy $source to $newPath");
                continue;
            }

            $dbPath = "uploads/workers/{$workerId}/{$newFileName}";
            $result = $this->repo->insertWorkerWork($workerId, $dbPath);

            if (!$result) {
                error_log("Failed to insert worker work into DB: worker_id=$workerId, path=$dbPath");
            } else {
                error_log("Successfully inserted work: $dbPath");
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

    public function viewRejectedApplications()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['admin'])) {
            header("Location: /Kislap/views/admin/login.php");
            exit;
        }

        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $applications = $this->repo->getRejectedApplications($limit, $offset, $search);
        $totalApplications = $this->repo->getRejectedApplicationsCount($search);
        $totalPages = ceil($totalApplications / $limit);

        require 'views/admin/rejected_applications.php';
    }

    // ========================================
    // BOOKING MANAGEMENT
    // ========================================
    
    public function bookings()
    {
        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $status = $_GET['status'] ?? 'all';
        
        $stmt = $chatRepo->conn->prepare(
            "SELECT c.conversation_id, c.booking_status, c.created_at,
                    u.firstName as user_first, u.lastName as user_last,
                    w.firstName as worker_first, w.lastName as worker_last,
                    atb.event_type, atb.event_date, atb.budget, atb.final_price,
                    atb.deposit_paid, atb.deposit_amount
             FROM conversations c
             LEFT JOIN user u ON c.user_id = u.user_id
             LEFT JOIN workers w ON c.worker_id = w.worker_id
             LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
             WHERE c.booking_status != 'pending_ai'
             " . ($status != 'all' ? "AND c.booking_status = ?" : "") . "
             ORDER BY c.created_at DESC"
        );
        
        if ($status != 'all') {
            $stmt->execute([$status]);
        } else {
            $stmt->execute();
        }
        
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/admin/bookings.php';
    }

}
