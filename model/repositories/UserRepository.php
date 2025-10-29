<?php

require_once __DIR__ . '/BaseRepository.php';

class UserRepository extends BaseRepository
{
    public function __construct() {
        parent::__construct();
    }

    public function updateUser(int $userId, array $user): bool
    {
        $sql = "UPDATE user 
                SET firstName = :firstName,
                    middleName = :middleName,
                    lastName = :lastName,
                    phoneNumber = :phoneNumber,
                    address = :address,
                    profilePhotoUrl = :profilePhotoUrl
                WHERE user_id = :userId";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':firstName' => $user['firstName'],
            ':middleName' => $user['middleName'],
            ':lastName' => $user['lastName'],
            ':phoneNumber' => $user['phoneNumber'],
            ':address' => $user['address'],
            ':profilePhotoUrl' => $user['profilePhotoUrl'] ?? null,
            ':userId' => $userId
        ]);
    }

    public function getUserById(int $userId): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error fetching user by ID: " . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error finding user by email: " . $e->getMessage());
            return null;
        }
    }

    public function findByPhoneNumber(string $phoneNumber): ?array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user WHERE phoneNumber = ?");
            $stmt->execute([$phoneNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error finding user by phone: " . $e->getMessage());
            return null;
        }
    }

    public function updateUserPassword(int $userId, string $hashedPassword): bool
    {
        try {
            $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (Exception $e) {
            error_log("Error updating user password: " . $e->getMessage());
            return false;
        }
    }



    private function createSupportTicketsTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS support_tickets (
                ticket_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
                status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
            )";
            
            $this->conn->exec($sql);
        } catch (Exception $e) {
            error_log("Error creating support_tickets table: " . $e->getMessage());
        }
    }

    public function changeUserPassword(int $userId, string $currentPassword, string $newPassword): array
    {
        try {
            // Get current user data
            $user = $this->getUserById($userId);
            
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Current password is incorrect.'];
            }

            // Check if new password is different from current
            if (password_verify($newPassword, $user['password'])) {
                return ['success' => false, 'message' => 'New password must be different from your current password.'];
            }

            // Hash new password and update
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $result = $stmt->execute([$hashedPassword, $userId]);

            if (!$result) {
                return ['success' => false, 'message' => 'Failed to update password. Please try again.'];
            }

            return ['success' => true, 'message' => 'Password changed successfully!'];
            
        } catch (Exception $e) {
            error_log("Error changing user password: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while changing password.'];
        }
    }

    public function createSupportTicket(int $userId, string $userEmail, string $userName, string $subject, string $message, string $priority): ?int
    {
        try {
            $stmt = $this->conn->prepare("
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

            if ($result) {
                return $this->conn->lastInsertId();
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error creating support ticket: " . $e->getMessage());
            return null;
        }
    }

    public function getUserStatistics(int $userId): array
    {
        try {
            // Initialize default booking statistics
            $stats = [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'active_bookings' => 0,
                'total_reviews' => 0,
                'total_spent' => 0,
                'average_rating_given' => 0
            ];

            // Get booking statistics
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total_bookings,
                    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                    SUM(CASE WHEN booking_status IN ('confirmed', 'in_progress') THEN 1 ELSE 0 END) as active_bookings,
                    SUM(CASE WHEN booking_status = 'completed' THEN total_amount ELSE 0 END) as total_spent
                FROM bookings 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
            $bookingStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($bookingStats) {
                $stats['total_bookings'] = (int)$bookingStats['total_bookings'];
                $stats['completed_bookings'] = (int)$bookingStats['completed_bookings'];
                $stats['active_bookings'] = (int)$bookingStats['active_bookings'];
                $stats['total_spent'] = (float)$bookingStats['total_spent'];
            }

            // Get review statistics
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating_given
                FROM reviews 
                WHERE user_id = ?
            ");
            
            $stmt->execute([$userId]);
            $reviewStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reviewStats) {
                $stats['total_reviews'] = (int)$reviewStats['total_reviews'];
                $stats['average_rating_given'] = (float)$reviewStats['average_rating_given'];
            }

            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'active_bookings' => 0,
                'total_reviews' => 0,
                'total_spent' => 0,
                'average_rating_given' => 0
            ];
        }
    }
}
