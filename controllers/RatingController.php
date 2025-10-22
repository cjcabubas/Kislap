<?php

class RatingController
{
    public function submitRating()
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $rating = $_POST['rating'] ?? null;
        $review = $_POST['review'] ?? '';
        
        if (!$conversationId || !$rating) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $conversation = $chatRepo->getConversationById($conversationId);
        
        // Insert rating
        $stmt = $chatRepo->conn->prepare(
            "INSERT INTO ratings (conversation_id, user_id, worker_id, rating, review) 
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = ?, review = ?"
        );
        
        if ($stmt->execute([
            $conversationId, 
            $user['user_id'], 
            $conversation['worker_id'], 
            $rating, 
            $review,
            $rating,
            $review
        ])) {
            echo json_encode(['success' => true, 'message' => 'Rating submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to submit rating']);
        }
        exit;
    }
}
