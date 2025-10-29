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
        header("Location: /Kislap/index.php?controller=Admin&action=login");
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
                            'specialty'      => '' // Set empty since application table doesn't have service_type
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
        $status = $_GET['status'] ?? 'all';
        $bookings = $this->repo->getAllBookings($status);
        
        require 'views/admin/bookings.php';
    }

    // ========================================
    // WORKER SUSPENSION MANAGEMENT
    // ========================================

    public function suspendWorker()
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
        $reason = trim($data['reason'] ?? '');
        $duration = $data['duration'] ?? null; // in hours, null for permanent
        $durationType = $data['duration_type'] ?? 'hours'; // hours, days, weeks, permanent

        if (!$workerId || empty($reason)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Worker ID and reason are required']);
            exit;
        }

        try {
            require_once __DIR__ . '/../model/repositories/WorkerRepository.php';
            $workerRepo = new WorkerRepository();

            $suspendedUntil = null;
            if ($durationType !== 'permanent' && $duration > 0) {
                $suspendedUntil = $this->calculateSuspensionEndTime($duration, $durationType);
            }

            $adminId = $_SESSION['admin']['admin_id'];
            $success = $workerRepo->suspendWorker($workerId, $reason, $suspendedUntil, $adminId);

            if ($success) {
                // Force logout the suspended worker if they're currently logged in
                $this->forceLogoutWorker($workerId);
                
                // Handle affected bookings
                $affectedBookings = $this->handleWorkerSuspensionBookings($workerId, $reason, $suspendedUntil);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Worker suspended successfully',
                    'suspended_until' => $suspendedUntil,
                    'affected_bookings' => $affectedBookings
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to suspend worker']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function unsuspendWorker()
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

        if (!$workerId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Worker ID is required']);
            exit;
        }

        try {
            require_once __DIR__ . '/../model/repositories/WorkerRepository.php';
            $workerRepo = new WorkerRepository();

            $success = $workerRepo->reactivateWorker($workerId);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Worker unsuspended successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to unsuspend worker']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    private function calculateSuspensionEndTime(int $duration, string $durationType): string
    {
        $now = new DateTime();
        
        switch ($durationType) {
            case 'hours':
                $now->add(new DateInterval("PT{$duration}H"));
                break;
            case 'days':
                $now->add(new DateInterval("P{$duration}D"));
                break;
            case 'weeks':
                $days = $duration * 7;
                $now->add(new DateInterval("P{$days}D"));
                break;
            default:
                throw new InvalidArgumentException("Invalid duration type: $durationType");
        }
        
        return $now->format('Y-m-d H:i:s');
    }

    private function handleWorkerSuspensionBookings(int $workerId, string $reason, ?string $suspendedUntil): array
    {
        try {
            // Get all active bookings for this worker
            $affectedBookings = $this->getActiveBookingsForWorker($workerId);
            if (empty($affectedBookings)) {
                return ['count' => 0, 'message' => 'No active bookings affected'];
            }

            $processedBookings = 0;
            $totalRefunds = 0;

            foreach ($affectedBookings as $booking) {
                // Cancel the booking
                $this->cancelBookingDueToSuspension($booking, $reason, $suspendedUntil);
                
                // Process refund if deposit was paid
                $depositPaid = ($booking['deposit_paid'] == 1 || $booking['deposit_paid'] === '1' || $booking['deposit_paid'] === true);
                $depositAmount = floatval($booking['deposit_amount'] ?? 0);
                
                if ($depositPaid && $depositAmount > 0) {
                    $this->processDepositRefund($booking);
                    $totalRefunds += $depositAmount;
                }
                
                // Send AI message to chat
                $this->sendSuspensionNotificationMessage($booking, $reason, $suspendedUntil);
                
                // Send email to client
                $this->sendBookingCancellationEmail($booking, $reason, $suspendedUntil);
                
                $processedBookings++;
            }

            return [
                'count' => $processedBookings,
                'total_refunds' => $totalRefunds,
                'message' => "Processed {$processedBookings} bookings, refunded $" . number_format($totalRefunds, 2)
            ];

        } catch (Exception $e) {
            error_log("Error handling suspension bookings: " . $e->getMessage());
            return ['count' => 0, 'error' => 'Failed to process affected bookings'];
        }
    }

    private function getActiveBookingsForWorker(int $workerId): array
    {
        return $this->repo->getActiveBookingsForWorker($workerId);
    }

    private function cancelBookingDueToSuspension(array $booking, string $reason, ?string $suspendedUntil): void
    {
        $this->repo->cancelBookingDueToSuspension($booking, $reason, $suspendedUntil);
    }

    private function processDepositRefund(array $booking): void
    {
        $this->repo->processDepositRefund($booking);
    }

    private function sendSuspensionNotificationMessage(array $booking, string $reason, ?string $suspendedUntil): void
    {

        
        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();

        $suspensionType = $suspendedUntil ? 'temporarily suspended' : 'suspended';
        $timeInfo = $suspendedUntil ? " until " . date('F j, Y', strtotime($suspendedUntil)) : '';
        
        $message = "🚨 **BOOKING CANCELLATION NOTICE** 🚨\n\n";
        $message .= "I regret to inform you that your photographer has been {$suspensionType}{$timeInfo} by our platform administration.\n\n";
        $message .= "**What this means for your booking:**\n";
        $message .= "• Your booking has been automatically cancelled\n";
        
        $message .= "• Any deposits paid will be refunded within 3-5 business days\n";
        
        $message .= "• You can book with other photographers on our platform\n";
        $message .= "• Our support team is available to help you find alternatives\n\n";
        $message .= "**Next Steps:**\n";
        $message .= "1. Check your email for detailed cancellation information\n";
        $message .= "2. Browse other photographers for your " . ($booking['event_type'] ?: 'event') . "\n";
        $message .= "3. Contact support if you need assistance: kislaphelpdesk@gmail.com\n\n";
        $message .= "We sincerely apologize for any inconvenience caused.";

        $chatRepo->saveMessage(
            $booking['conversation_id'],
            0, // System message (sender_id = 0)
            'system',
            $message
        );
    }

    private function sendBookingCancellationEmail(array $booking, string $reason, ?string $suspendedUntil): void
    {
        $userEmail = $booking['user_email'];
        $userName = trim($booking['user_firstName'] . ' ' . $booking['user_lastName']);
        $workerName = trim($booking['worker_firstName'] . ' ' . $booking['worker_lastName']);
        
        $subject = "Booking Cancellation - Photographer Suspended";
        $emailBody = $this->getBookingCancellationEmailTemplate($booking, $userName, $workerName, $reason, $suspendedUntil);
        
        $fromEmail = 'kislaphelpdesk@gmail.com';
        $fromName = 'Kislap Support Team';
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";

        // Send email (or log in test mode)
        if ($this->isEmailTestMode()) {
            $this->logCancellationEmail($userEmail, $subject, $emailBody);
        } else {
            mail($userEmail, $subject, $emailBody, $headers);
        }
    }

    private function getBookingCancellationEmailTemplate(array $booking, string $userName, string $workerName, string $reason, ?string $suspendedUntil): string
    {
        $suspensionType = $suspendedUntil ? 'temporarily suspended' : 'suspended';
        $timeInfo = $suspendedUntil ? ' until ' . date('F j, Y \a\t g:i A', strtotime($suspendedUntil)) : '';
        $refundInfo = '';
        
        if ($booking['deposit_paid'] && $booking['deposit_amount'] > 0) {
            $refundInfo = "
            <div class='refund-section'>
                <h3>💰 Deposit Refund</h3>
                <p><strong>Refund Amount:</strong> $" . number_format($booking['deposit_amount'], 2) . "</p>
                <p><strong>Processing Time:</strong> 3-5 business days</p>
                <p><strong>Refund Method:</strong> Original payment method</p>
            </div>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Booking Cancellation Notice</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .booking-details { background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .refund-section { background: #e8f5e8; border: 1px solid #28a745; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .next-steps { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .label { font-weight: bold; color: #555; }
                .urgent { color: #dc3545; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚨 Booking Cancellation Notice</h1>
                    <p>Kislap Photography Platform</p>
                </div>
                <div class='content'>
                    <p>Dear {$userName},</p>
                    
                    <p>We regret to inform you that your photography booking has been cancelled due to your photographer being {$suspensionType} by our platform administration{$timeInfo}.</p>
                    
                    <div class='booking-details'>
                        <h3>📋 Booking Details</h3>
                        <p><span class='label'>Photographer:</span> {$workerName}</p>
                        <p><span class='label'>Event Type:</span> " . ($booking['event_type'] ?: 'Not specified') . "</p>
                        <p><span class='label'>Event Date:</span> " . ($booking['event_date'] ? date('F j, Y', strtotime($booking['event_date'])) : 'Not set') . "</p>
                        <p><span class='label'>Location:</span> " . ($booking['event_location'] ?: 'Not specified') . "</p>
                        <p><span class='label'>Booking Status:</span> <span class='urgent'>CANCELLED</span></p>
                    </div>
                    
                    {$refundInfo}
                    
                    <div class='next-steps'>
                        <h3>🔄 What Happens Next</h3>
                        <ul>
                            <li><strong>Immediate:</strong> Your booking is cancelled and you'll receive a chat notification</li>
                            " . ($booking['deposit_paid'] ? "<li><strong>3-5 Business Days:</strong> Deposit refund will be processed</li>" : "") . "
                            <li><strong>Now:</strong> You can browse and book other photographers on our platform</li>
                            <li><strong>Support:</strong> Our team is ready to help you find alternative photographers</li>
                        </ul>
                    </div>
                    
                    <div class='booking-details'>
                        <h3>🆘 Need Help?</h3>
                        <p>We understand this cancellation may cause inconvenience, especially if your event is approaching. Our support team is here to help:</p>
                        <ul>
                            <li><strong>Email:</strong> kislaphelpdesk@gmail.com</li>
                            <li><strong>Priority Support:</strong> Mention this cancellation for expedited assistance</li>
                            <li><strong>Alternative Photographers:</strong> We can help match you with available photographers</li>
                        </ul>
                    </div>
                    
                    <p>We sincerely apologize for any inconvenience this may cause and appreciate your understanding. We're committed to helping you find a suitable alternative for your photography needs.</p>
                </div>
                <div class='footer'>
                    <p>© 2025 Kislap Photography Platform - Support Team</p>
                    <p>This cancellation was processed on " . date('F j, Y \a\t g:i A') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function isEmailTestMode(): bool
    {
        return true; // Set to false in production
    }

    private function logCancellationEmail(string $to, string $subject, string $message): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'BOOKING_CANCELLATION',
            'to' => $to,
            'subject' => $subject,
            'message' => strip_tags($message)
        ];
        
        $logFile = __DIR__ . '/../logs/booking_cancellations.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Force logout a worker by destroying their session
     */
    private function forceLogoutWorker(int $workerId): void
    {
        try {
            // Get the session save path
            $sessionPath = session_save_path();
            if (empty($sessionPath)) {
                $sessionPath = sys_get_temp_dir();
            }

            // Check if session path is readable
            if (!is_readable($sessionPath)) {
                error_log("Session path not readable: " . $sessionPath);
                return;
            }

            // Look for session files
            $sessionFiles = glob($sessionPath . '/sess_*');
            
            if ($sessionFiles === false) {
                error_log("Failed to read session files from: " . $sessionPath);
                return;
            }
            
            foreach ($sessionFiles as $sessionFile) {
                // Check if file is readable before trying to read it
                if (!is_readable($sessionFile)) {
                    continue;
                }
                
                $sessionData = @file_get_contents($sessionFile);
                
                // Skip if we couldn't read the file
                if ($sessionData === false) {
                    continue;
                }
                
                // Check if this session contains the worker we want to logout
                if (strpos($sessionData, '"worker_id";i:' . $workerId . ';') !== false || 
                    strpos($sessionData, '"worker_id";s:' . strlen($workerId) . ':"' . $workerId . '";') !== false) {
                    
                    // Delete the session file to force logout
                    if (is_writable($sessionFile)) {
                        @unlink($sessionFile);
                    }
                    break;
                }
            }
        } catch (Exception $e) {
            error_log("Error in forceLogoutWorker: " . $e->getMessage());
        }
    }

}
