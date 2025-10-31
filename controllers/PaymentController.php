<?php

require_once __DIR__ . '/../model/repositories/PaymentRepository.php';
require_once __DIR__ . '/../model/repositories/ChatRepository.php';

class PaymentController
{
    private PaymentRepository $paymentRepo;
    private ChatRepository $chatRepo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->paymentRepo = new PaymentRepository();
        $this->chatRepo = new ChatRepository();
    }

    public function payDeposit()
    {
        header('Content-Type: application/json');

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

        $totalAmount = $this->paymentRepo->getBookingAmount($conversationId);
        if (!$totalAmount) {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }

        $depositAmount = $totalAmount * 0.5;

        if ($this->paymentRepo->processDepositPayment($conversationId)) {
            $this->chatRepo->saveMessage(
                $conversationId,
                $user['user_id'],
                'user',
                "✅ Down payment of ₱" . number_format($depositAmount, 2) . " has been processed successfully! The photographer has been notified."
            );

            echo json_encode([
                'success' => true,
                'amount' => $depositAmount,
                'message' => 'Down payment processed successfully!'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to process payment']);
        }
        exit;
    }

    public function payFull()
    {
        header('Content-Type: application/json');

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

        $totalAmount = $this->paymentRepo->getBookingAmount($conversationId);
        if (!$totalAmount) {
            echo json_encode(['success' => false, 'error' => 'Booking not found']);
            exit;
        }

        $remainingBalance = $totalAmount * 0.5;

        if ($this->paymentRepo->processFullPayment($conversationId)) {

            $this->chatRepo->saveMessage(
                $conversationId,
                $user['user_id'],
                'user',
                "🎉 Final payment of ₱" . number_format($remainingBalance, 2) . " has been processed. Thank you for using our service! Please rate your experience."
            );

            echo json_encode([
                'success' => true,
                'amount' => $remainingBalance,
                'message' => 'Booking completed successfully! Please rate your experience.'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to complete booking']);
        }
        exit;
    }

}