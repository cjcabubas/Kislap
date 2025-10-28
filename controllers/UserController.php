<?php
require_once __DIR__ . '/../model/repositories/UserRepository.php';


class UserController
{
    // ========================================
    // PROFILE MANAGEMENT
    // ========================================
    
    public function profile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $userRepo = new UserRepository();
        $freshUserData = $userRepo->getUserById($user['user_id']);
        
        if ($freshUserData) {
            $user = $freshUserData;
            $_SESSION['user'] = $user;
        }

        $photoPath = $user['profilePhotoUrl'] ?? '';
        if (!$photoPath || !file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath)) {
            unset($user['profilePhotoUrl']);
        }

        $userStats = $this->getUserStatistics($user['user_id']);
        
        // Debug: Log the user stats to see what's being returned
        error_log("DEBUG UserController: userStats = " . print_r($userStats, true));
        
        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        $recentBookings = $chatRepo->getUserBookings($user['user_id'], null);
        $recentBookings = array_slice($recentBookings, 0, 5);

        require "views/user/profile.php";
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    private function getUserStatistics(int $userId): array
    {
        require_once __DIR__ . '/../model/repositories/UserRepository.php';
        $userRepo = new UserRepository();
        return $userRepo->getUserStatistics($userId);
    }

    private function uploadProfilePhoto(array $file, int $userId): ?string
    {
        require_once __DIR__ . '/../model/Validator.php';
        
        // Debug: Log file info
        error_log("DEBUG: Upload attempt - File name: " . $file['name'] . ", Size: " . $file['size'] . ", Error: " . $file['error']);
        
        $validation = Validator::validateFile($file, 'profile_photo');
        if (!$validation['valid']) {
            error_log("DEBUG: Validation failed: " . $validation['message']);
            throw new Exception('Profile photo validation failed: ' . $validation['message']);
        }

        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/Kislap/uploads/user/profile_photos/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Check if directory is writable
        if (!is_writable($targetDir)) {
            throw new Exception('Upload directory is not writable');
        }

        $secureFilename = Validator::generateSecureFilename($file['name'], "profile_{$userId}_");
        $targetPath = $targetDir . $secureFilename;
        $webPath = '/Kislap/uploads/user/profile_photos/' . $secureFilename;

        // Debug: Log paths
        error_log("DEBUG: Target path: " . $targetPath);
        error_log("DEBUG: Web path: " . $webPath);

        // Remove old photo if exists
        if (!empty($_SESSION['user']['profilePhotoUrl'])) {
            $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user']['profilePhotoUrl'];
            $defaultPath = '/Kislap/public/images/user/default-profile.webp';
            
            if (file_exists($oldPhotoPath) && $_SESSION['user']['profilePhotoUrl'] !== $defaultPath) {
                unlink($oldPhotoPath);
                error_log("DEBUG: Removed old photo: " . $oldPhotoPath);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            error_log("DEBUG: Photo uploaded successfully to: " . $targetPath);
            return $webPath;
        }

        error_log("DEBUG: Failed to move uploaded file");
        throw new Exception('Failed to upload profile photo');
    }

    public function updateProfile(): void
    {
        // Set proper content type for JSON response
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            return;
        }

        try {
            $userId = $_SESSION['user']['user_id'];

            $firstName = $_POST['firstName'] ?? '';
            $middleName = $_POST['middleName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $phoneNumber = $_POST['phoneNumber'] ?? '';
            $address = $_POST['address'] ?? '';

            // Basic validation
            if (empty($firstName) || empty($lastName)) {
                throw new Exception('First name and last name are required');
            }

            $photoPath = null;
            if (!empty($_FILES['profilePhotoUrl']['name'])) {
                try {
                    $photoPath = $this->uploadProfilePhoto($_FILES['profilePhotoUrl'], $userId);
                } catch (Exception $e) {
                    throw new Exception('Photo upload failed: ' . $e->getMessage());
                }
            }

            $repo = new UserRepository();
            $updateData = [
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'phoneNumber' => $phoneNumber,
                'address' => $address,
            ];

            if ($photoPath) {
                $updateData['profilePhotoUrl'] = $photoPath;
            }

            $update = $repo->updateUser($userId, $updateData);

            if ($update) {
                // Update session data
                $_SESSION['user']['firstName'] = $firstName;
                $_SESSION['user']['middleName'] = $middleName;
                $_SESSION['user']['lastName'] = $lastName;
                $_SESSION['user']['phoneNumber'] = $phoneNumber;
                $_SESSION['user']['address'] = $address;
                
                if ($photoPath) {
                    $_SESSION['user']['profilePhotoUrl'] = $photoPath;
                }

                // Debug: Log the photo path
                error_log("DEBUG: Photo path saved: " . ($photoPath ?? 'none'));
                error_log("DEBUG: Session photo URL: " . ($_SESSION['user']['profilePhotoUrl'] ?? 'none'));
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile updated successfully',
                    'photoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null,
                    'photoUploaded' => !empty($photoPath),
                    'debug' => [
                        'photoPath' => $photoPath,
                        'sessionPhotoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null
                    ]
                ]);
            } else {
                throw new Exception('Failed to update profile in database');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function changePassword(): void
    {
        error_log("DEBUG: changePassword method called");
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            error_log("DEBUG: User not logged in");
            $_SESSION['error'] = 'You must be logged in to change your password.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header("Location: index.php?controller=User&action=profile");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        error_log("DEBUG: POST data - current: " . (!empty($currentPassword) ? 'provided' : 'empty') . 
                  ", new: " . (!empty($newPassword) ? 'provided' : 'empty') . 
                  ", confirm: " . (!empty($confirmPassword) ? 'provided' : 'empty'));

        try {
            // Validate input fields
            if (empty($currentPassword)) {
                throw new Exception('Current password is required.');
            }

            if (empty($newPassword)) {
                throw new Exception('New password is required.');
            }

            if (empty($confirmPassword)) {
                throw new Exception('Password confirmation is required.');
            }

            // Check if new passwords match
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            // Simple password validation - just minimum length
            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters long.');
            }

            // Verify current password and update if valid
            $userRepo = new UserRepository();
            $result = $userRepo->changeUserPassword($userId, $currentPassword, $newPassword);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Password changed successfully!';
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?controller=User&action=profile");
        exit;
    }

    public function contactSupport(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'You must be logged in to contact support.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header("Location: index.php?controller=User&action=profile");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userEmail = $_SESSION['user']['email'];
        $userName = $_SESSION['user']['firstName'] . ' ' . $_SESSION['user']['lastName'];
        
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        try {
            // Validate input fields
            if (empty($subject)) {
                throw new Exception('Please select a subject for your support request.');
            }

            if (empty($message) || strlen(trim($message)) < 10) {
                throw new Exception('Please provide a detailed message (at least 10 characters).');
            }

            // Sanitize input
            require_once __DIR__ . '/../model/Validator.php';
            $subject = Validator::sanitizeInput($subject);
            $message = Validator::sanitizeInput($message);
            $priority = Validator::sanitizeInput($priority);

            // Store support ticket using repository
            $userRepo = new UserRepository();
            $ticketId = $userRepo->createSupportTicket($userId, $userEmail, $userName, $subject, $message, $priority);

            if ($ticketId) {
                $_SESSION['success'] = "Support ticket #{$ticketId} submitted successfully! We'll get back to you soon.";
            } else {
                throw new Exception('Failed to submit support request. Please try again.');
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?controller=User&action=profile");
        exit;
    }
}