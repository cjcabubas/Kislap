<?php

class PaymentController
{
    public function payDeposit()
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
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $tempBooking = $chatRepo->getTempBooking($conversationId);
        $finalPrice = $tempBooking['final_price'] ?? $tempBooking['budget'] ?? 0;
        $depositAmount = $finalPrice * 0.5; // 50% down payment
        
        // Update deposit as paid
        $stmt = $chatRepo->conn->prepare(
            "UPDATE ai_temp_bookings 
             SET deposit_amount = ?, 
                 deposit_paid = TRUE, 
                 deposit_paid_at = NOW() 
             WHERE conversation_id = ?"
        );
        
        if ($stmt->execute([$depositAmount, $conversationId])) {
            echo json_encode([
                'success' => true, 
                'message' => 'Deposit paid successfully!',
                'amount' => $depositAmount
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Payment failed']);
        }
        exit;
    }
    
    public function payFull()
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
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        // Mark booking as completed and paid
        $stmt = $chatRepo->conn->prepare(
            "UPDATE conversations SET booking_status = 'completed' WHERE conversation_id = ?"
        );
        
        if ($stmt->execute([$conversationId])) {
            echo json_encode(['success' => true, 'message' => 'Full payment received! Booking completed.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Payment failed']);
        }
        exit;
    }
}
