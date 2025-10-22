<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/WorkerRepository.php";

class WorkerController
{

    public function __construct()
    {
        $this->repo = new WorkerRepository();
    }

    public function login(): void
    {
        require "views/worker/login.php";
    }

    public function loginDB(): void
    {
        // Show the form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/application/login.php";
            return;
        }

        // Handle form submit on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? null;
            $password = $_POST['password'] ?? null;

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (strpos($identifier, '@') !== false) {
                    $worker = $this->repo->findByEmail($identifier);
                } else {
                    $worker = $this->repo->findByphoneNumber($identifier);
                }

                if (!$worker) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'No user with that email or phone number found.'
                    ];
                    header("Location: index.php?controller=Worker&action=login");
                    exit;
                }


                if (!password_verify($password, $worker['password'])) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Wrong password.'
                    ];
                    header("Location: index.php?controller=Worker&action=login");
                    exit;
                }

                unset($worker['password']);

                $_SESSION['worker'] = [
                    'worker_id' => $worker['worker_id'],
                    'application_id' => $worker['application_id'],
                    'lastName' => $worker['lastName'],
                    'firstName' => $worker['firstName'],
                    'middleName' => $worker['middleName'],
                    'email' => $worker['email'],
                    'phoneNumber' => $worker['phoneNumber'],
                    'address' => $worker['address'],
                    'specialty' => $worker['specialty'],
                    'experience_years' => $worker['experience_years'],
                    'bio' => $worker['bio'],
                    'profile_photo' => $worker['profile_photo'],
                    'rating_average' => $worker['rating_average'],
                    'total_ratings' => $worker['total_ratings'],
                    'total_bookings' => $worker['total_bookings'],
                    'total_earnings' => $worker['total_earnings'],
                    'status' => $worker['status'],
                    'created_at' => $worker['created_at'],
                    'role' => 'worker'

                ];
                header("Location: index.php?controller=Home&action=homePage");
                exit;
            } catch (Exception $e) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                header("Location: index.php?controller=Worker&action=login");
                exit;
            }
        }
    }

    public function profile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        // Get fresh data from database
        $workerId = $worker['worker_id'];
        $workerData = $this->repo->getWorkerById($workerId);

        if (!$workerData) {
            echo "Worker not found.";
            exit;
        }

        $worker = array_merge($worker, $workerData);

        $existingPortfolio = $this->repo->getWorkerPortfolio($workerId);

        $existingPackages = $this->repo->getWorkerPackages($workerId);

        $isEditMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

        require __DIR__ . '/../views/worker/profile.php';
    }

    public function updateProfile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Worker&action=profile");
            exit;
        }

        $workerId = $worker['worker_id'];

        try {
            // Prepare profile data
            $profileData = [
                'firstName' => trim($_POST['firstName'] ?? ''),
                'middleName' => trim($_POST['middleName'] ?? ''),
                'lastName' => trim($_POST['lastName'] ?? ''),
                'phoneNumber' => trim($_POST['phoneNumber'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'specialty' => trim($_POST['specialty'] ?? ''),
                'experience_years' => (int)($_POST['experience_years'] ?? 0),
                'bio' => trim($_POST['bio'] ?? '')
            ];

            // Validate required fields
            if (empty($profileData['firstName']) || empty($profileData['lastName']) ||
                empty($profileData['phoneNumber']) || empty($profileData['address']) ||
                empty($profileData['specialty']) || empty($profileData['bio'])) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                header("Location: index.php?controller=Worker&action=profile&edit=true");
                exit;
            }

            // Handle profile photo upload
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/workers/{$workerId}/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $fileName = "worker{$workerId}_profile_photo." . $ext;
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                    $profileData['profile_photo'] = $targetFile;
                } else {
                    $_SESSION['warning'] = 'Failed to upload profile photo.';
                }
            }

            // Update basic profile
            $this->repo->updateWorkerProfile($workerId, $profileData);

            // Handle password change
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if (!empty($currentPassword) && !empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New passwords do not match.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                if (strlen($newPassword) < 6) {
                    $_SESSION['error'] = 'Password must be at least 6 characters long.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                // Verify current password
                $workerData = $this->repo->getWorkerById($workerId);
                if (!password_verify($currentPassword, $workerData['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                // Update password
                $this->repo->updateWorkerPassword($workerId, $newPassword);
            }

            // **MODIFIED**: Handle service packages update
            $packagesData = $_POST['packages'] ?? [];
            if (!empty($packagesData)) {
                $this->repo->syncWorkerPackages($workerId, $packagesData);
            }

            // Handle portfolio images upload
            if (isset($_FILES['portfolio_images']) && !empty($_FILES['portfolio_images']['tmp_name'][0])) {
                $this->handlePortfolioUpload($_FILES['portfolio_images'], $workerId);
            }

            // Update session data
            $_SESSION['worker']['firstName'] = $profileData['firstName'];
            $_SESSION['worker']['middleName'] = $profileData['middleName'];
            $_SESSION['worker']['lastName'] = $profileData['lastName'];
            $_SESSION['worker']['phoneNumber'] = $profileData['phoneNumber'];
            $_SESSION['worker']['address'] = $profileData['address'];
            $_SESSION['worker']['specialty'] = $profileData['specialty'];
            $_SESSION['worker']['experience_years'] = $profileData['experience_years'];
            $_SESSION['worker']['bio'] = $profileData['bio'];

            if (isset($profileData['profile_photo'])) {
                $_SESSION['worker']['profile_photo'] = $profileData['profile_photo'];
            }

            $_SESSION['success'] = 'Profile updated successfully!';
            header("Location: index.php?controller=Worker&action=profile");
            exit;

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
            header("Location: index.php?controller=Worker&action=profile&edit=true");
            exit;
        }
    }


    private function handlePortfolioUpload(array $files, int $workerId): void
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/avif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $maxImages = 8;

        // Get current portfolio count
        $currentCount = $this->repo->getWorkerPortfolioCount($workerId);

        $uploadDir = "uploads/workers/{$workerId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedCount = 0;

        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($currentCount + $uploadedCount >= $maxImages) {
                $_SESSION['warning'] = "Maximum of {$maxImages} portfolio images allowed.";
                break;
            }

            if (!in_array($files['type'][$key], $allowedTypes)) {
                continue;
            }

            if ($files['size'][$key] > $maxSize) {
                continue;
            }

            $extension = pathinfo($files['name'][$key], PATHINFO_EXTENSION);
            $filename = "worker" . $workerId . "_" . 'work'. uniqid() . "." . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $dbPath = $uploadDir . $filename;
                $this->repo->insertWorkerWork($workerId, $dbPath);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            if (!isset($_SESSION['success'])) {
                $_SESSION['success'] = "{$uploadedCount} portfolio image(s) uploaded successfully!";
            }
        }
    }

    public function removePortfolioImage(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $workId = (int)($data['work_id'] ?? 0);
        $workerId = $worker['worker_id'];

        if (!$workId) {
            echo json_encode(['success' => false, 'message' => 'Invalid work ID']);
            exit;
        }

        try {
            // Get image path before deleting
            $work = $this->repo->getWorkerWorkById($workId, $workerId);

            if (!$work) {
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }

            // Delete from database
            $this->repo->deleteWorkerWork($workId, $workerId);

            // Delete physical file
            $filePath = $work['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
        } catch (Exception $e) {
            error_log("Remove portfolio error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to remove image']);
        }
    }

    // ========================================
    // BOOKING MANAGEMENT
    // ========================================
    
    public function bookings(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $workerId = $worker['worker_id'];
        $status = $_GET['status'] ?? null;
        
        // Get bookings
        $bookings = $chatRepo->getWorkerBookings($workerId, $status);
        
        require __DIR__ . '/../views/worker/bookings.php';
    }
    
    public function acceptBooking(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->acceptBooking($conversationId)) {
            echo json_encode(['success' => true, 'message' => 'Booking accepted']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to accept booking']);
        }
        exit;
    }
    
    public function rejectBooking(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $reason = $_POST['reason'] ?? null;
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->rejectBooking($conversationId, $reason)) {
            echo json_encode(['success' => true, 'message' => 'Booking rejected']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to reject booking']);
        }
        exit;
    }
    
    // ========================================
    // PROFESSIONAL BOOKING CONTROLS
    // ========================================
    
    /**
     * Propose alternative pricing
     */
    public function proposePrice(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $proposedPrice = $_POST['proposed_price'] ?? null;
        $notes = $_POST['notes'] ?? null;
        
        if (!$conversationId || !$proposedPrice) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->proposePrice($conversationId, (float)$proposedPrice, $notes)) {
            // Send notification message to client
            $message = "I've reviewed your booking request. I'd like to propose a price of ₱" . number_format($proposedPrice, 2);
            if ($notes) {
                $message .= "\n\nNote: " . $notes;
            }
            $chatRepo->saveMessage($conversationId, $worker['worker_id'], 'worker', $message);
            
            echo json_encode(['success' => true, 'message' => 'Price proposal sent']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to propose price']);
        }
        exit;
    }
    
    /**
     * Propose alternative date/time
     */
    public function proposeDateTime(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $proposedDate = $_POST['proposed_date'] ?? null;
        $proposedTime = $_POST['proposed_time'] ?? null;
        $reason = $_POST['reason'] ?? null;
        
        if (!$conversationId || !$proposedDate) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->proposeDateTime($conversationId, $proposedDate, $proposedTime, $reason)) {
            // Send notification message to client
            $message = "I'd like to propose an alternative date: " . date('F d, Y', strtotime($proposedDate));
            if ($proposedTime) {
                $message .= " at " . date('h:i A', strtotime($proposedTime));
            }
            if ($reason) {
                $message .= "\n\nReason: " . $reason;
            }
            $chatRepo->saveMessage($conversationId, $worker['worker_id'], 'worker', $message);
            
            echo json_encode(['success' => true, 'message' => 'Date/time proposal sent']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to propose date/time']);
        }
        exit;
    }
    
    /**
     * Request more information from client
     */
    public function requestMoreInfo(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $message = $_POST['message'] ?? null;
        
        if (!$conversationId || !$message) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->requestMoreInfo($conversationId, $message)) {
            echo json_encode(['success' => true, 'message' => 'Information request sent']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to send request']);
        }
        exit;
    }
    
    /**
     * Update booking details
     */
    public function updateBookingDetails(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }
        
        // Collect updates
        $updates = [];
        $allowedFields = ['event_date', 'event_time', 'event_location', 'final_price', 'worker_notes', 'package_id'];
        
        foreach ($allowedFields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $updates[$field] = $_POST[$field];
            }
        }
        
        if (empty($updates)) {
            echo json_encode(['success' => false, 'error' => 'No updates provided']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->updateBookingDetails($conversationId, $updates)) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update booking']);
        }
        exit;
    }
    
    /**
     * Set deposit amount
     */
    public function setDeposit(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $depositAmount = $_POST['deposit_amount'] ?? null;
        
        if (!$conversationId || !$depositAmount) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->setDepositAmount($conversationId, (float)$depositAmount)) {
            // Send notification message
            $message = "A deposit of ₱" . number_format($depositAmount, 2) . " is required to confirm this booking.";
            $chatRepo->saveMessage($conversationId, $worker['worker_id'], 'worker', $message);
            
            echo json_encode(['success' => true, 'message' => 'Deposit amount set']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to set deposit']);
        }
        exit;
    }
    
    /**
     * Get booking statistics
     */
    public function getBookingStats(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $stats = $chatRepo->getWorkerBookingStats($worker['worker_id'], $startDate, $endDate);
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }
    
    /**
     * Manage availability calendar
     */
    public function manageAvailability(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $workerId = $worker['worker_id'];
        
        // Get availability for next 90 days
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+90 days'));
        $availability = $chatRepo->getAvailabilityRange($workerId, $startDate, $endDate);
        
        require __DIR__ . '/../views/worker/availability.php';
    }
    
    /**
     * Set availability (AJAX)
     */
    public function setAvailability(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $date = $_POST['date'] ?? null;
        $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
        $startTime = $_POST['start_time'] ?? null;
        $endTime = $_POST['end_time'] ?? null;
        $maxBookings = $_POST['max_bookings'] ?? 1;
        
        if (!$date) {
            echo json_encode(['success' => false, 'error' => 'Missing date']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->setAvailability($worker['worker_id'], $date, $isAvailable, $startTime, $endTime, (int)$maxBookings)) {
            echo json_encode(['success' => true, 'message' => 'Availability updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update availability']);
        }
        exit;
    }
    
    /**
     * Block multiple dates
     */
    public function blockDates(): void
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $dates = $_POST['dates'] ?? [];
        $reason = $_POST['reason'] ?? null;
        
        if (empty($dates)) {
            echo json_encode(['success' => false, 'error' => 'No dates provided']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        if ($chatRepo->blockDates($worker['worker_id'], $dates, $reason)) {
            echo json_encode(['success' => true, 'message' => count($dates) . ' date(s) blocked']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to block dates']);
        }
        exit;
    }
}