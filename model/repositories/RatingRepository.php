<?php

require_once __DIR__ . '/Repository.php'; // Assuming a base repository for DB connection

class RatingRepository extends Repository
{
    /**
     * Inserts a new rating or updates an existing one for a conversation.
     */
    public function submitRating(string $conversationId, int $userId, int $workerId, int $rating, string $review): bool
    {
        try {
            // The SQL uses ON DUPLICATE KEY UPDATE to handle both inserts and updates
            $sql = "INSERT INTO ratings (conversation_id, user_id, worker_id, rating, review) 
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), submitted_at = NOW()";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                $conversationId,
                $userId,
                $workerId,
                $rating,
                $review
                // Note: No need to pass $rating and $review twice in PDO for VALUES() reference
                // if we use VALUES(column_name) in ON DUPLICATE KEY UPDATE clause.
            ]);

        } catch (PDOException $e) {
            // Log the error $e->getMessage()
            return false;
        }
    }
}