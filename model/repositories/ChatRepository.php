<?php

class ChatRepository
{
    public PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function findAiConversation(int $userId, int $workerId): ?array
    {
        // Find ANY existing conversation between this user and worker
        // This prevents creating duplicate conversations when clicking "Book Now" multiple times
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

    public function createAiConversation(int $userId, int $workerId): int
    {
        try {
            $this->conn->beginTransaction();

            // 1. Create conversation
            $stmt = $this->conn->prepare(
                "INSERT INTO conversations (user_id, worker_id, type, booking_status) 
                 VALUES (?, ?, 'ai', 'pending_ai')"
            );
            $stmt->execute([$userId, $workerId]);
            $conversationId = (int)$this->conn->lastInsertId();

            // 2. Create the temporary booking data holder
            $stmt = $this->conn->prepare(
                "INSERT INTO ai_temp_bookings (conversation_id, worker_id) VALUES (?, ?)"
            );
            $stmt->execute([$conversationId, $workerId]);

            // 3. Create the first welcome message from the bot
            $welcomeMessage = "Hi! I'm Kislap's AI assistant. 📸 I can help you start the booking process.
            \nTo start, what type of event are you planning? (e.g., Wedding, Birthday, Portrait)";

            $this->saveMessage($conversationId, 0, 'bot', $welcomeMessage);

            $this->conn->commit();
            return $conversationId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            // Log error $e->getMessage()
            return 0;
        }
    }

    // Save any message to the DB
    public function saveMessage(int $conversationId, int $senderId, string $senderType, string $messageText): ?array
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO messages (conversation_id, sender_id, sender_type, message_text) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$conversationId, $senderId, $senderType, $messageText]);
        $messageId = $this->conn->lastInsertId();

        // Return the full message object
        return $this->getMessageById($messageId);
    }

    public function getMessageById(int $messageId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM messages WHERE message_id = ?");
        $stmt->execute([$messageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Get all conversations for the sidebar
    public function getConversationsForUser(int $userId, string $userType): array
    {
        $idColumn = $userType === 'user' ? 'c.user_id' : 'c.worker_id';
        $otherUserJoin = $userType === 'user'
            ? "JOIN workers w ON c.worker_id = w.worker_id"
            : "JOIN user u ON c.user_id = u.user_id";

        $otherUserName = $userType === 'user'
            ? "CONCAT(w.firstName, ' ', w.lastName)"
            : "CONCAT(u.firstName, ' ', u.lastName)";

        $sql = "
            SELECT 
                c.conversation_id,
                c.type,
                $otherUserName AS other_user_name,
                lm.message_text AS last_message,
                lm.sent_at AS last_message_time,
                (SELECT COUNT(*) FROM messages m 
                 WHERE m.conversation_id = c.conversation_id AND m.is_read = 0 AND m.sender_type != ?) AS unread_count
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a single conversation's details
    public function getConversationById(int $conversationId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM conversations WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    // Get all messages for the active chat window
    public function getMessagesForConversation(int $conversationId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM messages WHERE conversation_id = ? ORDER BY sent_at ASC"
        );
        $stmt->execute([$conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get info about the other person in the chat
    public function getRecipientInfo(int $id, string $userType): ?array
    {
        $table = $userType === 'user' ? 'workers' : 'user';
        $idColumn = $userType === 'user' ? 'worker_id' : 'user_id';

        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE $idColumn = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) return null;

        // Normalize the data for the view
        return [
            'name' => ($result['firstName'] ?? '') . ' ' . ($result['lastName'] ?? ''),
            'profile_picture' => $result['profile_photo'] ?? $result['profilePhotoUrl'] ?? null,
            'is_online' => false // Placeholder for future online status logic
        ];
    }

    public function getTempBooking(int $conversationId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM ai_temp_bookings WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return empty array if no booking exists, not null
        if (!$result) {
            return [
                'event_type' => null,
                'event_date' => null,
                'event_location' => null,
                'budget' => null,
                'package_id' => null,
                'available_packages' => null
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
            // Insert new temp booking if it doesn't exist
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
        
        // Now update with the provided data
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

    public function updateConversationStatus(int $conversationId, string $status): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = ? WHERE conversation_id = ?"
        );
        return $stmt->execute([$status, $conversationId]);
    }

    public function updateConversationType(int $conversationId, string $type): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET type = ? WHERE conversation_id = ?"
        );
        return $stmt->execute([$type, $conversationId]);
    }

    public function getSuggestedPackages(int $workerId, ?float $budget): array
    {
        $sql = "SELECT * FROM packages WHERE worker_id = ? AND status = 'active'";
        $params = [$workerId];

        if ($budget) {
            // Show packages around the budget
            $sql .= " AND price <= ?";
            $params[] = $budget + 500; // e.g., show packages slightly over budget
        }

        $sql .= " ORDER BY price ASC LIMIT 3";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
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
    // WORKER BOOKING MANAGEMENT
    // ========================================
    
    public function getWorkerBookings(int $workerId, ?string $status = null): array
    {
    $sql = "
        SELECT 
            c.conversation_id,
            c.booking_status,
            c.type,
            c.created_at,
            atb.temp_booking_id,
            atb.event_type,
            atb.event_date,
            atb.event_location,
            atb.budget,
            atb.package_id,
            p.name as package_name,
            p.price as package_price,
            u.user_id,
            CONCAT(u.firstName, ' ', u.lastName) as customer_name,
            u.email as customer_email,
            u.phoneNumber as customer_phone,
            u.profilePhotoUrl as customer_photo
        FROM conversations c
        INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
        INNER JOIN user u ON c.user_id = u.user_id
        LEFT JOIN packages p ON atb.package_id = p.package_id
        WHERE c.worker_id = ?
    ";
    
    $params = [$workerId];
    
    if ($status) {
        $sql .= " AND c.booking_status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY c.created_at DESC";
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function acceptBooking(int $conversationId): bool
{
    $stmt = $this->conn->prepare(
        "UPDATE conversations SET booking_status = 'confirmed' WHERE conversation_id = ?"
    );
    return $stmt->execute([$conversationId]);
}

public function rejectBooking(int $conversationId, ?string $reason = null): bool
{
    try {
        $this->conn->beginTransaction();
        
        // Update conversation status
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = 'cancelled' WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        
        // Log cancellation reason if provided
        if ($reason) {
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings SET cancellation_reason = ?, cancelled_by = 'worker' WHERE conversation_id = ?"
            );
            $stmt->execute([$reason, $conversationId]);
        }
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        return false;
    }
}

// ========================================
// PHOTOGRAPHER BOOKING CONTROLS
// ========================================

/**
 * Propose alternative pricing to client
 */
public function proposePrice(int $conversationId, float $proposedPrice, ?string $notes = null): bool
{
    try {
        $this->conn->beginTransaction();
        
        // Update temp booking with proposed price
        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings SET worker_proposed_price = ?, worker_notes = ?, final_price = ? WHERE conversation_id = ?"
        );
        $stmt->execute([$proposedPrice, $notes, $proposedPrice, $conversationId]);
        
        // Update conversation status
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = 'negotiating' WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        
        // Log modification
        $this->logModification($conversationId, 'worker', 'price_change', null, $proposedPrice, $notes);
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("Error proposing price: " . $e->getMessage());
        return false;
    }
}

/**
 * Propose alternative date/time to client
 */
public function proposeDateTime(int $conversationId, string $proposedDate, ?string $proposedTime = null, ?string $reason = null): bool
{
    try {
        $this->conn->beginTransaction();
        
        $fields = ['worker_proposed_date = ?'];
        $params = [$proposedDate];
        
        if ($proposedTime) {
            $fields[] = 'worker_proposed_time = ?';
            $params[] = $proposedTime;
        }
        
        if ($reason) {
            $fields[] = 'worker_notes = ?';
            $params[] = $reason;
        }
        
        $params[] = $conversationId;
        
        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?"
        );
        $stmt->execute($params);
        
        // Update conversation status
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = 'negotiating' WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        
        // Log modification
        $this->logModification($conversationId, 'worker', 'date_change', null, $proposedDate . ' ' . ($proposedTime ?? ''), $reason);
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("Error proposing date/time: " . $e->getMessage());
        return false;
    }
}

/**
 * Request more information from client
 */
public function requestMoreInfo(int $conversationId, string $message): bool
{
    try {
        // Update conversation status
        $stmt = $this->conn->prepare(
            "UPDATE conversations SET booking_status = 'requires_info' WHERE conversation_id = ?"
        );
        $stmt->execute([$conversationId]);
        
        // Save message requesting info
        $conversation = $this->getConversationById($conversationId);
        if ($conversation) {
            $this->saveMessage($conversationId, $conversation['worker_id'], 'worker', $message);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error requesting info: " . $e->getMessage());
        return false;
    }
}

/**
 * Update booking details (worker modifications)
 */
public function updateBookingDetails(int $conversationId, array $updates): bool
{
    try {
        $this->conn->beginTransaction();
        
        $allowedFields = ['event_date', 'event_time', 'event_location', 'final_price', 'worker_notes', 'package_id'];
        $fields = [];
        $params = [];
        
        foreach ($updates as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $conversationId;
        
        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?"
        );
        $stmt->execute($params);
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("Error updating booking: " . $e->getMessage());
        return false;
    }
}

/**
 * Set deposit requirements
 */
public function setDepositAmount(int $conversationId, float $depositAmount): bool
{
    try {
        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings SET deposit_amount = ? WHERE conversation_id = ?"
        );
        return $stmt->execute([$depositAmount, $conversationId]);
    } catch (Exception $e) {
        error_log("Error setting deposit: " . $e->getMessage());
        return false;
    }
}

/**
 * Log booking modification
 */
private function logModification(int $conversationId, string $modifiedBy, string $modificationType, ?string $oldValue, ?string $newValue, ?string $reason): bool
{
    try {
        $stmt = $this->conn->prepare(
            "INSERT INTO booking_modifications (conversation_id, modified_by, modification_type, old_value, new_value, reason) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$conversationId, $modifiedBy, $modificationType, $oldValue, $newValue, $reason]);
    } catch (Exception $e) {
        error_log("Error logging modification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get booking modification history
 */
public function getBookingModifications(int $conversationId): array
{
    $stmt = $this->conn->prepare(
        "SELECT * FROM booking_modifications WHERE conversation_id = ? ORDER BY created_at DESC"
    );
    $stmt->execute([$conversationId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ========================================
// WORKER AVAILABILITY MANAGEMENT
// ========================================

/**
 * Set worker availability for a specific date
 */
public function setAvailability(int $workerId, string $date, bool $isAvailable, ?string $startTime = null, ?string $endTime = null, ?int $maxBookings = 1): bool
{
    try {
        $stmt = $this->conn->prepare(
            "INSERT INTO worker_availability (worker_id, date, is_available, start_time, end_time, max_bookings) 
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE is_available = ?, start_time = ?, end_time = ?, max_bookings = ?"
        );
        return $stmt->execute([
            $workerId, $date, $isAvailable, $startTime, $endTime, $maxBookings,
            $isAvailable, $startTime, $endTime, $maxBookings
        ]);
    } catch (Exception $e) {
        error_log("Error setting availability: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if worker is available on a date
 */
public function checkAvailability(int $workerId, string $date): ?array
{
    $stmt = $this->conn->prepare(
        "SELECT * FROM worker_availability WHERE worker_id = ? AND date = ?"
    );
    $stmt->execute([$workerId, $date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

/**
 * Get worker availability for date range
 */
public function getAvailabilityRange(int $workerId, string $startDate, string $endDate): array
{
    $stmt = $this->conn->prepare(
        "SELECT * FROM worker_availability WHERE worker_id = ? AND date BETWEEN ? AND ? ORDER BY date ASC"
    );
    $stmt->execute([$workerId, $startDate, $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Block multiple dates (vacation, unavailable periods)
 */
public function blockDates(int $workerId, array $dates, ?string $reason = null): bool
{
    try {
        $this->conn->beginTransaction();
        
        foreach ($dates as $date) {
            $this->setAvailability($workerId, $date, false, null, null, 0);
            
            if ($reason) {
                $stmt = $this->conn->prepare(
                    "UPDATE worker_availability SET notes = ? WHERE worker_id = ? AND date = ?"
                );
                $stmt->execute([$reason, $workerId, $date]);
            }
        }
        
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        error_log("Error blocking dates: " . $e->getMessage());
        return false;
    }
}

/**
 * Get worker settings
 */
public function getWorkerSettings(int $workerId): ?array
{
    $stmt = $this->conn->prepare(
        "SELECT * FROM worker_settings WHERE worker_id = ?"
    );
    $stmt->execute([$workerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

/**
 * Update worker settings
 */
public function updateWorkerSettings(int $workerId, array $settings): bool
{
    try {
        // Check if settings exist
        $existing = $this->getWorkerSettings($workerId);
        
        if (!$existing) {
            // Create default settings
            $stmt = $this->conn->prepare(
                "INSERT INTO worker_settings (worker_id) VALUES (?)"
            );
            $stmt->execute([$workerId]);
        }
        
        $allowedFields = [
            'auto_accept_bookings', 'require_deposit', 'deposit_percentage',
            'min_notice_days', 'max_advance_booking_days', 'cancellation_policy',
            'terms_and_conditions', 'working_hours_start', 'working_hours_end', 'working_days'
        ];
        
        $fields = [];
        $params = [];
        
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $workerId;
        
        $stmt = $this->conn->prepare(
            "UPDATE worker_settings SET " . implode(', ', $fields) . " WHERE worker_id = ?"
        );
        return $stmt->execute($params);
    } catch (Exception $e) {
        error_log("Error updating worker settings: " . $e->getMessage());
        return false;
    }
}

/**
 * Get advanced booking statistics for worker
 */
public function getWorkerBookingStats(int $workerId, ?string $startDate = null, ?string $endDate = null): array
{
    $sql = "
        SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN c.booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
            SUM(CASE WHEN c.booking_status = 'pending_worker' THEN 1 ELSE 0 END) as pending_bookings,
            SUM(CASE WHEN c.booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
            SUM(CASE WHEN c.booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
            SUM(CASE WHEN c.booking_status = 'confirmed' OR c.booking_status = 'completed' THEN atb.final_price ELSE 0 END) as total_revenue,
            SUM(CASE WHEN atb.deposit_paid = 1 THEN atb.deposit_amount ELSE 0 END) as total_deposits,
            AVG(atb.final_price) as average_booking_value
        FROM conversations c
        INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
        WHERE c.worker_id = ?
    ";
    
    $params = [$workerId];
    
    if ($startDate && $endDate) {
        $sql .= " AND atb.event_date BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Search and filter bookings with advanced criteria
 */
public function searchWorkerBookings(int $workerId, array $filters = []): array
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
            u.user_id,
            CONCAT(u.firstName, ' ', u.lastName) as customer_name,
            u.email as customer_email,
            u.phoneNumber as customer_phone,
            u.profilePhotoUrl as customer_photo
        FROM conversations c
        INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
        INNER JOIN user u ON c.user_id = u.user_id
        LEFT JOIN packages p ON atb.package_id = p.package_id
        WHERE c.worker_id = ?
    ";
    
    $params = [$workerId];
    
    // Apply filters
    if (!empty($filters['status'])) {
        $sql .= " AND c.booking_status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['event_type'])) {
        $sql .= " AND atb.event_type = ?";
        $params[] = $filters['event_type'];
    }
    
    if (!empty($filters['date_from'])) {
        $sql .= " AND atb.event_date >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $sql .= " AND atb.event_date <= ?";
        $params[] = $filters['date_to'];
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND atb.final_price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND atb.final_price <= ?";
        $params[] = $filters['max_price'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (CONCAT(u.firstName, ' ', u.lastName) LIKE ? OR u.email LIKE ? OR atb.event_location LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Sorting
    $orderBy = $filters['order_by'] ?? 'c.created_at';
    $orderDir = $filters['order_dir'] ?? 'DESC';
    $sql .= " ORDER BY $orderBy $orderDir";
    
    // Pagination
    if (isset($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = (int)$filters['limit'];
        
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
    }
    
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Client accepts photographer's proposal
 */
public function acceptProposal(int $conversationId): bool
{
    try {
        $this->conn->beginTransaction();
        
        // Get the temp booking to see what was proposed
        $tempBooking = $this->getTempBooking($conversationId);
        
        // If photographer proposed a price, set it as final AND update budget
        if (!empty($tempBooking['worker_proposed_price'])) {
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings 
                 SET final_price = worker_proposed_price,
                     budget = worker_proposed_price
                 WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);
        }
        
        // If photographer proposed a date, update the event date
        if (!empty($tempBooking['worker_proposed_date'])) {
            $fields = ['event_date = worker_proposed_date'];
            if (!empty($tempBooking['worker_proposed_time'])) {
                $fields[] = 'event_time = worker_proposed_time';
            }
            $stmt = $this->conn->prepare(
                "UPDATE ai_temp_bookings SET " . implode(', ', $fields) . " WHERE conversation_id = ?"
            );
            $stmt->execute([$conversationId]);
        }
        
        // Update conversation status to confirmed
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

/**
 * Client rejects photographer's proposal
 */
public function rejectProposal(int $conversationId, ?string $reason = null): bool
{
    try {
        // Reset proposed values
        $stmt = $this->conn->prepare(
            "UPDATE ai_temp_bookings 
             SET worker_proposed_price = NULL, 
                 worker_proposed_date = NULL, 
                 worker_proposed_time = NULL,
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

}