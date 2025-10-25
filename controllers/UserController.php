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
                    COUNT(DISTINCT c.conversation_id) as total_bookings,
                    COUNT(DISTINCT CASE WHEN c.booking_status = 'completed' THEN c.conversation_id END) as completed_bookings,
                    COUNT(DISTINCT CASE WHEN c.booking_status IN ('confirmed', 'negotiating') THEN c.conversation_id END) as active_bookings,
                    COUNT(DISTINCT CASE WHEN c.booking_status = 'cancelled' THEN c.conversation_id END) as cancelled_bookings,
                    COUNT(DISTINCT r.rating_id) as total_reviews,
                    AVG(r.rating) as average_rating_given,
                    SUM(COALESCE(atb.final_price, atb.budget, 0)) as total_spent
                FROM user u
                LEFT JOIN conversations c ON u.user_id = c.user_id
                LEFT JOIN ratings r ON c.conversation_id = r.conversation_id AND r.user_id = u.user_id
                LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE u.user_id = ?
                GROUP BY u.user_id
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
        
        $validation = Validator::validateFile($file, 'profile_photo');
        if (!$validation['valid']) {
            throw new Exception('Profile photo validation failed: ' . $validation['message']);
        }

        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/Kislap/uploads/user/profile_photos/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $secureFilename = Validator::generateSecureFilename($file['name'], "profile_{$userId}_");
        $targetPath = $targetDir . $secureFilename;

        if (!empty($_SESSION['user']['profilePhotoUrl'])) {
            $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user']['profilePhotoUrl'];

            $defaultPath = '/Kislap/public/images/user/default-profile.webp';
            if (file_exists($oldPhotoPath) && $_SESSION['user']['profilePhotoUrl'] !== $defaultPath) {
                unlink($oldPhotoPath);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            return '/Kislap/uploads/user/profile_photos/' . $secureFilename;
        }

        throw new Exception('Failed to upload profile photo');
    }

    public function updateProfile(): void
    {
        if (!isset($_SESSION['user'])) {
            echo "unauthorized";
            return;
        }

        $userId = $_SESSION['user']['user_id'];

        $firstName = $_POST['firstName'] ?? '';
        $middleName = $_POST['middleName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $phoneNumber = $_POST['phoneNumber'] ?? '';
        $address = $_POST['address'] ?? '';

        $photoPath = null;
        if (!empty($_FILES['profilePhotoUrl']['name'])) {
            $photoPath = $this->uploadProfilePhoto($_FILES['profilePhotoUrl'], $userId);
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
            $_SESSION['user']['profilePhotoUrl'] = $photoPath;
        }

        $update = $repo->updateUser($userId, $updateData);

        if ($update) {
            $_SESSION['user']['firstName'] = $firstName;
            $_SESSION['user']['middleName'] = $middleName;
            $_SESSION['user']['lastName'] = $lastName;
            $_SESSION['user']['phoneNumber'] = $phoneNumber;
            $_SESSION['user']['address'] = $address;

            echo json_encode(['success' => true, 'photoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
