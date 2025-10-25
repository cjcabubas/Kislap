<?php

require_once __DIR__ . '/../model/repositories/RatingRepository.php';
require_once __DIR__ . '/../model/repositories/ChatRepository.php';

class RatingController
{
    private RatingRepository $ratingRepo;
    private ChatRepository $chatRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->ratingRepo = new RatingRepository();
        $this->chatRepo = new ChatRepository();
    }

    public function submitRating()
    {
        header('Content-Type: application/json');

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

        // Validate rating is between 1-5
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'error' => 'Invalid rating value']);
            exit;
        }

        // Check if already rated
        if ($this->ratingRepo->hasUserRated($conversationId, $user['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'You have already rated this service']);
            exit;
        }

        // Get worker_id from conversation
        $conversation = $this->chatRepo->getConversationById($conversationId);
        if (!$conversation) {
            echo json_encode(['success' => false, 'error' => 'Conversation not found']);
            exit;
        }

        $workerId = $conversation['worker_id'];

        // Save rating
        if ($this->ratingRepo->saveRating($conversationId, $user['user_id'], $workerId, $rating, $review)) {
            // Send thank you message
            $this->chatRepo->saveMessage(
                $conversationId,
                $user['user_id'],
                'user',
                "⭐ I've rated this service {$rating}/5 stars. " .
                ($review ? "Review: {$review}" : "Thank you for the excellent service!")
            );

            echo json_encode([
                'success' => true,
                'message' => 'Thank you for your feedback!'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save rating']);
        }
        exit;
    }

    public function getWorkerRatings()
    {
        $workerId = $_GET['worker_id'] ?? null;

        if (!$workerId) {
            echo json_encode(['success' => false, 'error' => 'Worker ID required']);
            exit;
        }

        $ratings = $this->ratingRepo->getWorkerRatings($workerId);
        $stats = $this->ratingRepo->getWorkerRatingStats($workerId);

        echo json_encode([
            'success' => true,
            'ratings' => $ratings,
            'stats' => $stats
        ]);
        exit;
    }
}