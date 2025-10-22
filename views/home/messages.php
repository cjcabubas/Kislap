<?php
// start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// get logged in user
$user = $_SESSION['user'] ?? null;
$worker = $_SESSION['worker'] ?? null;

// redirect if not logged in
if (!$user && !$worker) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

// check user type
$userType = $user ? 'user' : 'worker';
$senderType = $userType; // Save this before navbar overwrites it

// DEBUG
error_log("DEBUG messages.php: user=" . ($user ? 'SET' : 'NULL') . ", worker=" . ($worker ? 'SET' : 'NULL') . ", userType=$userType, senderType=$senderType");

// initialize data from controller
$conversations = $conversations ?? [];
$activeConversation = $activeConversation ?? null;
$messages = $messages ?? [];
$recipientInfo = $recipientInfo ?? null;
$tempBooking = $tempBooking ?? null;
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
    <link rel="stylesheet" href="/Kislap/public/css/package-cards.css" type="text/css">
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
                    <button class="mobile-back-btn" onclick="showMobileSidebar()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="chat-user-info">
                        <div class="chat-user-avatar">
                            <?php
                            $recipientName = $recipientInfo['name'] ?? 'User';
                            $recipientPicture = $recipientInfo['profile_picture'] ?? null;
                            if ($recipientPicture && file_exists($_SERVER['DOCUMENT_ROOT'] . $recipientPicture)):
                            ?>
                                <img src="<?php echo htmlspecialchars($recipientPicture); ?>" alt="Profile">
                            <?php else: ?>
                                <?php echo strtoupper(substr($recipientName, 0, 2)); ?>
                            <?php endif; ?>
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
                            'negotiating' => 'Negotiating',
                            'requires_info' => 'More Info Needed',
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
                            $senderType = $msg['sender_type'] ?? 'user';
                            $messageText = $msg['message_text'] ?? '';
                            $attachmentPath = $msg['attachment_path'] ?? '';
                            $sentAt = $msg['sent_at'] ?? '';
                            
                            // Determine if this message was sent by the current user
                            $currentUserId = $user['user_id'] ?? ($worker['worker_id'] ?? 0);
                            $currentUserType = $user ? 'user' : 'worker';
                            $isSent = ($senderId == $currentUserId && $senderType == $currentUserType);
                            
                            // Determine sender name and type label
                            // AI messages have sender_id = 0 or sender_type = 'bot'/'ai'
                            if ($senderId == 0 || $senderType === 'bot' || $senderType === 'ai') {
                                $senderName = 'AI Assistant';
                                $messageClass = 'bot-message';
                            } elseif ($isSent) {
                                $senderName = 'You';
                                $messageClass = 'sent';
                            } else {
                                $senderName = $recipientName ?? 'Other User';
                                $messageClass = 'received';
                            }
                            ?>
                            <div class="message-group <?php echo $messageClass; ?>"><?php if ($senderId == 0 || $senderType === 'bot' || $senderType === 'ai'): ?>
                                <div class="bot-badge"><i class="fas fa-robot"></i> AI</div>
                            <?php endif; ?>
                                <div class="message-avatar">
                                <?php
                                // get sender profile pic
                                $senderPicture = null;
                                if ($isSent) {
                                    $senderPicture = $user['profilePhotoUrl'] ?? $worker['profile_photo'] ?? null;
                                } else {
                                    $senderPicture = $recipientInfo['profile_picture'] ?? null;
                                }
                                
                                if ($senderPicture && file_exists($_SERVER['DOCUMENT_ROOT'] . $senderPicture)):
                                ?>
                                    <img src="<?php echo htmlspecialchars($senderPicture); ?>" alt="Avatar">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($senderName, 0, 2)); ?>
                                <?php endif; ?>
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
                    
                    <?php
                    // Show negotiation proposal banner AFTER messages (only for clients, not workers)
                    if ($user && 
                        isset($activeConversation['booking_status']) && 
                        $activeConversation['booking_status'] === 'negotiating' &&
                        isset($tempBooking) && is_array($tempBooking) &&
                        (!empty($tempBooking['worker_proposed_price']) || !empty($tempBooking['worker_proposed_date']))): 
                    ?>
                        <div class="negotiation-banner">
                            <div class="banner-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="banner-content">
                                <h4>Photographer's Proposal</h4>
                                <?php if (!empty($tempBooking['worker_proposed_price'])): ?>
                                    <p><strong>Proposed Price:</strong> ₱<?php echo number_format($tempBooking['worker_proposed_price'], 2); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($tempBooking['worker_proposed_date'])): ?>
                                    <p><strong>Proposed Date:</strong> <?php echo date('F d, Y', strtotime($tempBooking['worker_proposed_date'])); ?>
                                    <?php if (!empty($tempBooking['worker_proposed_time'])): ?>
                                        at <?php echo date('h:i A', strtotime($tempBooking['worker_proposed_time'])); ?>
                                    <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($tempBooking['worker_notes'])): ?>
                                    <p class="proposal-notes"><em><?php echo htmlspecialchars($tempBooking['worker_notes']); ?></em></p>
                                <?php endif; ?>
                                <div class="banner-actions">
                                    <button class="btn-accept-proposal" onclick="acceptProposal(<?php echo $activeConversation['conversation_id']; ?>)">
                                        <i class="fas fa-check"></i> Accept Proposal
                                    </button>
                                    <button class="btn-reject-proposal" onclick="showRejectProposalModal()">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="message-input-area">
                    <form id="messageForm">
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
                            <button type="button" class="send-btn" id="sendBtn" onclick="sendMessage()">
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
        const currentUserType = <?php echo $user ? "'user'" : "'worker'"; ?>; // Simple: if user exists, type is 'user', else 'worker'
        const recipientName = '<?php echo addslashes($recipientInfo['name'] ?? 'Recipient'); ?>';

        console.log('Chat initialized:', {
            conversationId: activeConversationId,
            userId: currentUserId,
            userType: currentUserType
        });

        // --- Initial UI setup ---
        if (messagesArea) {
            // Use setTimeout to ensure DOM is fully rendered before scrolling
            setTimeout(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }, 100);
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
                    console.log('Enter key pressed at:', new Date().toISOString());
                    e.preventDefault();
                    window.sendMessage();
                }
            });
        }

        // ===================================
        //  Send Message Function
        // ===================================
        let isSubmitting = false;
        const displayedMessageIds = new Set();
        
        window.sendMessage = async function() {
            console.log('sendMessage called at:', new Date().toISOString());
            console.log('Call stack:', new Error().stack);
            
            // Prevent double submissions
            if (isSubmitting) {
                console.log('Already submitting, ignoring duplicate submission');
                return;
            }

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

            isSubmitting = true;
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
                        console.log('Attempting to display user message:', result.message);
                        displayMessage(result.message);
                    } else {
                        console.log('No message in response!');
                    }

                    // Display bot's responses (if any)
                    if (result.botMessages && result.botMessages.length > 0) {
                        result.botMessages.forEach((botMsg, index) => {
                            setTimeout(() => {
                                displayMessage(botMsg);
                            }, index * 500); // Stagger bot responses
                        });
                    }

                    // Display package cards if available
                    if (result.packages && result.packages.length > 0) {
                        setTimeout(() => {
                            displayPackageCards(result.packages);
                        }, (result.botMessages?.length || 0) * 500 + 500);
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
                isSubmitting = false;
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        };

        // Form submission handler - DISABLED to prevent double submission
        // The form submit event was causing double calls even with preventDefault
        // Now we only use direct function calls from button and Enter key
        /*
        if (messageForm) {
            messageForm.addEventListener('submit', function(event) {
                console.log('Form submit event triggered at:', new Date().toISOString());
                event.preventDefault();
                window.sendMessage();
            });
        }
        */

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

            // Prevent duplicate display of the same message
            if (msg.message_id && displayedMessageIds.has(msg.message_id)) {
                console.log('Message already displayed, skipping:', msg.message_id);
                return;
            }

            // Check if bot FIRST (sender_id = 0 means AI)
            const isBot = msg.sender_id == 0 || msg.sender_type === 'ai' || msg.sender_type === 'bot';
            
            console.log('CHECK: currentUserId =', currentUserId, ', currentUserType =', currentUserType);
            console.log('CHECK: msg.sender_id =', msg.sender_id, ', msg.sender_type =', msg.sender_type);
            console.log('CHECK: IDs match?', msg.sender_id == currentUserId, ', Types match?', msg.sender_type === currentUserType);
            
            // Then check if sent by current user (MUST match BOTH sender_id AND sender_type)
            const isSent = !isBot && (msg.sender_id == currentUserId && msg.sender_type === currentUserType);

            let senderName;
            let messageClass;
            if (isBot) {
                senderName = 'AI Assistant';
                messageClass = 'bot-message';
            } else if (isSent) {
                senderName = 'You';
                messageClass = 'sent';
            } else {
                senderName = recipientName;
                messageClass = 'received';
            }
            
            console.log('Final class:', messageClass, 'for message from sender_id:', msg.sender_id, 'sender_type:', msg.sender_type);

            const messageGroup = document.createElement('div');
            messageGroup.className = `message-group ${messageClass}`;

            // Format message text (preserve line breaks, make bold text work)
            let formattedText = msg.message_text
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
                .replace(/\*(.*?)\*/g, '<em>$1</em>'); // Italic

            messageGroup.innerHTML = `
            ${isBot ? '<div class="bot-badge"><i class="fas fa-robot"></i> AI</div>' : ''}
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
            
            // Track this message as displayed
            if (msg.message_id) {
                displayedMessageIds.add(msg.message_id);
            }
            
            // Use setTimeout to ensure smooth scrolling after DOM update
            setTimeout(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }, 50);
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        }

        // ===================================
        //  Display Package Cards
        // ===================================
        function displayPackageCards(packages) {
            const packageContainer = document.createElement('div');
            packageContainer.className = 'package-cards-container';
            
            packages.forEach((pkg, index) => {
                const packageCard = document.createElement('div');
                packageCard.className = 'package-card';
                packageCard.setAttribute('data-package-id', pkg.package_id);
                
                packageCard.innerHTML = `
                    <div class="package-header">
                        <span class="package-number">${index + 1}</span>
                        <h4>${pkg.name}</h4>
                    </div>
                    <p class="package-description">${pkg.description || 'No description available'}</p>
                    <div class="package-price">₱${parseFloat(pkg.price).toLocaleString('en-PH', {minimumFractionDigits: 2})}</div>
                    <div class="package-details">
                        ${pkg.duration_hours ? `<div class="detail"><i class="fas fa-clock"></i> ${pkg.duration_hours} hours</div>` : ''}
                        ${pkg.photo_count ? `<div class="detail"><i class="fas fa-camera"></i> ${pkg.photo_count} photos</div>` : ''}
                        ${pkg.delivery_days ? `<div class="detail"><i class="fas fa-calendar"></i> ${pkg.delivery_days} days delivery</div>` : ''}
                    </div>
                    <button class="btn-select-package" onclick="selectPackage(${index + 1}, '${pkg.name}')">
                        <i class="fas fa-check-circle"></i> Select Package ${index + 1}
                    </button>
                `;
                
                packageContainer.appendChild(packageCard);
            });
            
            messagesArea.appendChild(packageContainer);
            setTimeout(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }, 100);
        }

        // ===================================
        //  Select Package Function
        // ===================================
        window.selectPackage = function(packageNumber, packageName) {
            // Send the package selection as a message
            messageText.value = packageNumber.toString();
            sendMessage();
        };

        // ===================================
        //  Load Conversation Function
        // ===================================
        window.loadConversation = function (conversationId) {
            // On mobile, show the chat area and hide sidebar
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.conversations-sidebar');
                const chatArea = document.querySelector('.chat-area');
                if (sidebar && chatArea) {
                    sidebar.classList.add('mobile-hidden');
                    chatArea.classList.add('mobile-active');
                }
            }
            window.location.href = `?controller=Chat&action=view&conversation_id=${conversationId}`;
        };

        // ===================================
        //  Mobile Sidebar Toggle
        // ===================================
        window.showMobileSidebar = function() {
            const sidebar = document.querySelector('.conversations-sidebar');
            const chatArea = document.querySelector('.chat-area');
            if (sidebar && chatArea) {
                sidebar.classList.remove('mobile-hidden');
                chatArea.classList.remove('mobile-active');
            }
        };

        // On page load, if on mobile and there's an active conversation, show chat
        if (window.innerWidth <= 768 && activeConversationId) {
            const sidebar = document.querySelector('.conversations-sidebar');
            const chatArea = document.querySelector('.chat-area');
            if (sidebar && chatArea) {
                sidebar.classList.add('mobile-hidden');
                chatArea.classList.add('mobile-active');
            }
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
                window.sendMessage();
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
    
    // ===================================
    //  Proposal Actions
    // ===================================
    async function acceptProposal(conversationId) {
        if (!confirm('Accept this proposal and confirm the booking?')) return;
        
        try {
            const response = await fetch('?controller=Chat&action=acceptProposal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            alert('Failed to accept proposal');
            console.error(error);
        }
    }
    
    function showRejectProposalModal() {
        const reason = prompt('Why are you declining this proposal? (optional)');
        if (reason !== null) { // User didn't cancel
            rejectProposal(reason);
        }
    }
    
    async function rejectProposal(reason) {
        const conversationId = <?php echo $activeConversation['conversation_id'] ?? 0; ?>;
        
        try {
            const response = await fetch('?controller=Chat&action=rejectProposal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}&reason=${encodeURIComponent(reason || '')}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        } catch (error) {
            alert('Failed to reject proposal');
            console.error(error);
        }
    }
</script>

<style>
.negotiation-banner {
    background: linear-gradient(135deg, rgba(255, 107, 0, 0.1) 0%, rgba(255, 133, 51, 0.1) 100%);
    border: 2px solid rgba(255, 107, 0, 0.3);
    border-radius: 12px;
    padding: 20px;
    margin: 15px;
    display: flex;
    gap: 15px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.banner-icon {
    font-size: 40px;
    color: #ff6b00;
    display: flex;
    align-items: center;
}

.banner-content {
    flex: 1;
}

.banner-content h4 {
    color: #ff6b00;
    margin: 0 0 10px 0;
    font-size: 18px;
}

.banner-content p {
    margin: 8px 0;
    color: #e0e0e0;
}

.banner-content strong {
    color: #ff6b00;
}

.proposal-notes {
    background: rgba(255, 107, 0, 0.05);
    padding: 10px;
    border-left: 3px solid #ff6b00;
    border-radius: 4px;
    margin: 10px 0;
}

.banner-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-accept-proposal {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-accept-proposal:hover {
    background: #218838;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    transform: translateY(-2px);
}

.btn-reject-proposal {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    border: 1px solid rgba(220, 53, 69, 0.3);
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-reject-proposal:hover {
    background: rgba(220, 53, 69, 0.2);
    border-color: #dc3545;
}

/* Bot/AI Message Styling - Override default message colors */
.message-group.bot-message {
    justify-content: center !important;
    margin: 20px 0;
}

.message-group.bot-message .message-bubble {
    background: linear-gradient(135deg, rgba(138, 43, 226, 0.2) 0%, rgba(147, 51, 234, 0.2) 100%) !important;
    border: 1px solid rgba(138, 43, 226, 0.5) !important;
    border-color: rgba(138, 43, 226, 0.5) !important;
    color: #e0e0e0 !important;
    max-width: 80%;
    box-shadow: 0 2px 8px rgba(138, 43, 226, 0.3) !important;
}

.message-group.bot-message .message-avatar {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important;
}

.bot-badge {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 8px;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
}

.bot-badge i {
    margin-right: 4px;
}

/* Make sure worker messages appear on left for users */
.message-group.received {
    justify-content: flex-start;
}

.message-group.sent {
    justify-content: flex-end;
}
</style>

</body>
</html>