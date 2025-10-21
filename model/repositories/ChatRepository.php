<?php

class ChatRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function findAiConversation(int $userId, int $workerId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM conversations 
             WHERE user_id = ? AND worker_id = ? AND type = 'ai'"
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
                WHERE (conversation_id, sent_at) IN (
                    SELECT conversation_id, MAX(sent_at)
                    FROM messages
                    GROUP BY conversation_id
                )
            ) AS lm ON c.conversation_id = lm.conversation_id
            WHERE $idColumn = ?
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
            'is_online' => false // Placeholder for future online status logic
        ];
    }

    public function getTempBooking(int $conversationId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM ai_temp_bookings WHERE conversation_id = ?");
        $stmt->execute([$conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateTempBooking(int $conversationId, array $data): bool
    {
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

}