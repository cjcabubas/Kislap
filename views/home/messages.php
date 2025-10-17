<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

$conversations = $conversations ?? [];
$activeConversation = $activeConversation ?? null;
$messages = $messages ?? [];
$recipientInfo = $recipientInfo ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/messages.css" type="text/css">
</head>
<body>

<?php  require __DIR__ . '/../shared/navbar.php'; ?>

<div class="messages-container">
    <div class="messages-wrapper">
        <!-- Conversations Sidebar -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-comments"></i> Messages</h2>
                <div class="search-box">
                    <input type="text" id="searchConversations" placeholder="Search conversations...">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <div class="conversations-list" id="conversationsList">
                <?php if (empty($conversations)): ?>
                    <div class="empty-state-sidebar">
                        <i class="fas fa-inbox"></i>
                        <h3>No conversations yet</h3>
                        <p>Start a conversation with a photographer!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <?php
                        $convId = $conv['conversation_id'] ?? 0;
                        $otherUserName = $conv['other_user_name'] ?? 'Unknown User';
                        $lastMessage = $conv['last_message'] ?? 'No messages yet';
                        $lastMessageTime = $conv['last_message_time'] ?? '';
                        $unreadCount = $conv['unread_count'] ?? 0;
                        $isActive = isset($activeConversation['conversation_id']) && $convId == $activeConversation['conversation_id'];
                        ?>
                        <div class="conversation-item <?php echo $unreadCount > 0 ? 'unread' : ''; ?> <?php echo $isActive ? 'active' : ''; ?>"
                             onclick="loadConversation(<?php echo $convId; ?>)">
                            <div class="conversation-header">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($otherUserName, 0, 2)); ?>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-name">
                                        <?php echo htmlspecialchars($otherUserName); ?>
                                    </div>
                                    <div class="conversation-preview">
                                        <?php echo htmlspecialchars($lastMessage); ?>
                                    </div>
                                </div>
                                <div class="conversation-meta">
                                        <span class="conversation-time">
                                            <?php echo htmlspecialchars($lastMessageTime); ?>
                                        </span>
                                    <?php if ($unreadCount > 0): ?>
                                        <span class="unread-badge"><?php echo $unreadCount; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($activeConversation && $recipientInfo): ?>
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-user-avatar">
                            <?php
                            $recipientName = $recipientInfo['name'] ?? 'User';
                            echo strtoupper(substr($recipientName, 0, 2));
                            ?>
                        </div>
                        <div class="chat-user-details">
                            <h3><?php echo htmlspecialchars($recipientName); ?></h3>
                            <span class="user-status <?php echo ($recipientInfo['is_online'] ?? false) ? 'online' : ''; ?>">
                                    <?php echo ($recipientInfo['is_online'] ?? false) ? 'Online' : 'Offline'; ?>
                                </span>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <button class="chat-action-btn" title="View Profile">
                            <i class="fas fa-user"></i>
                        </button>
                        <button class="chat-action-btn" title="More Options">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="messages-area" id="messagesArea">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php
                            $senderId = $msg['sender_id'] ?? 0;
                            $messageText = $msg['message_text'] ?? '';
                            $attachmentPath = $msg['attachment_path'] ?? '';
                            $sentAt = $msg['sent_at'] ?? '';
                            $currentUserId = $user['user_id'] ?? 0;
                            $isSent = $senderId == $currentUserId;

                            if ($isSent) {
                                $displayName = ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '');
                            } else {
                                $displayName = $recipientName;
                            }
                            ?>
                            <div class="message-group <?php echo $isSent ? 'sent' : 'received'; ?>">
                                <div class="message-avatar">
                                    <?php echo strtoupper(substr($displayName, 0, 2)); ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($messageText)); ?>
                                    </div>
                                    <?php if ($attachmentPath): ?>
                                        <div class="message-attachment">
                                            <img src="<?php echo htmlspecialchars($attachmentPath); ?>" alt="Attachment">
                                        </div>
                                    <?php endif; ?>
                                    <span class="message-time">
                                            <?php echo $sentAt ? date('h:i A', strtotime($sentAt)) : ''; ?>
                                        </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Message Input Area -->
                <div class="message-input-area">
                    <form id="messageForm" onsubmit="sendMessage(event)">
                        <div class="input-wrapper">
                            <button type="button" class="attachment-btn" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelect(event)">

                            <div class="message-input">
                                    <textarea id="messageText"
                                              name="message"
                                              placeholder="Type a message..."
                                              rows="1"
                                              required></textarea>
                            </div>

                            <button type="submit" class="send-btn" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>No conversation selected</h3>
                    <p>Choose a conversation from the sidebar to start messaging</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const textarea = document.getElementById('messageText');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
    }

    function loadConversation(conversationId) {
        window.location.href = `?controller=Messages&action=view&conversation_id=${conversationId}`;
    }


</script>
</body>
</html>