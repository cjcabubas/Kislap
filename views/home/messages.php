<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for active user or worker session
$user = $_SESSION['user'] ?? null;
$worker = $_SESSION['worker'] ?? null;

// If no user and no worker is logged in, redirect to the login page
if (!$user && !$worker) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

// Determine the current user type
$userType = $user ? 'user' : 'worker';

// Initialize variables passed from the controller, using null coalescing for safety
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

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="messages-container">
    <div class="messages-wrapper">
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
                        <p>Start a conversation to see it here!</p>
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
                        <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $unreadCount > 0 ? 'unread' : ''; ?>"
                             onclick="loadConversation(<?php echo $convId; ?>)">
                            <div class="conversation-header">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($otherUserName, 0, 2)); ?>
                                </div>
                                <div class="conversation-info">
                                    <div class="conversation-name"><?php echo htmlspecialchars($otherUserName); ?></div>
                                    <div class="conversation-preview"><?php echo htmlspecialchars($lastMessage); ?></div>
                                </div>
                                <div class="conversation-meta">
                                    <span class="conversation-time"><?php echo htmlspecialchars($lastMessageTime); ?></span>
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

        <div class="chat-area">
            <?php if ($activeConversation && $recipientInfo): ?>
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
                <?php
                if (isset($recipientInfo['conversation_type']) && $recipientInfo['conversation_type'] === 'ai') {
                    echo '<i class="fas fa-robot"></i> AI Assistant';
                } else {
                    echo ($recipientInfo['is_online'] ?? false) ? 'Online' : 'Offline';
                }
                ?>
            </span>
                            <?php if (isset($recipientInfo['booking_status']) && $recipientInfo['booking_status'] !== 'pending_ai'): ?>
                                <span class="booking-status-badge">
                    <?php
                    $statusLabels = [
                            'pending_details' => 'Collecting Details',
                            'pending_confirmation' => 'Awaiting Confirmation',
                            'pending_worker' => 'Waiting for Photographer',
                            'confirmed' => 'Booking Confirmed',
                            'completed' => 'Completed',
                            'cancelled' => 'Cancelled'
                    ];
                    echo $statusLabels[$recipientInfo['booking_status']] ?? $recipientInfo['booking_status'];
                    ?>
                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <?php if (isset($recipientInfo['conversation_type']) && $recipientInfo['conversation_type'] === 'ai'): ?>
                            <button class="chat-action-btn" title="Talk to Human" onclick="requestHumanAgent()">
                                <i class="fas fa-user"></i>
                            </button>
                        <?php endif; ?>
                        <button class="chat-action-btn" title="More Options"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>


                <div class="messages-area" id="messagesArea">
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php
                            $senderId = $msg['sender_id'] ?? 0;
                            $messageText = $msg['message_text'] ?? '';
                            $attachmentPath = $msg['attachment_path'] ?? '';
                            $sentAt = $msg['sent_at'] ?? '';
                            $currentUserId = $user['user_id'] ?? ($worker['worker_id'] ?? 0); // Handle both user and worker
                            $isSent = $senderId == $currentUserId;

                            if ($isSent) {
                                $senderName = ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '');
                                if (trim($senderName) === '') {
                                    $senderName = $worker['firstName'] ?? 'You'; // Fallback for worker
                                }
                            } else {
                                $senderName = $recipientName;
                            }
                            ?>
                            <div class="message-group <?php echo $isSent ? 'sent' : 'received'; ?>">
                                <div class="message-avatar">
                                    <?php echo strtoupper(substr($senderName, 0, 2)); ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($messageText)); ?>
                                    </div>
                                    <?php if ($attachmentPath): ?>
                                        <div class="message-attachment">
                                            <img src="<?php echo htmlspecialchars($attachmentPath); ?>"
                                                 alt="Attachment">
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

                <div class="message-input-area">
                    <form id="messageForm" onsubmit="sendMessage(event)">
                        <div class="input-wrapper">
                            <button type="button" class="attachment-btn"
                                    onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-paperclip"></i>
                            </button>
                            <input type="file" id="fileInput" accept="image/*" style="display: none;"
                                   onchange="handleFileSelect(event)">
                            <div class="message-input">
                                <textarea id="messageText" name="message" placeholder="Type a message..." rows="1"
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
                    <p>Choose a conversation from the sidebar to start messaging.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ===================================
        //  Element References & Initial State
        // ===================================
        const messageForm = document.getElementById('messageForm');
        const messageText = document.getElementById('messageText');
        const messagesArea = document.getElementById('messagesArea');
        const sendBtn = document.getElementById('sendBtn');
        const fileInput = document.getElementById('fileInput');
        const searchInput = document.getElementById('searchConversations');

        // --- Get essential data from PHP ---
        const activeConversationId = <?php echo $activeConversation['conversation_id'] ?? 'null'; ?>;
        const currentUserId = <?php echo($user['user_id'] ?? $worker['worker_id'] ?? 'null'); ?>;
        const currentUserType = '<?php echo $userType; ?>';
        const recipientName = '<?php echo addslashes($recipientInfo['name'] ?? 'Recipient'); ?>';

        console.log('Chat initialized:', {
            conversationId: activeConversationId,
            userId: currentUserId,
            userType: currentUserType
        });

        // --- Initial UI setup ---
        if (messagesArea) {
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        // Auto-resize textarea
        if (messageText) {
            messageText.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = `${Math.min(this.scrollHeight, 120)}px`;
            });

            // Enter key to send
            messageText.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (messageForm) {
                        messageForm.dispatchEvent(new Event('submit'));
                    }
                }
            });
        }

        // ===================================
        //  Send Message Form Submission
        // ===================================
        if (messageForm) {
            messageForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const message = messageText.value.trim();
                const file = fileInput.files[0];

                if (!message && !file) {
                    console.log('Empty message, not sending');
                    return;
                }

                if (!activeConversationId) {
                    alert('No active conversation. Please refresh the page.');
                    return;
                }

                console.log('Sending message:', message);
                sendBtn.disabled = true;
                sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                // Use FormData to handle files and text
                const formData = new FormData();
                formData.append('conversation_id', activeConversationId);
                formData.append('message', message);
                if (file) {
                    formData.append('attachment', file);
                }

                try {
                    // Make sure URL is correct
                    const url = '?controller=Chat&action=sendMessage';
                    console.log('Posting to:', url);

                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin' // Include session cookies
                    });

                    console.log('Response status:', response.status);

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server error:', errorText);
                        throw new Error(`Server responded with ${response.status}: ${errorText}`);
                    }

                    const result = await response.json();
                    console.log('Server response:', result);

                    if (result.success) {
                        // Display user's message
                        if (result.message) {
                            displayMessage(result.message);
                        }

                        // Display bot's responses (if any)
                        if (result.botMessages && result.botMessages.length > 0) {
                            result.botMessages.forEach((botMsg, index) => {
                                setTimeout(() => {
                                    displayMessage(botMsg);
                                }, index * 500); // Stagger bot responses
                            });
                        }

                        // Clear input
                        messageText.value = '';
                        fileInput.value = '';
                        messageText.style.height = 'auto';
                    } else {
                        console.error('Failed to send message:', result.error);
                        alert('Error: ' + (result.error || 'Could not send message'));
                    }

                } catch (error) {
                    console.error('Network error:', error);
                    alert('A network error occurred. Please check the console and try again.\n\nError: ' + error.message);
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                }
            });
        }

        // ===================================
        //  Conversation Search
        // ===================================
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const conversations = document.querySelectorAll('.conversation-item');

                conversations.forEach(conv => {
                    const name = conv.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
                    conv.style.display = name.includes(searchTerm) ? '' : 'none';
                });
            });
        }

        // ===================================
        //  Display Message Function
        // ===================================
        function displayMessage(msg) {
            if (!messagesArea) return;

            const isSent = msg.sender_type === currentUserType;
            const isBot = msg.sender_type === 'bot';

            let senderName;
            if (isBot) {
                senderName = 'AI';
            } else if (isSent) {
                senderName = 'You';
            } else {
                senderName = recipientName;
            }

            const messageGroup = document.createElement('div');
            messageGroup.className = `message-group ${isSent ? 'sent' : 'received'}`;

            // Format message text (preserve line breaks, make bold text work)
            let formattedText = msg.message_text
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
                .replace(/\*(.*?)\*/g, '<em>$1</em>'); // Italic

            messageGroup.innerHTML = `
            <div class="message-avatar">
                ${senderName.substring(0, 2).toUpperCase()}
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    ${formattedText}
                </div>
                ${msg.attachment_path ? `
                    <div class="message-attachment">
                        <img src="${msg.attachment_path}" alt="Attachment">
                    </div>
                ` : ''}
                <span class="message-time">${formatTime(msg.sent_at)}</span>
            </div>
        `;

            messagesArea.appendChild(messageGroup);
            messagesArea.scrollTop = messagesArea.scrollHeight;
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        }

        // ===================================
        //  Load Conversation Function
        // ===================================
        window.loadConversation = function (conversationId) {
            window.location.href = `?controller=Chat&action=view&conversation_id=${conversationId}`;
        };

        // ===================================
        //  File Handling
        // ===================================
        window.handleFileSelect = function () {
            if (fileInput) {
                fileInput.click();
            }
        };

        // ===================================
        //  Request Human Agent
        // ===================================
        window.requestHumanAgent = function () {
            if (confirm('Would you like to be connected to the photographer directly?')) {
                messageText.value = 'I would like to talk to a human agent';
                messageForm.dispatchEvent(new Event('submit'));
            }
        };

        // ===================================
        //  Polling for New Messages
        // ===================================
        function startPollingForMessages() {
            if (!activeConversationId) return;

            let lastMessageId = <?php echo !empty($messages) ? end($messages)['message_id'] : 0; ?>;

            setInterval(async () => {
                try {
                    const response = await fetch(
                        `?controller=Chat&action=fetchNewMessages&conversation_id=${activeConversationId}&last_message_id=${lastMessageId}`,
                        {credentials: 'same-origin'}
                    );

                    if (!response.ok) return;

                    const newMessages = await response.json();

                    if (Array.isArray(newMessages) && newMessages.length > 0) {
                        newMessages.forEach(msg => {
                            displayMessage(msg);
                            lastMessageId = Math.max(lastMessageId, msg.message_id);
                        });
                    }
                } catch (error) {
                    console.error("Error polling for messages:", error);
                }
            }, 5000); // Poll every 5 seconds
        }

        // Start polling
        startPollingForMessages();
    });


</script>
</body>
</html>

</script>
</body>
</html>