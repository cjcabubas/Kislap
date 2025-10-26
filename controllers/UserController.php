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
        try {
            require_once __DIR__ . '/../config/dbconfig.php';
            
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(COUNT(DISTINCT c.conversation_id), 0) as total_bookings,
                    COALESCE(COUNT(DISTINCT CASE WHEN c.booking_status = 'completed' THEN c.conversation_id END), 0) as completed_bookings,
                    COALESCE(COUNT(DISTINCT CASE WHEN c.booking_status IN ('confirmed', 'negotiating') THEN c.conversation_id END), 0) as active_bookings,
                    COALESCE(COUNT(DISTINCT CASE WHEN c.booking_status = 'cancelled' THEN c.conversation_id END), 0) as cancelled_bookings,
                    COALESCE(COUNT(DISTINCT r.rating_id), 0) as total_reviews,
                    COALESCE(AVG(r.rating), 0) as average_rating_given,
                    COALESCE(SUM(CASE WHEN atb.final_price > 0 THEN atb.final_price ELSE atb.budget END), 0) as total_spent
                FROM user u
                LEFT JOIN conversations c ON u.user_id = c.user_id
                LEFT JOIN ratings r ON c.conversation_id = r.conversation_id AND r.user_id = u.user_id
                LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE u.user_id = ?
            ");
            
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'active_bookings' => 0,
                'cancelled_bookings' => 0,
                'total_reviews' => 0,
                'average_rating_given' => 0,
                'total_spent' => 0
            ];
        } catch (Exception $e) {
            error_log("Error fetching user statistics: " . $e->getMessage());
            return [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'active_bookings' => 0,
                'cancelled_bookings' => 0,
                'total_reviews' => 0,
                'average_rating_given' => 0,
                'total_spent' => 0
            ];
        }
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

            // Verify current password
            $userRepo = new UserRepository();
            $user = $userRepo->getUserById($userId);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect.');
            }

            // Check if new password is different from current
            if (password_verify($newPassword, $user['password'])) {
                throw new Exception('New password must be different from your current password.');
            }

            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password in database
            require_once __DIR__ . '/../config/dbconfig.php';
            $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);

            if (!$result) {
                throw new Exception('Failed to update password. Please try again.');
            }

            $_SESSION['success'] = 'Password changed successfully!';
            
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

            // Store support ticket in database
            require_once __DIR__ . '/../config/dbconfig.php';
            
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, user_email, user_name, subject, message, priority, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())
            ");
            
            $result = $stmt->execute([
                $userId,
                $userEmail,
                $userName,
                $subject,
                $message,
                $priority
            ]);

            if (!$result) {
                throw new Exception('Failed to submit support request. Please try again.');
            }

            // Get the ticket ID for reference
            $ticketId = $pdo->lastInsertId();

            $_SESSION['success'] = "Support ticket #{$ticketId} submitted successfully! We'll get back to you soon.";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?controller=User&action=profile");
        exit;
    }
}