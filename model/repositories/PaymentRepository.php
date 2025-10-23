<?php

require_once __DIR__ . '/Repository.php'; // Your base repository with the DB connection

class PaymentRepository extends Repository
{
    /**
     * Updates the temporary booking to mark the deposit as paid.
     *
     * @param string $conversationId The ID of the conversation.
     * @param float  $depositAmount  The calculated deposit amount.
     * @return bool True on success, false on failure.
     */
    public function markDepositAsPaid(string $conversationId, float $depositAmount): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
                 SET deposit_amount = ?, 
                     deposit_paid = TRUE, 
                     deposit_paid_at = NOW() 
                 WHERE conversation_id = ?"
            );

            return $stmt->execute([$depositAmount, $conversationId]);

        } catch (PDOException $e) {
            // In a real app, you would log the error $e->getMessage()
            return false;
        }
    }

    /**
     * Updates the main conversation to mark the booking as 'completed'.
     */
    public function markBookingAsCompleted(string $conversationId): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE conversations 
                 SET booking_status = 'completed' 
                 WHERE conversation_id = ?"
            );

            return $stmt->execute([$conversationId]);

        } catch (PDOException $e) {
            // In a real app, you would log the error $e->getMessage()
            return false;
        }
    }
}