<?php

class RatingRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function saveRating(int $conversationId, int $userId, int $workerId, int $rating, string $review): bool
    {
        try {
            $this->conn->beginTransaction();

            // Insert rating
            $stmt = $this->conn->prepare(
                "INSERT INTO ratings (conversation_id, user_id, worker_id, rating, review, created_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$conversationId, $userId, $workerId, $rating, $review]);

            // Update temp booking with rated_at timestamp
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings SET rated_at = NOW() WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            // Update worker's average rating
            $this->updateWorkerAverageRating($workerId);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error saving rating: " . $e->getMessage());
            return false;
        }
    }

    public function hasUserRated(int $conversationId, int $userId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM ratings WHERE conversation_id = ? AND user_id = ?"
        );
        $stmt->execute([$conversationId, $userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getWorkerRatings(int $workerId, int $limit = 10): array
    {
        // Validate limit to prevent SQL injection
        $limit = max(1, min(100, $limit)); // Between 1 and 100
        
        $stmt = $this->conn->prepare(
            "SELECT r.*, 
                    CONCAT(u.firstName, ' ', u.lastName) as user_name,
                    u.profilePhotoUrl as user_photo
             FROM ratings r
             INNER JOIN user u ON r.user_id = u.user_id
             WHERE r.worker_id = ?
             ORDER BY r.created_at DESC
             LIMIT " . $limit
        );
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkerRatingStats(int $workerId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) as total_ratings,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
             FROM ratings
             WHERE worker_id = ?"
        );
        $stmt->execute([$workerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_ratings' => 0,
            'average_rating' => 0,
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
    }

    private function updateWorkerAverageRating(int $workerId): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE workers 
             SET average_rating = (
                 SELECT AVG(rating) 
                 FROM ratings 
                 WHERE worker_id = ?
             ),
             total_ratings = (
                 SELECT COUNT(*) 
                 FROM ratings 
                 WHERE worker_id = ?
             )
             WHERE worker_id = ?"
        );
        $stmt->execute([$workerId, $workerId, $workerId]);
    }

    public function getRatingByConversation(int $conversationId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM ratings WHERE conversation_id = ? LIMIT 1"
        );
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function checkIfRated(int $conversationId): bool
    {
        // Check both ai_temp_bookings and ratings table
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM ratings WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        return $stmt->fetchColumn() > 0;
    }
}