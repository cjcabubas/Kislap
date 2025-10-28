<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/WorkerRepository.php";

class WorkerController
{
    // ========================================
    // CONSTRUCTOR
    // ========================================

    public function __construct()
    {
        $this->repo = new WorkerRepository();
    }

    // ========================================
    // AUTHENTICATION
    // ========================================
    
    public function login(): void
    {
        require "views/worker/login.php";
    }

    public function loginDB(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/worker/login.php";
            return;
        }
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
                header("Location: index.php?controller=Worker&action=dashboard");
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

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /Kislap/index.php?controller=Home&action=homePage");
        exit;
    }

    // ========================================
    // DASHBOARD
    // ========================================
    
    public function dashboard(): void
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
        
        $stats = $chatRepo->getWorkerBookingStats($workerId);
        $recentBookings = $chatRepo->getWorkerBookings($workerId, null, 5);
        $earningsData = $this->repo->getWorkerEarnings($workerId);
        
        require __DIR__ . '/../views/worker/dashboard.php';
    }

    // ========================================
    // PROFILE MANAGEMENT
    // ========================================
    
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
        error_log("=== WORKER PROFILE UPDATE STARTED ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("FILES data: " . print_r($_FILES, true));
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        error_log("Worker session data: " . print_r($worker, true));
        
        if (!$worker) {
            error_log("No worker in session, redirecting to login");
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not a POST request, redirecting to profile");
            header("Location: index.php?controller=Worker&action=profile");
            exit;
        }

        $workerId = $worker['worker_id'];
        error_log("Worker ID: " . $workerId);

        try {
            error_log("Building profile data from POST...");
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
            
            error_log("Profile data built: " . print_r($profileData, true));

            error_log("Validating required fields...");
            if (empty($profileData['firstName']) || empty($profileData['lastName']) ||
                empty($profileData['phoneNumber']) || empty($profileData['address']) ||
                empty($profileData['specialty']) || empty($profileData['bio'])) {
                error_log("Validation failed - missing required fields");
                error_log("firstName: '" . $profileData['firstName'] . "'");
                error_log("lastName: '" . $profileData['lastName'] . "'");
                error_log("phoneNumber: '" . $profileData['phoneNumber'] . "'");
                error_log("address: '" . $profileData['address'] . "'");
                error_log("specialty: '" . $profileData['specialty'] . "'");
                error_log("bio: '" . $profileData['bio'] . "'");
                $_SESSION['error'] = 'Please fill in all required fields.';
                header("Location: index.php?controller=Worker&action=profile&edit=true");
                exit;
            }
            
            error_log("Validation passed! Proceeding with file upload check...");

            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                error_log("Profile photo file detected, validating...");
                error_log("File details: " . print_r($_FILES['profile_photo'], true));
                
                require_once __DIR__ . '/../model/Validator.php';
                
                $photoValidation = Validator::validateFile($_FILES['profile_photo'], 'profile_photo');
                error_log("Photo validation result: " . print_r($photoValidation, true));
                if (!$photoValidation['valid']) {
                    error_log("Photo validation FAILED: " . $photoValidation['message']);
                    $_SESSION['error'] = 'Profile photo error: ' . $photoValidation['message'];
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }
                
                error_log("Photo validation PASSED, proceeding with upload...");

                $targetDir = "uploads/workers/{$workerId}/";
                error_log("Target directory: " . $targetDir);
                
                if (!is_dir($targetDir)) {
                    error_log("Creating directory: " . $targetDir);
                    mkdir($targetDir, 0755, true);
                }

                $secureFilename = Validator::generateSecureFilename($_FILES['profile_photo']['name'], "worker{$workerId}_profile_");
                $targetFile = $targetDir . $secureFilename;
                error_log("Target file: " . $targetFile);

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                    error_log("File upload SUCCESS: " . $targetFile);
                    chmod($targetFile, 0644);
                    $profileData['profile_photo'] = $targetFile;
                } else {
                    error_log("File upload FAILED for: " . $targetFile);
                    $_SESSION['warning'] = 'Failed to upload profile photo.';
                }
            } else {
                error_log("No profile photo to upload or file upload error");
            }

            error_log("Attempting to update worker profile for worker ID: " . $workerId);
            error_log("Profile data: " . print_r($profileData, true));
            
            $updateResult = $this->repo->updateWorkerProfile($workerId, $profileData);
            
            if (!$updateResult) {
                error_log("Failed to update worker profile for worker ID: " . $workerId);
                $_SESSION['error'] = 'Failed to update profile. Please try again.';
                header("Location: index.php?controller=Worker&action=profile&edit=true");
                exit;
            }
            
            error_log("Successfully updated worker profile for worker ID: " . $workerId);

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

                $workerData = $this->repo->getWorkerById($workerId);
                if (!password_verify($currentPassword, $workerData['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                $this->repo->updateWorkerPassword($workerId, $newPassword);
            }

            $packagesData = $_POST['packages'] ?? [];
            if (!empty($packagesData)) {
                $this->repo->syncWorkerPackages($workerId, $packagesData);
            }

            if (isset($_FILES['portfolio_images']) && !empty($_FILES['portfolio_images']['tmp_name'][0])) {
                $portfolioResult = $this->handlePortfolioUpload($_FILES['portfolio_images'], $workerId);
                if (!$portfolioResult['success']) {
                    $_SESSION['error'] = $portfolioResult['message'];
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }
            }

            error_log("Updating session data...");
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
                error_log("Updated profile photo in session: " . $profileData['profile_photo']);
            }

            error_log("Setting success message and redirecting...");
            $_SESSION['success'] = 'Profile updated successfully!';
            error_log("=== WORKER PROFILE UPDATE COMPLETED SUCCESSFULLY ===");
            header("Location: index.php?controller=Worker&action=profile");
            exit;

        } catch (Exception $e) {
            error_log("=== EXCEPTION CAUGHT IN PROFILE UPDATE ===");
            error_log("Exception message: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            error_log("=== END EXCEPTION ===");
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
            header("Location: index.php?controller=Worker&action=profile&edit=true");
            exit;
        }
    }


    // ========================================
    // PORTFOLIO MANAGEMENT
    // ========================================
    
    private function handlePortfolioUpload(array $files, int $workerId): array
    {
        require_once __DIR__ . '/../model/Validator.php';
        
        $maxImages = 8;
        $currentCount = $this->repo->getWorkerPortfolioCount($workerId);
        $validation = Validator::validateMultipleFiles($files, 'portfolio', $maxImages);
        
        if (!$validation['valid']) {
            return ['success' => false, 'message' => implode(', ', $validation['errors'])];
        }

        $availableSlots = $maxImages - $currentCount;
        if (count($validation['files']) > $availableSlots) {
            return ['success' => false, 'message' => "You can only upload {$availableSlots} more images (maximum {$maxImages} total)"];
        }

        $uploadDir = "uploads/workers/{$workerId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedCount = 0;
        $errors = [];

        foreach ($validation['files'] as $file) {
            try {
                $secureFilename = Validator::generateSecureFilename($file['name'], "worker{$workerId}_work_");
                $targetPath = $uploadDir . $secureFilename;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    chmod($targetPath, 0644);
                    $this->repo->insertWorkerWork($workerId, $targetPath);
                    $uploadedCount++;
                } else {
                    $errors[] = "Failed to upload: " . $file['name'];
                }
            } catch (Exception $e) {
                $errors[] = "Error uploading " . $file['name'] . ": " . $e->getMessage();
            }
        }

        if ($uploadedCount > 0) {
            $message = "{$uploadedCount} portfolio image(s) uploaded successfully!";
            if (!empty($errors)) {
                $message .= " Some files failed: " . implode(', ', $errors);
            }
            
            if (!isset($_SESSION['success'])) {
                $_SESSION['success'] = $message;
            }
            
            return ['success' => true, 'message' => $message];
        }

        return ['success' => false, 'message' => 'No files were uploaded. ' . implode(', ', $errors)];
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
        
        // Handle special filter cases
        if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            // For grouped filters, get all bookings and filter in PHP
            $allBookings = $chatRepo->getWorkerBookings($workerId, null);
            $bookings = [];
            
            foreach ($allBookings as $booking) {
                $bookingStatus = $booking['booking_status'] ?? '';
                
                // Handle empty status as pending_worker
                if (empty($bookingStatus) || $bookingStatus === '') {
                    $bookingStatus = 'pending_worker';
                }
                
                $includeBooking = false;
                
                switch ($status) {
                    case 'pending':
                        $includeBooking = in_array($bookingStatus, ['pending_worker', 'pending_ai', 'requires_info']);
                        break;
                    case 'confirmed':
                        $includeBooking = in_array($bookingStatus, ['confirmed', 'awaiting_deposit', 'deposit_paid', 'in_progress']);
                        break;
                    case 'completed':
                        $includeBooking = in_array($bookingStatus, ['completed', 'rated']);
                        break;
                    case 'cancelled':
                        $includeBooking = in_array($bookingStatus, ['cancelled']);
                        break;
                }
                
                if ($includeBooking) {
                    $bookings[] = $booking;
                }
            }
        } else {
            // Get bookings with specific status (negotiating, etc.)
            $bookings = $chatRepo->getWorkerBookings($workerId, $status);
        }
        
        // Ensure bookings is always an array
        if (!$bookings) {
            $bookings = [];
        }
        
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

        try {
            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();

            // Update conversation status to 'confirmed'
            if ($chatRepo->updateConversationStatus($conversationId, 'confirmed')) {
                // Send message to user
                $chatRepo->saveMessage(
                    $conversationId,
                    $worker['worker_id'],
                    'worker',
                    "✅ I've accepted your booking! Please proceed with the 50% down payment to confirm."
                );

                echo json_encode(['success' => true, 'message' => 'Booking accepted!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to accept booking']);
            }
        } catch (Exception $e) {
            error_log("Accept booking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
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

        try {
            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();

            // Update conversation status to 'cancelled'
            if ($chatRepo->updateConversationStatus($conversationId, 'cancelled')) {
                // Send message to user
                $message = "❌ I've declined your booking request.";
                if ($reason) {
                    $message .= "\n\nReason: " . $reason;
                }
                $chatRepo->saveMessage(
                    $conversationId,
                    $worker['worker_id'],
                    'worker',
                    $message
                );

                echo json_encode(['success' => true, 'message' => 'Booking rejected']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to reject booking']);
            }
        } catch (Exception $e) {
            error_log("Reject booking error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // ========================================
    // BOOKING CONTROLS
    // ========================================
    
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
    
    public function updateBookingDetails(): void
    {
        // Start output buffering to catch any unexpected output
        ob_start();
        
        try {
            header('Content-Type: application/json');
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $worker = $_SESSION['worker'] ?? null;
            if (!$worker) {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Not authenticated']);
                exit;
            }

            $conversationId = $_POST['conversation_id'] ?? null;
            
            if (!$conversationId) {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
                exit;
            }
            
            // Collect updates
            $updates = [];
            $allowedFields = ['event_date', 'event_time', 'event_location', 'worker_notes', 'package_id'];
            
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field]) && $_POST[$field] !== '') {
                    $updates[$field] = $_POST[$field];
                }
            }
            
            if (empty($updates)) {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'No updates provided']);
                exit;
            }

            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();
            
            if ($chatRepo->updateBookingDetails($conversationId, $updates)) {
                ob_clean();
                echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Failed to update booking']);
            }
        } catch (Exception $e) {
            ob_clean();
            error_log("Update booking details error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
    
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
    
    // ========================================
    // AVAILABILITY MANAGEMENT
    // ========================================
    
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