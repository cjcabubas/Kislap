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

                // Check if worker is suspended BEFORE logging them in
                if ($worker['status'] === 'suspended') {
                    $suspensionInfo = $this->checkSuspensionStatus($worker);
                    if ($suspensionInfo['is_suspended']) {
                        $_SESSION['suspension_info'] = $suspensionInfo;
                        header("Location: index.php?controller=Worker&action=suspended");
                        exit;
                    }
                }

                // Check if worker is banned BEFORE logging them in
                if ($worker['status'] === 'banned') {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Your account has been permanently banned. Contact support for more information.'
                    ];
                    header("Location: index.php?controller=Worker&action=login");
                    exit;
                }

                unset($worker['password']);

                // Store the complete worker data (minus password) - only for active workers
                $_SESSION['worker'] = $worker;
                $_SESSION['worker']['role'] = 'worker';
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
    // SUSPENSION MANAGEMENT
    // ========================================

    public function suspended(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $suspensionInfo = $_SESSION['suspension_info'] ?? null;
        if (!$suspensionInfo) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        require "views/worker/suspended.php";
    }



    private function checkSuspensionStatus(array $worker): array
    {
        $currentTime = new DateTime();
        $suspendedUntil = $worker['suspended_until'] ? new DateTime($worker['suspended_until']) : null;

        // If no suspension end date, it's permanent suspension
        if (!$suspendedUntil) {
            return [
                'is_suspended' => true,
                'is_permanent' => true,
                'reason' => $worker['suspension_reason'] ?? 'No reason provided',
                'suspended_at' => $worker['suspended_at'] ?? null,
                'time_remaining' => null,
                'worker_id' => $worker['worker_id'],
                'worker_name' => trim(($worker['firstName'] ?? '') . ' ' . ($worker['lastName'] ?? '')),
                'worker_email' => $worker['email'] ?? ''
            ];
        }

        // Check if suspension has expired
        if ($currentTime >= $suspendedUntil) {
            // Suspension expired, reactivate worker
            $this->repo->reactivateWorker($worker['worker_id']);
            return ['is_suspended' => false];
        }

        // Calculate time remaining
        $interval = $currentTime->diff($suspendedUntil);
        $timeRemaining = $this->formatTimeRemaining($interval);

        return [
            'is_suspended' => true,
            'is_permanent' => false,
            'reason' => $worker['suspension_reason'] ?? 'No reason provided',
            'suspended_at' => $worker['suspended_at'] ?? null,
            'suspended_until' => $worker['suspended_until'],
            'time_remaining' => $timeRemaining,
            'worker_id' => $worker['worker_id'],
            'worker_name' => trim(($worker['firstName'] ?? '') . ' ' . ($worker['lastName'] ?? '')),
            'worker_email' => $worker['email'] ?? ''
        ];
    }

    private function formatTimeRemaining(DateInterval $interval): string
    {
        $parts = [];
        
        if ($interval->d > 0) {
            $parts[] = $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
        }
        if ($interval->h > 0) {
            $parts[] = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '');
        }
        if ($interval->i > 0 && $interval->d == 0) {
            $parts[] = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '');
        }

        return empty($parts) ? 'Less than a minute' : implode(', ', $parts);
    }

    private function isWorkerSuspended(array $worker): bool
    {
        if ($worker['status'] !== 'suspended') {
            return false;
        }

        $suspensionInfo = $this->checkSuspensionStatus($worker);
        if ($suspensionInfo['is_suspended']) {
            $_SESSION['suspension_info'] = $suspensionInfo;
            return true;
        }

        return false;
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

        // Check suspension status
        if ($this->isWorkerSuspended($worker)) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $workerId = $worker['worker_id'];
        
        $stats = $chatRepo->getWorkerBookingStats($workerId);
        $recentBookings = $chatRepo->getWorkerBookings($workerId, null, 5);
        
        // Calculate worker's revenue share (90% after 10% platform fee)
        $stats['total_revenue'] = ($stats['total_revenue'] ?? 0) * 0.9;
        $stats['avg_booking_value'] = ($stats['avg_booking_value'] ?? 0) * 0.9;
        
        // Calculate earnings from completed bookings only (minus 30% platform fee)
        $completedEarnings = $this->getCompletedBookingsEarnings($workerId);
        
        $earningsData = [
            'total_earnings' => $completedEarnings,
            'rating_average' => $worker['average_rating'] ?? 0,
            'total_ratings' => $worker['total_ratings'] ?? 0
        ];
        
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

        // Check suspension status
        if ($this->isWorkerSuspended($worker)) {
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
        
        // Get stats directly from workers table - no complex merging
        $stats = $this->repo->getWorkerStats($workerId);
        
        // Create a clean worker data array with stats
        $workerData = array_merge($worker, $stats);
        
        // Update session with complete data including stats
        $_SESSION['worker'] = $workerData;
        $_SESSION['worker']['role'] = 'worker';
        
        $existingPortfolio = $this->repo->getWorkerPortfolio($workerId);
        $existingPackages = $this->repo->getWorkerPackages($workerId);
        $isEditMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

        // Pass clean data to view
        $worker = $workerData;
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

            // Validate phone number format
            require_once __DIR__ . '/../model/Validator.php';
            $phoneValidation = Validator::validatePhoneNumber($profileData['phoneNumber']);
            if (!$phoneValidation['valid']) {
                error_log("Phone validation failed: " . $phoneValidation['message']);
                $_SESSION['error'] = $phoneValidation['message'];
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

    // ========================================
    // APPEALS SYSTEM
    // ========================================

    public function submitAppeal(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $suspensionInfo = $_SESSION['suspension_info'] ?? null;
        if (!$suspensionInfo) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');

        if (empty($subject) || empty($message)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Appeal not sent - Please fill in all required fields (subject and message).'
            ];
            header("Location: index.php?controller=Worker&action=suspended");
            exit;
        }

        if (strlen($message) < 20) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Appeal not sent - Please provide a more detailed explanation (at least 20 characters).'
            ];
            header("Location: index.php?controller=Worker&action=suspended");
            exit;
        }

        try {
            $success = $this->sendAppealEmail($subject, $message, $contactEmail, $suspensionInfo);

            if ($success) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Appeal submitted'
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Appeal was not submitted'
                ];
            }
        } catch (Exception $e) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Appeal was not submitted'
            ];
        }

        header("Location: index.php?controller=Worker&action=suspended");
        exit;
    }



    private function sendAppealEmail(string $subject, string $message, string $contactEmail, array $suspensionInfo): bool
    {
        $helpdeskEmail = 'kislaphelpdesk@gmail.com'; // Send to helpdesk (itself)
        $fromEmail = 'kislaphelpdesk@gmail.com';
        $fromName = 'Kislap Appeals System';

        // Get worker info from suspension info (since worker is not logged in)
        $workerName = $suspensionInfo['worker_name'] ?? 'Unknown Worker';
        $workerEmail = $suspensionInfo['worker_email'] ?? 'Unknown Email';
        $workerId = $suspensionInfo['worker_id'] ?? 'Unknown ID';

        $emailSubject = "Suspension Appeal: $subject - Worker ID: $workerId";
        
        $emailBody = $this->getAppealEmailTemplate(
            $subject,
            $message,
            $contactEmail,
            $suspensionInfo,
            $workerName,
            $workerEmail,
            $workerId
        );

        // Use Gmail SMTP settings
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;
        $smtpUsername = 'kislaphelpdesk@gmail.com';
        $smtpPassword = 'vbvp uokz yyfa hfnf';

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$smtpUsername}>\r\n";
        $headers .= "Reply-To: " . ($contactEmail ?: $workerEmail) . "\r\n";



        // Use the exact same email sending approach as AuthController
        return $this->sendAppealEmailSimple($helpdeskEmail, $emailSubject, $emailBody);
    }

    private function getAppealEmailTemplate(string $subject, string $message, string $contactEmail, array $suspensionInfo, string $workerName, string $workerEmail, string $workerId): string
    {
        $suspensionType = $suspensionInfo['is_permanent'] ? 'Permanent' : 'Temporary';
        $suspensionReason = htmlspecialchars($suspensionInfo['reason']);
        $suspendedAt = $suspensionInfo['suspended_at'] ?? 'Unknown';
        $suspendedUntil = $suspensionInfo['suspended_until'] ?? 'N/A';
        $timeRemaining = $suspensionInfo['time_remaining'] ?? 'N/A';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Suspension Appeal</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-section { background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .appeal-message { background: #e8f5e8; border: 1px solid #28a745; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .suspension-details { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .label { font-weight: bold; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚖️ Suspension Appeal Submitted</h1>
                    <p>Kislap Photography Platform</p>
                </div>
                <div class='content'>
                    <div class='info-section'>
                        <h3>Worker Information</h3>
                        <p><span class='label'>Name:</span> $workerName</p>
                        <p><span class='label'>Worker ID:</span> $workerId</p>
                        <p><span class='label'>Email:</span> $workerEmail</p>
                        <p><span class='label'>Contact Email:</span> " . ($contactEmail ?: 'Same as worker email') . "</p>
                    </div>
                    
                    <div class='suspension-details'>
                        <h3>Current Suspension Details</h3>
                        <p><span class='label'>Type:</span> $suspensionType Suspension</p>
                        <p><span class='label'>Reason:</span> $suspensionReason</p>
                        <p><span class='label'>Suspended At:</span> $suspendedAt</p>
                        " . (!$suspensionInfo['is_permanent'] ? "<p><span class='label'>Suspended Until:</span> $suspendedUntil</p><p><span class='label'>Time Remaining:</span> $timeRemaining</p>" : "") . "
                    </div>
                    
                    <div class='appeal-message'>
                        <h3>Appeal Details</h3>
                        <p><span class='label'>Appeal Type:</span> " . ucwords(str_replace('_', ' ', $subject)) . "</p>
                        <p><span class='label'>Worker's Statement:</span></p>
                        <div style='background: white; padding: 15px; border-radius: 5px; margin-top: 10px;'>
                            " . nl2br(htmlspecialchars($message)) . "
                        </div>
                    </div>
                    
                    <div class='info-section'>
                        <h3>Next Steps</h3>
                        <ul>
                            <li>Review the appeal within 24-48 hours</li>
                            <li>Investigate the original suspension reason</li>
                            <li>Contact the worker if additional information is needed</li>
                            <li>Make a decision and notify the worker</li>
                        </ul>
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2025 Kislap Photography Platform - Appeals System</p>
                    <p>This appeal was submitted on " . date('F j, Y \a\t g:i A') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }



    /**
     * Send email using Gmail SMTP (same implementation as AuthController)
     */
    private function sendWithGmailSMTP(string $to, string $subject, string $message, string $fromName): bool
    {
        try {
            // Gmail SMTP settings
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com'; // Gmail address
            $smtpPassword = 'vbvp uokz yyfa hfnf';      // Gmail App Password
            
            // Create socket connection to Gmail SMTP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                error_log("SMTP connection failed: $errstr ($errno)");
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("SMTP initial response failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send EHLO command
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
            
            // Start TLS encryption
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("STARTTLS failed: $response");
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("Failed to enable TLS encryption");
                fclose($socket);
                return false;
            }
            
            // Send EHLO again after TLS
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                error_log("AUTH LOGIN failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send username (base64 encoded)
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                error_log("Username authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send password (base64 encoded)
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                error_log("Password authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("MAIL FROM failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("RCPT TO failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send DATA command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                error_log("DATA command failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send email headers and body
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$smtpUsername}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $message . "\r\n.\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("Email sending failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            // Log successful email
            error_log("Appeal email sent successfully to: $to");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Gmail SMTP error in WorkerController: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using the exact same method as AuthController
     */
    private function sendEmailLikeAuthController(string $to, string $subject, string $message, string $appealSubject = '', string $appealMessage = '', array $suspensionInfo = []): bool
    {
        // Production email settings (same as AuthController)
        $fromEmail = 'kislaphelpdesk@gmail.com';
        $fromName = 'Kislap Photography Platform';
        
        // Log email attempt with more details
        error_log("=== EMAIL SENDING DEBUG START ===");
        error_log("To: $to");
        error_log("Subject: $subject");
        error_log("From: $fromName <$fromEmail>");
        error_log("Message length: " . strlen($message) . " characters");
        
        // Try a very simple email first
        error_log("Trying simple text email...");
        
        $simpleSubject = "Test Appeal - Worker Suspension";
        $simpleMessage = "This is a test appeal email from the Kislap system.\n\nWorker: " . ($suspensionInfo['worker_name'] ?? 'Unknown') . "\nReason: " . $appealSubject . "\nMessage: " . substr($appealMessage, 0, 100) . "...";
        
        $simpleHeaders = "From: kislaphelpdesk@gmail.com\r\n";
        $simpleHeaders .= "Reply-To: kislaphelpdesk@gmail.com\r\n";
        
        $result = mail($to, $simpleSubject, $simpleMessage, $simpleHeaders);
        
        if ($result) {
            error_log("=== SIMPLE EMAIL SUCCESS ===");
            error_log("Simple appeal email sent to: $to");
        } else {
            error_log("=== SIMPLE EMAIL FAILED ===");
            error_log("Even simple mail() failed for: $to");
            
            // Try Gmail SMTP as backup
            error_log("Trying Gmail SMTP as backup...");
            $result = $this->sendWithGmailSMTPLikeAuth($to, $subject, $message, $fromEmail, $fromName);
            
            if ($result) {
                error_log("=== Gmail SMTP SUCCESS ===");
            } else {
                error_log("=== Gmail SMTP ALSO FAILED ===");
            }
        }
        
        error_log("=== EMAIL SENDING DEBUG END ===");
        
        return $result;
    }

    /**
     * Exact copy of AuthController's Gmail SMTP method
     */
    private function sendWithGmailSMTPLikeAuth(string $to, string $subject, string $message, string $fromEmail, string $fromName): bool
    {
        try {
            // Gmail SMTP settings
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com'; // Gmail address
            $smtpPassword = 'vbvp uokz yyfa hfnf';      // Gmail App Password
            
            // Create socket connection to Gmail SMTP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                error_log("SMTP connection failed: $errstr ($errno)");
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("SMTP initial response failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send EHLO command
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
            
            // Start TLS encryption
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("STARTTLS failed: $response");
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("Failed to enable TLS encryption");
                fclose($socket);
                return false;
            }
            
            // Send EHLO again after TLS
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                error_log("AUTH LOGIN failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send username (base64 encoded)
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                error_log("Username authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send password (base64 encoded)
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                error_log("Password authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("MAIL FROM failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("RCPT TO failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send DATA command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                error_log("DATA command failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send email headers and body
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$smtpUsername}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $message . "\r\n.\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("Email sending failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Gmail SMTP error: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Get debug information for email sending issues
     */
    private function getEmailDebugInfo(): string
    {
        $debugInfo = [];
        
        // Check if fsockopen is available
        if (!function_exists('fsockopen')) {
            $debugInfo[] = "fsockopen() function not available";
        }
        
        // Check if we can connect to Gmail SMTP
        $socket = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5);
        if (!$socket) {
            $debugInfo[] = "Cannot connect to Gmail SMTP (Error: $errno - $errstr)";
        } else {
            $debugInfo[] = "Gmail SMTP connection OK";
            fclose($socket);
        }
        
        // Check if TLS is supported
        if (!function_exists('stream_socket_enable_crypto')) {
            $debugInfo[] = "TLS encryption not supported";
        } else {
            $debugInfo[] = "TLS encryption supported";
        }
        
        // Check if mail() function is available
        if (!function_exists('mail')) {
            $debugInfo[] = "mail() function not available";
        } else {
            $debugInfo[] = "mail() function available";
        }
        
        return implode(', ', $debugInfo);
    }

    /**
     * Log appeal for manual review when email sending fails
     */
    private function logAppealForReview(string $subject, string $message, string $contactEmail, array $suspensionInfo): bool
    {
        $workerName = $suspensionInfo['worker_name'] ?? 'Unknown Worker';
        $workerEmail = $suspensionInfo['worker_email'] ?? 'Unknown Email';
        $workerId = $suspensionInfo['worker_id'] ?? 'Unknown ID';
        
        $appealData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => 'APPEAL_FOR_REVIEW',
            'worker_id' => $workerId,
            'worker_name' => $workerName,
            'worker_email' => $workerEmail,
            'contact_email' => $contactEmail ?: $workerEmail,
            'appeal_subject' => $subject,
            'appeal_message' => $message,
            'suspension_reason' => $suspensionInfo['reason'] ?? 'Unknown',
            'suspension_type' => $suspensionInfo['is_permanent'] ? 'Permanent' : 'Temporary',
            'suspended_until' => $suspensionInfo['suspended_until'] ?? 'N/A',
            'suspended_at' => $suspensionInfo['suspended_at'] ?? 'Unknown'
        ];
        
        $logFile = __DIR__ . '/../logs/appeals_for_review.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Log in a readable format
        $readableEntry = "\n" . str_repeat("=", 80) . "\n";
        $readableEntry .= "APPEAL SUBMITTED: " . date('Y-m-d H:i:s') . "\n";
        $readableEntry .= str_repeat("=", 80) . "\n";
        $readableEntry .= "Worker ID: " . $workerId . "\n";
        $readableEntry .= "Worker Name: " . $workerName . "\n";
        $readableEntry .= "Worker Email: " . $workerEmail . "\n";
        $readableEntry .= "Contact Email: " . ($contactEmail ?: $workerEmail) . "\n";
        $readableEntry .= "Appeal Type: " . $subject . "\n";
        $readableEntry .= "Suspension Reason: " . ($suspensionInfo['reason'] ?? 'Unknown') . "\n";
        $readableEntry .= "Suspension Type: " . ($suspensionInfo['is_permanent'] ? 'Permanent' : 'Temporary') . "\n";
        if (!$suspensionInfo['is_permanent']) {
            $readableEntry .= "Suspended Until: " . ($suspensionInfo['suspended_until'] ?? 'N/A') . "\n";
        }
        $readableEntry .= "\nAPPEAL MESSAGE:\n";
        $readableEntry .= str_repeat("-", 40) . "\n";
        $readableEntry .= $message . "\n";
        $readableEntry .= str_repeat("-", 40) . "\n";
        
        // Also log as JSON for programmatic access
        file_put_contents($logFile, json_encode($appealData) . "\n", FILE_APPEND | LOCK_EX);
        
        // Log readable version to a separate file
        $readableLogFile = __DIR__ . '/../logs/appeals_readable.log';
        file_put_contents($readableLogFile, $readableEntry, FILE_APPEND | LOCK_EX);
        
        error_log("Appeal logged for manual review - Worker: $workerName (ID: $workerId)");
        
        return true;
    }

    /**
     * Send appeal email
     */
    private function sendAppealEmailSimple(string $to, string $subject, string $message): bool
    {
        try {
            $fromEmail = 'kislaphelpdesk@gmail.com';
            $fromName = 'Kislap Photography Platform';
            
            return $this->sendWithGmailSMTPSimple($to, $subject, $message, $fromEmail, $fromName);
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Send email using Gmail SMTP
     */
    private function sendWithGmailSMTPSimple(string $to, string $subject, string $message, string $fromEmail, string $fromName): bool
    {
        try {
            // Gmail SMTP settings
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com';
            $smtpPassword = 'vbvp uokz yyfa hfnf';
            
            // Create socket connection to Gmail SMTP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            // Send EHLO command and read all response lines
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            // Start TLS encryption
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            
            // Send EHLO again after TLS and read all response lines
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            // Send username (base64 encoded)
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            // Send password (base64 encoded)
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send DATA command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                fclose($socket);
                return false;
            }
            
            // Send email headers and body
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$smtpUsername}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $message . "\r\n.\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Write debug information to custom log file
     */
    private function writeDebugLog(string $logFile, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Calculate earnings from completed bookings only (minus 30% platform fee)
     */
    private function getCompletedBookingsEarnings(int $workerId): float
    {
        try {
            // Use the repository's connection
            $conn = $this->repo->getConnection();
            
            $stmt = $conn->prepare("
                SELECT SUM(COALESCE(atb.final_price, 0)) as total_completed_revenue
                FROM conversations c
                JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE c.worker_id = ? 
                AND c.booking_status = 'completed'
                AND atb.final_price IS NOT NULL
                AND atb.final_price > 0
            ");
            
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalRevenue = floatval($result['total_completed_revenue'] ?? 0);
            
            // Deduct 30% platform fee
            $workerEarnings = $totalRevenue * 0.7;
            
            // Debug: Log detailed calculation
            error_log("DEBUG EARNINGS: Total Revenue = $totalRevenue, Worker Earnings (70%) = $workerEarnings");
            
            return $workerEarnings;
            
        } catch (Exception $e) {
            error_log("Error calculating completed bookings earnings: " . $e->getMessage());
            return 0.0;
        }
    }

}