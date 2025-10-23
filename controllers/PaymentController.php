<?php

// Includes all repositories the controller needs
require_once __DIR__ . '/../model/repositories/PaymentRepository.php';
require_once __DIR__ . '/../model/repositories/ChatRepository.php';

class PaymentController
{
    private $paymentRepo;
    private $chatRepo;

    /**
     * Constructor to instantiate repositories, just like in AuthController
     */
    public function __construct()
    {
        $this->paymentRepo = new PaymentRepository();
        $this->chatRepo = new ChatRepository(); // Needed to get booking details
    }

    public function payDeposit()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Handle session and authentication
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        // 2. Get POST data
        $conversationId = $_POST['conversation_id'] ?? null;
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        try {
            // 3. Business Logic (get price, calculate deposit)
            $tempBooking = $this->chatRepo->getTempBooking($conversationId);
            $finalPrice = $tempBooking['final_price'] ?? $tempBooking['budget'] ?? 0;
            $depositAmount = $finalPrice * 0.5; // 50% down payment

            // 4. Call Repository to save data
            $success = $this->paymentRepo->markDepositAsPaid($conversationId, $depositAmount);

            // 5. Send response
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Deposit paid successfully!',
                    'amount' => $depositAmount
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Payment failed']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
        }
        exit;
    }

    public function payFull()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Handle session and authentication
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        // 2. Get POST data
        $conversationId = $_POST['conversation_id'] ?? null;
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        try {
            // 3. Call Repository to save data
            $success = $this->paymentRepo->markBookingAsCompleted($conversationId);

            // 4. Send response
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Full payment received! Booking completed.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Payment failed']);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
        }
        exit;
    }
}