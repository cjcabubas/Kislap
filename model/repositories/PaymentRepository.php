<?php

require_once __DIR__ . '/BaseRepository.php';

class PaymentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    public function processDepositPayment(int $conversationId): bool
    {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
             SET deposit_paid = 1,
                 deposit_paid_at = NOW(),
                 deposit_amount = (final_price * 0.5)
             WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            // Keep status as confirmed after deposit
            $stmt = $this->conn->prepare(
                "UPDATE conversations 
             SET booking_status = 'confirmed'
             WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error processing deposit: " . $e->getMessage());
            return false;
        }
    }

    public function processFullPayment(int $conversationId): bool
    {
        try {
            $this->conn->beginTransaction();

            // Get worker ID for statistics update
            $stmt = $this->conn->prepare(
                "SELECT c.worker_id FROM conversations c WHERE c.conversation_id = ?"
            );
            $stmt->execute([$conversationId]);
            $workerId = $stmt->fetchColumn();

            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
             SET full_payment_paid = 1,
                 completed_at = NOW(),
                 full_payment_paid_at = NOW()
             WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            // Update status to completed
            $stmt = $this->conn->prepare(
                "UPDATE conversations 
             SET booking_status = 'completed'
             WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            $this->conn->commit();

            // Update worker statistics after successful completion
            if ($workerId) {
                require_once __DIR__ . '/WorkerRepository.php';
                $workerRepo = new WorkerRepository();
                $workerRepo->updateWorkerStatistics($workerId);
                error_log("Updated statistics for worker ID: $workerId after booking completion");
            }

            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error processing full payment: " . $e->getMessage());
            return false;
        }
    }


    public function getBookingAmount(int $conversationId): ?float
    {
        $stmt = $this->conn->prepare(
            "SELECT final_price, budget FROM ai_temp_bookings WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) return null;

        return $booking['final_price'] ?? $booking['budget'] ?? 0;
    }
}