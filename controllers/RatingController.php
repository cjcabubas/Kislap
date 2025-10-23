<?php

// Include all necessary repositories
require_once __DIR__ . '/../model/repositories/ChatRepository.php';
require_once __DIR__ . '/../model/repositories/RatingRepository.php';

class RatingController
{
    private $chatRepo;
    private $ratingRepo;

    public function __construct()
    {
        $this->chatRepo = new ChatRepository();
        $this->ratingRepo = new RatingRepository();
    }

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

        try {
            // 1. Get required data from a repository (ChatRepository)
            $conversation = $this->chatRepo->getConversationById($conversationId);

            if (!$conversation || !isset($conversation['worker_id'])) {
                echo json_encode(['success' => false, 'error' => 'Conversation or worker not found.']);
                exit;
            }

            $workerId = $conversation['worker_id'];
            $userId = $user['user_id'];

            // 2. Delegate the main database action to the new repository (RatingRepository)
            $success = $this->ratingRepo->submitRating(
                $conversationId,
                $userId,
                $workerId,
                (int)$rating, // Ensure rating is an integer
                $review
            );

            // 3. Send response
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Rating submitted successfully!']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to submit rating']);
            }

        } catch (Exception $e) {
            // Log the exception
            echo json_encode(['success' => false, 'error' => 'An unexpected error occurred.']);
        }
        exit;
    }
}