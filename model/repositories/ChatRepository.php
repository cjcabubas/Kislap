<?php

require_once __DIR__ . '/BaseRepository.php';

class ChatRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    // ========================================
    // CONVERSATION MANAGEMENT
    // ========================================

    public function findConversation(int $userId, int $workerId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM conversations 
             WHERE user_id = ? AND worker_id = ?
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute([$userId, $workerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findIncompleteBooking(int $userId, int $workerId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.* FROM conversations c
         WHERE c.user_id = ? AND c.worker_id = ?
         AND c.booking_status NOT IN ('completed', 'rated', 'cancelled')
         ORDER BY c.created_at DESC
         LIMIT 1"
        );
        $stmt->execute([$userId, $workerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function hasCompletedBooking(int $userId, int $workerId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM conversations c
         WHERE c.user_id = ? AND c.worker_id = ?
         AND c.booking_status IN ('completed', 'rated')"
        );
        $stmt->execute([$userId, $workerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    public function hasUserBookedWorker(int $userId, int $workerId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM conversations c
         WHERE c.user_id = ? AND c.worker_id = ?"
        );
        $stmt->execute([$userId, $workerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['count'] ?? 0) > 0;
    }

    public function createAiConversation(int $userId, int $workerId): int
    {
        try {
            $this->conn->beginTransaction();

            // Create conversation
            $stmt = $this->conn->prepare(
                "INSERT INTO conversations (user_id, worker_id, type, booking_status) 
                 VALUES (?, ?, 'ai', 'collecting_info')"
            );
            $stmt->execute([$userId, $workerId]);
            $conversationId = (int)$this->conn->lastInsertId();

            // Create temporary booking holder
            $stmt = $this->conn->prepare(
                "INSERT INTO ai_temp_bookings (conversation_id, worker_id) VALUES (?, ?)"
            );
            $stmt->execute([$conversationId, $workerId]);

            // Send welcome message
            $welcomeMessage = "Hi! I'm here to help you book a photographer. 📸\n\n" .
                "What type of event are you planning?\n" .
                "(e.g., Wedding, Birthday, Portrait, Corporate)";

            $this->saveMessage($conversationId, 0, 'bot', $welcomeMessage);

            $this->conn->commit();
            return $conversationId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error creating AI conversation: " . $e->getMessage());
            return 0;
        }
    }

    public function getConversationById(int $conversationId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM conversations WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getConversationsForUser(int $userId, string $userType): array
    {
        $idColumn = $userType === 'user' ? 'c.user_id' : 'c.worker_id';
        $otherUserJoin = $userType === 'user'
            ? "JOIN workers w ON c.worker_id = w.worker_id"
            : "JOIN user u ON c.user_id = u.user_id";

        $otherUserName = $userType === 'user'
            ? "CONCAT(w.firstName, ' ', w.lastName)"
            : "CONCAT(u.firstName, ' ', u.lastName)";

        $otherUserPhoto = $userType === 'user'
            ? "w.profile_photo AS worker_profile_photo"
            : "u.profilePhotoUrl AS user_profile_photo";

        $sql = "
            SELECT 
                c.conversation_id,
                c.type,
                $otherUserName AS other_user_name,
                $otherUserPhoto,
                lm.message_text AS last_message,
                lm.sent_at AS last_message_time,
                (SELECT COUNT(*) FROM messages m 
                 WHERE m.conversation_id = c.conversation_id 
                 AND m.is_read = 0 
                 AND m.sender_type != ?) AS unread_count
            FROM conversations c
            $otherUserJoin
            LEFT JOIN (
                SELECT conversation_id, message_text, sent_at
                FROM messages
                WHERE (conversation_id, message_id) IN (
                    SELECT conversation_id, MAX(message_id)
                    FROM messages
                    GROUP BY conversation_id
                )
            ) AS lm ON c.conversation_id = lm.conversation_id
            WHERE $idColumn = ?
            GROUP BY c.conversation_id
            ORDER BY lm.sent_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userType, $userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Log the SQL query and results
        error_log("DEBUG: getConversationsForUser SQL: " . $sql);
        error_log("DEBUG: getConversationsForUser params: userType=$userType, userId=$userId");
        error_log("DEBUG: getConversationsForUser result: " . print_r($result, true));
        
        return $result;
    }

    public function updateConversationType(int $conversationId, string $type): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET type = ? WHERE conversation_id = ?"
        );
        return $stmt->execute([$type, $conversationId]);
    }

    public function updateConversationStatus(int $conversationId, string $status): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = ? WHERE conversation_id = ?"
        );
        return $stmt->execute([$status, $conversationId]);
    }

    // ========================================
    // MESSAGE MANAGEMENT
    // ========================================

    public function saveMessage(int $conversationId, int $senderId, string $senderType, string $messageText): ?array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO messages (conversation_id, sender_id, sender_type, message_text) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$conversationId, $senderId, $senderType, $messageText]);
        $messageId = $this->conn->lastInsertId();

        return $this->getMessageById($messageId);
    }

    public function getMessageById(int $messageId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM messages WHERE message_id = ?");
        $stmt->execute([$messageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getMessagesForConversation(int $conversationId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC"
        );
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewMessages(int $conversationId, int $lastMessageId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM messages 
             WHERE conversation_id = ? AND message_id > ? 
             ORDER BY sent_at ASC"
        );
        $stmt->execute([$conversationId, $lastMessageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================
    // RECIPIENT INFO
    // ========================================

    public function getRecipientInfo(int $id, string $userType): ?array
    {
        $table = $userType === 'user' ? 'workers' : 'user';
        $idColumn = $userType === 'user' ? 'worker_id' : 'user_id';

        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE $idColumn = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return null;

        return [
            'name' => ($result['firstName'] ?? '') . ' ' . ($result['lastName'] ?? ''),
            'profile_picture' => $result['profile_photo'] ?? $result['profilePhotoUrl'] ?? null,
            'is_online' => false
        ];
    }

    // ========================================
    // BOOKING MANAGEMENT
    // ========================================

    public function getTempBooking(int $conversationId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM ai_temp_bookings WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [
                'event_type' => null,
                'event_date' => null,
                'event_location' => null,
                'budget' => null,
                'package_id' => null
            ];
        }

        return $result;
    }

    public function updateTempBooking(int $conversationId, array $data): bool
    {
        // Check if temp booking exists
        $checkStmt = $this->conn->prepare("SELECT temp_booking_id FROM ai_temp_bookings WHERE conversation_id = ?");
        $checkStmt->execute([$conversationId]);
        $exists = $checkStmt->fetch();

        if (!$exists) {
            // Get worker_id from conversation
            $convStmt = $this->conn->prepare("SELECT worker_id FROM conversations WHERE conversation_id = ?");
            $convStmt->execute([$conversationId]);
            $conv = $convStmt->fetch();

            if ($conv) {
                $insertStmt = $this->conn->prepare(
                    "INSERT INTO ai_temp_bookings (conversation_id, worker_id) VALUES (?, ?)"
                );
                $insertStmt->execute([$conversationId, $conv['worker_id']]);
            }
        }

        // Update with provided data
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $conversationId;

        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?"
        );
        return $stmt->execute($params);
    }

    public function getSuggestedPackages(int $workerId, ?float $budget): array
    {
        $sql = "SELECT * FROM packages WHERE worker_id = ? AND status = 'active'";
        $params = [$workerId];

        if ($budget) {
            $sql .= " AND price <= ?";
            $params[] = $budget + 1000; // Show packages slightly over budget
        }

        $sql .= " ORDER BY price ASC LIMIT 3";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========================================
    // PROPOSAL ACTIONS (Client Side)
    // ========================================

    public function acceptProposal(int $conversationId): bool
    {
        try {
            $this->conn->beginTransaction();

            $tempBooking = $this->getTempBooking($conversationId);

            // If photographer proposed a price, set it as final
            if (!empty($tempBooking['worker_proposed_price'])) {
                $stmt = $this->conn->prepare(
                    "UPDATE ai_temp_bookings 
                     SET final_price = worker_proposed_price,
                         budget = worker_proposed_price
                     WHERE conversation_id = ?"
                );
                $stmt->execute([$conversationId]);
            }

            // If photographer proposed a date, update event date
            if (!empty($tempBooking['worker_proposed_date'])) {
                $fields = ['event_date = worker_proposed_date'];

                $stmt = $this->conn->prepare(
                    "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?"
                );
                $stmt->execute([$conversationId]);
            }

            // Update status to confirmed
            $stmt = $this->conn->prepare(
                "UPDATE conversations SET booking_status = 'confirmed' WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error accepting proposal: " . $e->getMessage());
            return false;
        }
    }

    public function rejectProposal(int $conversationId, ?string $reason = null): bool
    {
        try {
            // Reset proposed values
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
                 SET worker_proposed_price = NULL, 
                     worker_proposed_date = NULL,
                     worker_notes = ? 
                 WHERE conversation_id = ?"
            );
            $stmt->execute([$reason, $conversationId]);

            // Set status back to pending
            $stmt = $this->conn->prepare(
                "UPDATE conversations SET booking_status = 'pending_worker' WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            return true;
        } catch (Exception $e) {
            error_log("Error rejecting proposal: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // WORKER BOOKING MANAGEMENT
    // ========================================



    public function getUserBookings(int $userId, ?string $status = null): array
    {
        $sql = "
            SELECT 
                c.conversation_id,
                c.booking_status,
                c.type,
                c.created_at,
                atb.*,
                p.name as package_name,
                p.price as package_price,
                p.duration_hours as duration,
                w.worker_id,
                CONCAT(w.firstName, ' ', COALESCE(w.middleName, ''), ' ', w.lastName) as photographer_name,
                w.email as photographer_email,
                w.phoneNumber as photographer_phone,
                w.profile_photo as photographer_photo,
                w.specialty as photographer_specialty
            FROM conversations c
            INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
            INNER JOIN workers w ON c.worker_id = w.worker_id
            LEFT JOIN packages p ON atb.package_id = p.package_id
            WHERE c.user_id = ?
        ";

        $params = [$userId];

        if ($status) {
            $sql .= " AND c.booking_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function proposePrice(int $conversationId, float $proposedPrice, ?string $notes = null): bool
    {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
                 SET worker_proposed_price = ?, 
                     worker_notes = ?, 
                     final_price = ? 
                 WHERE conversation_id = ?"
            );
            $stmt->execute([$proposedPrice, $notes, $proposedPrice, $conversationId]);

            $stmt = $this->conn->prepare(
                "UPDATE conversations SET booking_status = 'negotiating' WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error proposing price: " . $e->getMessage());
            return false;
        }
    }



    public function getWorkerBookingStats(int $workerId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_bookings,
                        COUNT(CASE WHEN c.booking_status = 'confirmed' THEN 1 END) as confirmed_bookings,
                        COUNT(CASE WHEN c.booking_status = 'completed' THEN 1 END) as completed_bookings,
                        COUNT(CASE WHEN c.booking_status = 'cancelled' THEN 1 END) as cancelled_bookings,
                        COUNT(CASE WHEN c.booking_status = 'pending_worker' THEN 1 END) as pending_bookings,
                        SUM(CASE WHEN c.booking_status = 'completed' THEN COALESCE(atb.final_price, 0) ELSE 0 END) as total_revenue,
                        AVG(CASE WHEN c.booking_status = 'completed' THEN COALESCE(atb.final_price, 0) ELSE NULL END) as avg_booking_value
                    FROM conversations c
                    LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                    WHERE c.worker_id = ?";
            
            $params = [$workerId];
            
            if ($startDate) {
                $sql .= " AND c.created_at >= ?";
                $params[] = $startDate;
            }
            
            if ($endDate) {
                $sql .= " AND c.created_at <= ?";
                $params[] = $endDate;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: [
                'total_bookings' => 0,
                'confirmed_bookings' => 0,
                'completed_bookings' => 0,
                'cancelled_bookings' => 0,
                'pending_bookings' => 0,
                'total_revenue' => 0,
                'avg_booking_value' => 0
            ];
        } catch (Exception $e) {
            error_log("Error fetching worker booking stats: " . $e->getMessage());
            return [
                'total_bookings' => 0,
                'confirmed_bookings' => 0,
                'completed_bookings' => 0,
                'cancelled_bookings' => 0,
                'pending_bookings' => 0,
                'total_revenue' => 0,
                'avg_booking_value' => 0
            ];
        }
    }

    public function getWorkerBookings(int $workerId, ?string $status = null, int $limit = 0): array
    {
        try {
            $sql = "SELECT c.conversation_id, c.booking_status, c.type, c.created_at, c.updated_at,
                           u.user_id, u.firstName as user_first, u.lastName as user_last, 
                           u.email as customer_email, u.phoneNumber as customer_phone,
                           u.profilePhotoUrl as customer_photo,
                           CONCAT(u.firstName, ' ', u.lastName) as customer_name,
                           atb.event_type, atb.event_date, atb.event_time, atb.event_location,
                           atb.budget, atb.final_price, atb.deposit_paid, atb.deposit_amount,
                           p.name as package_name, p.price as package_price, p.duration_hours as duration
                    FROM conversations c
                    LEFT JOIN user u ON c.user_id = u.user_id
                    LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                    LEFT JOIN packages p ON atb.package_id = p.package_id
                    WHERE c.worker_id = ?";
            
            $params = [$workerId];
            
            if ($status) {
                $sql .= " AND c.booking_status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching worker bookings: " . $e->getMessage());
            return [];
        }
    }

    public function requestMoreInfo(int $conversationId, string $message): bool
    {
        try {
            // Update status to requires_info
            $stmt = $this->conn->prepare(
                "UPDATE conversations SET booking_status = 'requires_info' WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);

            // Save the message
            $this->saveMessage($conversationId, 0, 'worker', $message);

            return true;
        } catch (Exception $e) {
            error_log("Error requesting more info: " . $e->getMessage());
            return false;
        }
    }

    public function updateBookingDetails(int $conversationId, array $updates): bool
    {
        try {
            if (empty($updates)) {
                return false;
            }

            // Build the SET clause dynamically
            $fields = [];
            $params = [];
            
            $allowedFields = [
                'event_date', 'event_time', 'event_location', 'final_price', 
                'worker_notes', 'package_id', 'event_type', 'budget'
            ];
            
            foreach ($updates as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $fields[] = "$field = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $params[] = $conversationId;
            
            $sql = "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error updating booking details: " . $e->getMessage());
            return false;
        }
    }

    public function setDepositAmount(int $conversationId, float $depositAmount): bool
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings SET deposit_amount = ? WHERE conversation_id = ?"
            );
            return $stmt->execute([$depositAmount, $conversationId]);
        } catch (Exception $e) {
            error_log("Error setting deposit amount: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // AVAILABILITY MANAGEMENT
    // ========================================

    public function getAvailabilityRange(int $workerId, string $startDate, string $endDate): array
    {
        try {
            $sql = "SELECT date, is_available, start_time, end_time, max_bookings, notes
                    FROM worker_availability 
                    WHERE worker_id = ? AND date BETWEEN ? AND ?
                    ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$workerId, $startDate, $endDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching availability range: " . $e->getMessage());
            return [];
        }
    }

    public function setAvailability(int $workerId, string $date, bool $isAvailable, ?string $startTime = null, ?string $endTime = null, int $maxBookings = 1): bool
    {
        try {
            $sql = "INSERT INTO worker_availability (worker_id, date, is_available, start_time, end_time, max_bookings) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    is_available = VALUES(is_available),
                    start_time = VALUES(start_time),
                    end_time = VALUES(end_time),
                    max_bookings = VALUES(max_bookings)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$workerId, $date, $isAvailable ? 1 : 0, $startTime, $endTime, $maxBookings]);
        } catch (Exception $e) {
            error_log("Error setting availability: " . $e->getMessage());
            return false;
        }
    }

    public function blockDates(int $workerId, array $dates, ?string $reason = null): bool
    {
        try {
            $this->conn->beginTransaction();
            
            $sql = "INSERT INTO worker_availability (worker_id, date, is_available, notes) 
                    VALUES (?, ?, 0, ?)
                    ON DUPLICATE KEY UPDATE 
                    is_available = 0,
                    notes = VALUES(notes)";
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($dates as $date) {
                $stmt->execute([$workerId, $date, $reason]);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error blocking dates: " . $e->getMessage());
            return false;
        }
    }
}