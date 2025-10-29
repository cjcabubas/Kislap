<?php
// ========================================
// SESSION MANAGEMENT
// ========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$worker = $_SESSION['worker'] ?? null;

if (!$user && !$worker) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

// ========================================
// USER TYPE DETECTION
// ========================================

$userType = $user ? 'user' : 'worker';
$senderType = $userType;

error_log("DEBUG messages.php: user=" . ($user ? 'SET' : 'NULL') . ", worker=" . ($worker ? 'SET' : 'NULL') . ", userType=$userType, senderType=$senderType");

// ========================================
// DATA INITIALIZATION
// ========================================

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

<!-- ========================================
     MESSAGES CONTAINER
     ======================================== -->

<div class="messages-container">
    <div class="messages-wrapper">
        <!-- ========================================
             CONVERSATIONS SIDEBAR
             ======================================== -->
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
                        // Debug: Check userType at start of loop
                        error_log("DEBUG: Loop start - userType: '$userType'");
                        
                        $convId = $conv['conversation_id'] ?? 0;
                        $otherUserName = $conv['other_user_name'] ?? 'Unknown User';
                        $rawLastMessage = $conv['last_message'] ?? 'No messages yet';
                        
                        // Process last message to show nice preview for images
                        $lastMessage = $rawLastMessage;
                        if (preg_match('/\[IMAGE:.*?\]/', $lastMessage)) {
                            // Check if there's text along with the image
                            $textOnly = preg_replace('/\[IMAGE:.*?\]/', '', $lastMessage);
                            $textOnly = trim($textOnly);
                            
                            if (!empty($textOnly)) {
                                $lastMessage = "📷 " . $textOnly;
                            } else {
                                $lastMessage = "📷 Photo";
                            }
                        }
                        $lastMessageTime = $conv['last_message_time'] ?? '';
                        $unreadCount = $conv['unread_count'] ?? 0;
                        $isActive = isset($activeConversation['conversation_id']) && $convId == $activeConversation['conversation_id'];
                        ?>
                        <div class="conversation-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $unreadCount > 0 ? 'unread' : ''; ?>"
                             onclick="loadConversation(<?php echo $convId; ?>)">
                            <div class="conversation-header">
                                <div class="user-avatar">
                                    <?php
                                    // Get the other user's profile photo
                                    $otherUserPhoto = null;
                                    
                                    // Debug: Check userType and available fields
                                    error_log("DEBUG: Sidebar - userType: '$userType', worker_profile_photo: " . ($conv['worker_profile_photo'] ?? 'NOT_SET') . ", user_profile_photo: " . ($conv['user_profile_photo'] ?? 'NOT_SET'));
                                    
                                    // Fix: Handle both 'user' and 'Customer' values (in case of variable modification)
                                    if ($userType === 'user' || $userType === 'Customer') {
                                        // Current user is a customer, so other user is a worker
                                        $otherUserPhoto = $conv['worker_profile_photo'] ?? null;
                                        error_log("DEBUG: Sidebar - Branch: user/customer, selected worker_profile_photo: " . ($otherUserPhoto ?? 'NULL'));
                                    } else {
                                        // Current user is a worker, so other user is a customer
                                        $otherUserPhoto = $conv['user_profile_photo'] ?? null;
                                        error_log("DEBUG: Sidebar - Branch: worker, selected user_profile_photo: " . ($otherUserPhoto ?? 'NULL'));
                                    }
                                    
                                    // Debug: Log conversation photo data
                                    error_log("DEBUG: Conversation sidebar - Conv ID: {$convId}, User Type: {$userType}, Photo: " . ($otherUserPhoto ?? 'NULL'));
                                    error_log("DEBUG: Conversation sidebar - Full conv data: " . print_r($conv, true));
                                    
                                    if ($otherUserPhoto) {
                                        // Handle different path formats for users vs workers
                                        if (strpos($otherUserPhoto, '/Kislap/') === 0) {
                                            // User path format: /Kislap/uploads/user/...
                                            $cleanPath = $otherUserPhoto;
                                        } else {
                                            // Worker path format: uploads/workers/...
                                            $cleanPath = '/Kislap/' . ltrim($otherUserPhoto, '/');
                                        }
                                        
                                        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $cleanPath;
                                        
                                        // Debug: Log conversation photo paths
                                        error_log("DEBUG: Conversation sidebar - Conv ID: {$convId}, Original: $otherUserPhoto, Clean: $cleanPath, Exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
                                        
                                        if (file_exists($fullPath)) {
                                            echo '<img src="' . htmlspecialchars($cleanPath) . '" alt="' . htmlspecialchars($otherUserName) . '">';
                                        } else {
                                            echo strtoupper(substr($otherUserName, 0, 2));
                                        }
                                    } else {
                                        echo strtoupper(substr($otherUserName, 0, 2));
                                    }
                                    ?>
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
                            
                            // Debug: Log recipient info data
                            error_log("DEBUG: Chat header - Recipient Name: $recipientName, Picture: " . ($recipientPicture ?? 'NULL'));
                            error_log("DEBUG: Chat header - Full recipient info: " . print_r($recipientInfo, true));
                            
                            // Clean up the path and check if file exists
                            if ($recipientPicture) {
                                // Handle different path formats for users vs workers
                                if (strpos($recipientPicture, '/Kislap/') === 0) {
                                    // User path format: /Kislap/uploads/user/...
                                    $cleanPath = $recipientPicture;
                                } else {
                                    // Worker path format: uploads/workers/...
                                    $cleanPath = '/Kislap/' . ltrim($recipientPicture, '/');
                                }
                                
                                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $cleanPath;
                                
                                // Debug: Log the paths for troubleshooting
                                error_log("DEBUG: Recipient avatar - Original: $recipientPicture, Clean: $cleanPath, Full: $fullPath, Exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
                                
                                if (file_exists($fullPath)) {
                                    echo '<img src="' . htmlspecialchars($cleanPath) . '" alt="Profile">';
                                } else {
                                    echo strtoupper(substr($recipientName, 0, 2));
                                }
                            } else {
                                echo strtoupper(substr($recipientName, 0, 2));
                            }
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
                                <i class="fas fa-headset"></i>
                            </button>
                        <?php endif; ?>
                        <?php if ($user): ?>
                            <!-- Customer/User buttons -->
                            <button class="chat-action-btn" title="Book Again" onclick="bookAgain(<?php echo $recipientInfo['worker_id'] ?? 0; ?>)">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                            <button class="chat-action-btn" title="View Profile" onclick="viewProfile(<?php echo $recipientInfo['worker_id'] ?? 0; ?>)">
                                <i class="fas fa-user"></i>
                            </button>
                            <button class="chat-action-btn" title="My Bookings" onclick="goToBookings('user')">
                                <i class="fas fa-calendar-check"></i>
                            </button>
                        <?php elseif ($worker): ?>
                            <!-- Worker buttons -->
                            <button class="chat-action-btn" title="View Customer Profile" onclick="viewCustomerProfile(<?php echo $recipientInfo['user_id'] ?? 0; ?>)">
                                <i class="fas fa-user"></i>
                            </button>
                            <button class="chat-action-btn" title="My Bookings" onclick="goToBookings('worker')">
                                <i class="fas fa-calendar-check"></i>
                            </button>
                        <?php endif; ?>
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
                                    // Skip avatar for bot messages
                                    if ($senderId == 0 || $senderType === 'bot' || $senderType === 'ai') {
                                        echo '<div class="bot-avatar"><i class="fas fa-robot"></i></div>';
                                    } else {
                                        // Get sender profile pic
                                        $senderPicture = null;
                                        if ($isSent) {
                                            $senderPicture = $user['profilePhotoUrl'] ?? $worker['profile_photo'] ?? null;
                                        } else {
                                            $senderPicture = $recipientInfo['profile_picture'] ?? null;
                                        }

                                        // Clean up the path and check if file exists
                                        if ($senderPicture) {
                                            // Handle different path formats for users vs workers
                                            if (strpos($senderPicture, '/Kislap/') === 0) {
                                                // User path format: /Kislap/uploads/user/...
                                                $cleanPath = $senderPicture;
                                            } else {
                                                // Worker path format: uploads/workers/...
                                                $cleanPath = '/Kislap/' . ltrim($senderPicture, '/');
                                            }
                                            
                                            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $cleanPath;
                                            
                                            if (file_exists($fullPath)) {
                                                echo '<img src="' . htmlspecialchars($cleanPath) . '" alt="Avatar">';
                                            } else {
                                                echo strtoupper(substr($senderName, 0, 2));
                                            }
                                        } else {
                                            echo strtoupper(substr($senderName, 0, 2));
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="message-content">
                                    <?php
                                    // Parse images from message text
                                    $displayText = $messageText;
                                    $images = [];
                                    
                                    // Extract images
                                    if (preg_match_all('/\[IMAGE:(.*?)\]/', $messageText, $matches)) {
                                        foreach ($matches[1] as $imagePath) {
                                            $images[] = $imagePath;
                                        }
                                        // Remove image tags from display text
                                        $displayText = preg_replace('/\[IMAGE:(.*?)\]/', '', $displayText);
                                        $displayText = trim($displayText);
                                    }
                                    ?>
                                    
                                    <?php if (!empty($displayText)): ?>
                                        <div class="message-bubble">
                                            <?php echo nl2br(htmlspecialchars($displayText)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($images as $imagePath): ?>
                                        <div class="message-image">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                                 alt="Shared image" 
                                                 onclick="openImageModal('<?php echo htmlspecialchars($imagePath); ?>')"
                                                 style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;">
                                        </div>
                                    <?php endforeach; ?>
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
                                    <p><strong>Proposed Price:</strong>
                                        ₱<?php echo number_format($tempBooking['worker_proposed_price'], 2); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($tempBooking['worker_proposed_date'])): ?>
                                    <p><strong>Proposed
                                            Date:</strong> <?php echo date('F d, Y', strtotime($tempBooking['worker_proposed_date'])); ?>

                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($tempBooking['worker_notes'])): ?>
                                    <p class="proposal-notes">
                                        <em><?php echo htmlspecialchars($tempBooking['worker_notes']); ?></em></p>
                                <?php endif; ?>
                                <div class="banner-actions">
                                    <button class="btn-accept-proposal"
                                            onclick="acceptProposal(<?php echo $activeConversation['conversation_id']; ?>)">
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

                <?php
// Check if rating repository exists and get rating status
                $alreadyRated = false;
                if (isset($activeConversation['conversation_id']) && file_exists(__DIR__ . '/../../model/repositories/RatingRepository.php')) {
                    require_once __DIR__ . '/../../model/repositories/RatingRepository.php';
                    $ratingRepo = new RatingRepository();
                    $alreadyRated = $ratingRepo->checkIfRated($activeConversation['conversation_id']);
                }
                ?>

                <?php
// Check if rating repository exists and get rating status
                $alreadyRated = false;
                if (isset($activeConversation['conversation_id']) && file_exists(__DIR__ . '/../../model/repositories/RatingRepository.php')) {
                    require_once __DIR__ . '/../../model/repositories/RatingRepository.php';
                    $ratingRepo = new RatingRepository();
                    $alreadyRated = $ratingRepo->checkIfRated($activeConversation['conversation_id']);
                }
                ?>

                <!-- Replace the payment bubble section in messages.php with this: -->

                <?php
// 1. Show deposit payment bubble for CONFIRMED bookings (only for clients)
                if ($user &&
                        isset($activeConversation['booking_status']) &&
                        $activeConversation['booking_status'] === 'confirmed' &&
                        isset($tempBooking) && is_array($tempBooking)):

                    $finalPrice = $tempBooking['final_price'] ?? $tempBooking['budget'] ?? 0;
                    $depositAmount = $finalPrice * 0.5;
                    $depositPaid = !empty($tempBooking['deposit_paid_at']);
                    ?>
                    <div class="payment-bubble-wrapper">
                        <div class="payment-bubble <?php echo $depositPaid ? 'paid' : 'pending'; ?>"
                             data-tooltip="<?php echo $depositPaid ? '                          Deposit Paid - Click for details' : 'Click to Pay Deposit'; ?>"
                             onclick="<?php echo $depositPaid ? 'togglePaymentDetails(this)' : 'payDeposit(' . $activeConversation['conversation_id'] . ')'; ?>">
                            <div class="bubble-icon">
                                <i class="fas fa-<?php echo $depositPaid ? 'check-circle' : 'credit-card'; ?>"></i>
                            </div>
                        </div>

                        <?php if ($depositPaid): ?>
                            <div class="payment-details">
                                <div class="payment-breakdown">
                                    <div class="breakdown-row">
                                        <span>Total Price:</span>
                                        <strong>₱<?php echo number_format($finalPrice, 2); ?></strong>
                                    </div>
                                    <div class="breakdown-row success">
                                        <span><i class="fas fa-check-circle"></i> Down Payment:</span>
                                        <strong>₱<?php echo number_format($depositAmount, 2); ?></strong>
                                    </div>
                                    <div class="breakdown-row">
                                        <span>Remaining Balance:</span>
                                        <strong>₱<?php echo number_format($depositAmount, 2); ?></strong>
                                    </div>
                                    <p class="payment-info">
                                        <i class="fas fa-info-circle"></i>
                                        Paid
                                        on <?php echo date('M d, Y', strtotime($tempBooking['deposit_paid_at'])); ?>
                                        <br>Balance due after service
                                        on <?php echo date('M d, Y', strtotime($tempBooking['event_date'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php
// 2. Show completion payment bubble (after event date, when deposit is paid)
                if ($user &&
                        isset($activeConversation['booking_status']) &&
                        $activeConversation['booking_status'] === 'confirmed' &&
                        isset($tempBooking) &&
                        !empty($tempBooking['deposit_paid_at']) &&
                        !empty($tempBooking['event_date']) &&
                        strtotime($tempBooking['event_date']) < time() &&
                        empty($tempBooking['completed_at'])):

                    $finalPrice = $tempBooking['final_price'] ?? $tempBooking['budget'] ?? 0;
                    $remainingBalance = $finalPrice * 0.5;
                    ?>
                    <div class="payment-bubble-wrapper">
                        <div class="payment-bubble pending-completion"
                             data-tooltip="Complete Service & Pay Balance"
                             onclick="payFullAmount(<?php echo $activeConversation['conversation_id']; ?>)">
                            <div class="bubble-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <div class="bubble-info">
                            <span class="bubble-label">Service Complete?</span>
                            <span class="bubble-amount">Pay ₱<?php echo number_format($remainingBalance, 2); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
// 3. Show rating bubble (when service is completed)
                if ($user &&
                        isset($activeConversation['booking_status']) &&
                        $activeConversation['booking_status'] === 'completed' &&
                        isset($tempBooking) &&
                        !empty($tempBooking['completed_at'])):
                    ?>
                    <div class="payment-bubble-wrapper">
                        <div class="payment-bubble completed <?php echo $alreadyRated ? 'rated' : ''; ?>"
                             data-tooltip="<?php echo $alreadyRated ? 'Service Rated' : 'Click to Rate Service'; ?>"
                             onclick="<?php echo $alreadyRated ? 'togglePaymentDetails(this)' : 'showRatingModal(' . $activeConversation['conversation_id'] . ')'; ?>">
                            <div class="bubble-icon">
                                <i class="fas fa-<?php echo $alreadyRated ? 'heart' : 'star'; ?>"></i>
                            </div>
                        </div>

                        <?php if (!$alreadyRated): ?>
                            <div class="bubble-info">
                                <span class="bubble-label">Rate Service</span>
                            </div>
                        <?php else: ?>
                            <div class="payment-details">
                                <div class="payment-breakdown">
                                    <p class="completion-message success">
                                        <i class="fas fa-check-circle"></i>
                                        Booking completed
                                        on <?php echo date('M d, Y', strtotime($tempBooking['completed_at'])); ?>!
                                    </p>
                                    <p class="rating-thankyou">
                                        <i class="fas fa-heart"></i> Thank you for your review!
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="message-input-area">
                    <form id="messageForm">
                        <div class="input-wrapper">
                            <button type="button" class="attachment-btn" id="attachmentBtn"
                                    onclick="document.getElementById('fileInput').click()" 
                                    title="Send Photo">
                                <i class="fas fa-camera"></i>
                            </button>
                            <input type="file" id="fileInput" accept="image/*" style="display: none;">
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
    // Global variables accessible to all functions
    const activeConversationId = <?php echo $activeConversation['conversation_id'] ?? 'null'; ?>;
    const currentUserId = <?php echo($user['user_id'] ?? $worker['worker_id'] ?? 'null'); ?>;
    const currentUserType = <?php echo $user ? "'user'" : "'worker'"; ?>;
    const recipientName = '<?php echo addslashes($recipientInfo['name'] ?? 'Recipient'); ?>';
    const currentUserPhoto = '<?php echo addslashes($user['profilePhotoUrl'] ?? $worker['profile_photo'] ?? ''); ?>';
    const recipientPhoto = '<?php echo addslashes($recipientInfo['profile_picture'] ?? ''); ?>';
    const bookingData = <?php echo $tempBooking ? json_encode($tempBooking) : 'null'; ?>;

    document.addEventListener('DOMContentLoaded', function () {
        // Get essential data from PHP (now moved to global scope above)

        console.log('Chat initialized:', {
            conversationId: activeConversationId,
            userId: currentUserId,
            userType: currentUserType
        });
        const messageText = document.getElementById('messageText');
        const messagesArea = document.getElementById('messagesArea');
        const sendBtn = document.getElementById('sendBtn');
        const fileInput = document.getElementById('fileInput');
        const searchInput = document.getElementById('searchConversations');
        
        // Define file select handler
        function handleFileSelect(event) {
            console.log('DEBUG: handleFileSelect called');
            const file = event.target.files[0];
            const attachmentBtn = document.getElementById('attachmentBtn');
            
            console.log('DEBUG: Selected file:', file);
            console.log('DEBUG: Attachment button:', attachmentBtn);
            
            if (file) {
                console.log('DEBUG: File details:', {
                    name: file.name,
                    type: file.type,
                    size: file.size
                });
                
                // Show file selected state
                attachmentBtn.innerHTML = '<i class="fas fa-check-circle"></i>';
                attachmentBtn.style.color = '#28a745';
                attachmentBtn.title = `Selected: ${file.name}`;
                
                console.log('DEBUG: Button updated to checkmark');
                
                // Show preview (optional)
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        console.log('DEBUG: Image loaded for preview');
                    };
                    reader.readAsDataURL(file);
                }
            } else {
                console.log('DEBUG: No file selected, resetting button');
                // Reset button state
                attachmentBtn.innerHTML = '<i class="fas fa-camera"></i>';
                attachmentBtn.style.color = '';
                attachmentBtn.title = 'Send Photo';
            }
        }

        // Add file input event listener
        if (fileInput) {
            fileInput.addEventListener('change', handleFileSelect);
            console.log('DEBUG: File input event listener added');
        } else {
            console.log('DEBUG: File input not found!');
        }

        const displayedMessageIds = new Set();
        let isSubmitting = false;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['message_id'] : 0; ?>;

        // Scroll to bottom on page load
        if (messagesArea) {
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

            // Enter key to send (Shift+Enter for new line)
            messageText.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    window.sendMessage();
                }
            });
        }

        // ========================================
        // AVATAR HELPER FUNCTION
        // ========================================
        
        function getAvatarHTML(isBot, isSent, senderName) {
            if (isBot) {
                return '<div class="bot-avatar"><i class="fas fa-robot"></i></div>';
            }
            
            let photoPath = '';
            if (isSent) {
                photoPath = currentUserPhoto;
            } else {
                photoPath = recipientPhoto;
            }
            
            // Clean up empty or whitespace-only paths
            photoPath = photoPath ? photoPath.trim() : '';
            
            if (photoPath && photoPath !== '') {
                // Handle different path formats for users vs workers
                let cleanPath = '';
                if (photoPath.startsWith('/Kislap/')) {
                    // User path format: /Kislap/uploads/user/...
                    cleanPath = photoPath;
                } else {
                    // Worker path format: uploads/workers/...
                    cleanPath = '/Kislap/' + photoPath.replace(/^\/+/, '');
                }
                
                const initials = senderName.substring(0, 2).toUpperCase();
                
                return `<img src="${cleanPath}" alt="Avatar" style="width:100%; height:100%; object-fit:cover;" 
                            onerror="this.style.display='none'; this.parentNode.innerHTML='${initials}';">`;
            } else {
                // No photo - show initials directly
                return senderName.substring(0, 2).toUpperCase();
            }
        }



        // ========================================
        // SEND MESSAGE
        // ========================================

        window.sendMessage = async function () {
            // Prevent double submissions
            if (isSubmitting) {
                console.log('Already submitting, ignoring');
                return;
            }

            const message = messageText.value.trim();
            const file = fileInput.files[0];

            if (!message && !file) {
                return;
            }

            if (!activeConversationId) {
                alert('No active conversation. Please refresh the page.');
                return;
            }

            isSubmitting = true;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('conversation_id', activeConversationId);
            formData.append('message', message);
            if (file) {
                console.log('DEBUG: Sending file:', file.name, file.type, file.size);
                formData.append('attachment', file);
            } else {
                console.log('DEBUG: No file selected');
            }

            try {
                const response = await fetch('?controller=Chat&action=sendMessage', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    // Display user's message
                    if (result.message) {
                        displayMessage(result.message);
                    }

                    // Display bot responses (if any)
                    if (result.botMessages && result.botMessages.length > 0) {
                        result.botMessages.forEach((botMsg, index) => {
                            setTimeout(() => {
                                displayMessage(botMsg);
                            }, index * 500);
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
                    
                    // Reset attachment button
                    const attachmentBtn = document.getElementById('attachmentBtn');
                    attachmentBtn.innerHTML = '<i class="fas fa-camera"></i>';
                    attachmentBtn.style.color = '';
                    attachmentBtn.title = 'Send Photo';
                } else {
                    alert('Error: ' + (result.error || 'Could not send message'));
                }

            } catch (error) {
                console.error('Network error:', error);
                alert('Network error. Please try again.');
            } finally {
                isSubmitting = false;
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        };

        // ========================================
        // DISPLAY MESSAGE
        // ========================================

        function displayMessage(msg) {
            if (!messagesArea) return;

            // Prevent duplicate display
            if (msg.message_id && displayedMessageIds.has(msg.message_id)) {
                return;
            }

            // Determine message type
            const isBot = msg.sender_id == 0 || msg.sender_type === 'ai' || msg.sender_type === 'bot';
            const isSent = !isBot && (msg.sender_id == currentUserId && msg.sender_type === currentUserType);

            let senderName, messageClass;

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

            // Format message text and handle images
            let formattedText = msg.message_text;
            let imageHTML = '';
            
            // Extract images from message text
            const imageRegex = /\[IMAGE:(.*?)\]/g;
            let match;
            while ((match = imageRegex.exec(formattedText)) !== null) {
                const imagePath = match[1];
                imageHTML += `<div class="message-image">
                    <img src="${imagePath}" alt="Shared image" onclick="openImageModal('${imagePath}')" style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;">
                </div>`;
                // Remove the image tag from text
                formattedText = formattedText.replace(match[0], '');
            }
            
            // Format remaining text
            formattedText = formattedText
                .trim()
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            const messageGroup = document.createElement('div');
            messageGroup.className = `message-group ${messageClass}`;
            messageGroup.innerHTML = `
            ${isBot ? '<div class="bot-badge"><i class="fas fa-robot"></i> AI</div>' : ''}
            <div class="message-avatar">
                ${getAvatarHTML(isBot, isSent, senderName)}
            </div>
            <div class="message-content">
                ${formattedText ? `<div class="message-bubble">${formattedText}</div>` : ''}
                ${imageHTML}
                <span class="message-time">${formatTime(msg.sent_at)}</span>
            </div>
        `;

            messagesArea.appendChild(messageGroup);

            if (msg.message_id) {
                displayedMessageIds.add(msg.message_id);
            }

            setTimeout(() => {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }, 50);
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        }

        // ========================================
        // DISPLAY PACKAGE CARDS
        // ========================================

        function displayPackageCards(packages) {
            const packageContainer = document.createElement('div');
            packageContainer.className = 'package-cards-container';

            packages.forEach((pkg, index) => {
                const packageCard = document.createElement('div');
                packageCard.className = 'package-card';

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

        window.selectPackage = function (packageNumber, packageName) {
            messageText.value = packageNumber.toString();
            sendMessage();
        };

        // ========================================
        // CONVERSATION SEARCH
        // ========================================

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

        // ========================================
        // NAVIGATION
        // ========================================

        window.loadConversation = function (conversationId) {
            // On mobile, show chat area
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

        window.showMobileSidebar = function () {
            const sidebar = document.querySelector('.conversations-sidebar');
            const chatArea = document.querySelector('.chat-area');
            if (sidebar && chatArea) {
                sidebar.classList.remove('mobile-hidden');
                chatArea.classList.remove('mobile-active');
            }
        };

        // Mobile setup
        if (window.innerWidth <= 768 && activeConversationId) {
            const sidebar = document.querySelector('.conversations-sidebar');
            const chatArea = document.querySelector('.chat-area');
            if (sidebar && chatArea) {
                sidebar.classList.add('mobile-hidden');
                chatArea.classList.add('mobile-active');
            }
        }

        // ========================================
        // FILE HANDLING
        // ========================================

        window.handleFileSelect = function () {
            if (fileInput) {
                fileInput.click();
            }
        };

        // ========================================
        // REQUEST HUMAN AGENT
        // ========================================

        window.requestHumanAgent = function () {
            if (confirm('Would you like to be connected to the photographer directly?')) {
                messageText.value = 'I would like to talk to a human agent';
                window.sendMessage();
            }
        };

        // ========================================
        // POLLING FOR NEW MESSAGES
        // ========================================

        function startPollingForMessages() {
            if (!activeConversationId) return;

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
            }, 5000);
        }

        startPollingForMessages();
    });

    // ========================================
    // PROPOSAL ACTIONS
    // ========================================

    async function acceptProposal(conversationId) {
        if (!confirm('Accept this proposal and confirm the booking?')) return;

        try {
            const response = await fetch('?controller=Chat&action=acceptProposal', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
        // Create modal if it doesn't exist
        let modal = document.getElementById('rejectProposalModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'rejectProposalModal';
            modal.className = 'reject-modal';
            modal.innerHTML = `
            <div class="reject-modal-content">
                <span class="close" onclick="closeRejectProposalModal()">&times;</span>
                <h3>Decline Proposal</h3>
                <label>Why are you declining this proposal?</label>
                <textarea id="rejectProposalReason" placeholder="Please provide a reason (optional but recommended)..." rows="4"></textarea>
                <div class="reject-modal-actions">
                    <button class="btn-confirm-reject" onclick="confirmRejectProposal()">
                        <i class="fas fa-times"></i> Decline Proposal
                    </button>
                    <button class="btn-cancel-reject" onclick="closeRejectProposalModal()">
                        Cancel
                    </button>
                </div>
            </div>
        `;
            document.body.appendChild(modal);
        }
        modal.style.display = 'block';
    }

    function closeRejectProposalModal() {
        const modal = document.getElementById('rejectProposalModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function confirmRejectProposal() {
        const reason = document.getElementById('rejectProposalReason').value;
        rejectProposal(reason);
        closeRejectProposalModal();
    }

    async function rejectProposal(reason) {
        const conversationId = <?php echo $activeConversation['conversation_id'] ?? 0; ?>;

        try {
            const response = await fetch('?controller=Chat&action=rejectProposal', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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

    // ========================================
    // PAYMENT FUNCTIONS
    // ========================================


    function payDeposit(conversationId) {
        console.log('payDeposit called with conversationId:', conversationId);
        console.log('bookingData:', bookingData);
        console.log('recipientName:', recipientName);
        
        if (!bookingData) {
            console.error('No booking data available');
            return;
        }

        console.log('Showing deposit payment modal');
        showDepositPaymentModal(conversationId, bookingData);
    }

    function showDepositPaymentModal(conversationId, booking) {
        console.log('showDepositPaymentModal called');
        console.log('conversationId:', conversationId);
        console.log('booking:', booking);
        
        const finalPrice = booking.final_price || booking.budget || 0;
        const depositAmount = finalPrice * 0.5;
        
        console.log('finalPrice:', finalPrice);
        console.log('depositAmount:', depositAmount);
        
        const modal = document.createElement('div');
        modal.className = 'payment-modal-overlay';
        modal.innerHTML = `
            <div class="payment-modal">
                <div class="modal-header">
                    <h3><i class="fas fa-credit-card"></i> Deposit Payment</h3>
                    <button class="close-btn" onclick="closePaymentModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="booking-summary">
                        <h4>Booking Details</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Photographer:</span>
                                <span class="detail-value">${recipientName}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Event Type:</span>
                                <span class="detail-value">${booking.event_type || 'Photography Session'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Event Date:</span>
                                <span class="detail-value">${booking.event_date ? new Date(booking.event_date).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                }) : 'TBD'}</span>
                            </div>
                            ${booking.event_time ? `
                            <div class="detail-item">
                                <span class="detail-label">Event Time:</span>
                                <span class="detail-value">${booking.event_time}</span>
                            </div>
                            ` : ''}
                            <div class="detail-item">
                                <span class="detail-label">Location:</span>
                                <span class="detail-value">${booking.event_location || 'Not specified'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Price:</span>
                                <span class="detail-value">₱${parseFloat(finalPrice).toLocaleString()}</span>
                            </div>
                            <div class="detail-item deposit-highlight">
                                <span class="detail-label">Deposit Required:</span>
                                <span class="detail-value">₱${parseFloat(depositAmount).toLocaleString()}</span>
                            </div>
                        </div>
                    </div>
                    <div class="payment-notice">
                        <i class="fas fa-info-circle"></i>
                        <p>By paying this deposit, you confirm your booking. The remaining balance will be due after the service is completed.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel" onclick="closePaymentModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button class="btn-pay" onclick="processDepositPayment(${conversationId})">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        console.log('Modal added to DOM:', modal);
        console.log('Modal classes:', modal.className);
        console.log('Modal style display:', modal.style.display);
        
        // Force display the modal
        modal.style.display = 'flex';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.zIndex = '9999';
        
        console.log('Modal should now be visible');
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closePaymentModal();
            }
        });
    }

    function closePaymentModal() {
        const modal = document.querySelector('.payment-modal-overlay');
        if (modal) {
            modal.remove();
        }
    }

    async function processDepositPayment(conversationId) {
        const payBtn = document.querySelector('.btn-pay');
        const originalText = payBtn.innerHTML;
        
        // Visual feedback
        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const response = await fetch('?controller=Payment&action=payDeposit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}`
            });

            const result = await response.json();

            if (result.success) {
                closePaymentModal();
                showNotification('success', `✅ Deposit payment of ₱${result.amount.toLocaleString()} processed successfully!`);

                // Reload page to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error(result.error || 'Payment failed');
            }
        } catch (error) {
            console.error('Payment error:', error);
            showNotification('error', 'Payment failed. Please try again.');

            // Restore button
            payBtn.disabled = false;
            payBtn.innerHTML = originalText;
        }
    }

    async function payFullAmount(conversationId) {
        const bubble = event.currentTarget;

        // Visual feedback
        bubble.style.pointerEvents = 'none';
        bubble.style.opacity = '0.6';
        const icon = bubble.querySelector('.bubble-icon i');
        const originalIcon = icon.className;
        icon.className = 'fas fa-spinner fa-spin';

        try {
            const response = await fetch('?controller=Payment&action=payFull', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}`
            });

            const result = await response.json();

            if (result.success) {
                showNotification('success', '🎉 Service completed! Thank you!');

                // Reload to show rating option
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error(result.error || 'Payment failed');
            }
        } catch (error) {
            console.error('Payment error:', error);
            showNotification('error', 'Payment failed. Please try again.');

            // Restore button
            bubble.style.pointerEvents = '';
            bubble.style.opacity = '';
            icon.className = originalIcon;
        }
    }

    function togglePaymentDetails(bubbleElement) {
        const wrapper = bubbleElement.closest('.payment-bubble-wrapper');
        const detailsDiv = wrapper.querySelector('.payment-details');

        if (detailsDiv) {
            const isExpanded = bubbleElement.classList.contains('expanded');

            // Close all other expanded bubbles
            document.querySelectorAll('.payment-bubble.expanded').forEach(b => {
                if (b !== bubbleElement) {
                    b.classList.remove('expanded');
                }
            });

            // Toggle current bubble
            if (isExpanded) {
                bubbleElement.classList.remove('expanded');
            } else {
                bubbleElement.classList.add('expanded');
            }
        }
    }

    // Close details when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.payment-bubble-wrapper')) {
            document.querySelectorAll('.payment-bubble.expanded').forEach(bubble => {
                bubble.classList.remove('expanded');
            });
        }
    });

    // ========================================
    // RATING FUNCTIONS
    // ========================================

    function showRatingModal(conversationId) {
        // Remove existing modal if any
        const existingModal = document.getElementById('ratingModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'ratingModal';
        modal.className = 'rating-modal';
        modal.innerHTML = `
        <div class="rating-modal-content">
            <span class="close" onclick="closeRatingModal()">&times;</span>
            <h2>Rate Your Experience</h2>
            <p class="rating-subtitle">How was your experience with this photographer?</p>

            <div class="star-rating" id="starRating">
                ${[5,4,3,2,1].map(star => `
                    <input type="radio" id="star${star}" name="rating" value="${star}">
                    <label for="star${star}" title="${star} stars">★</label>
                `).join('')}
            </div>

            <div class="selected-rating-text" id="ratingText"></div>

            <textarea id="reviewText" placeholder="Share your experience (optional)" rows="4"></textarea>

            <button onclick="submitRating(${conversationId})" class="btn-submit-rating">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </div>
    `;
        document.body.appendChild(modal);

        // Show modal with animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);

        // Add star rating interaction
        setupStarRating();
    }

    function closeRatingModal() {
        const modal = document.getElementById('ratingModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }

    function setupStarRating() {
        const ratingInputs = document.querySelectorAll('#starRating input[type="radio"]');
        const ratingText = document.getElementById('ratingText');

        const ratingLabels = {
            5: 'Excellent! 🌟',
            4: 'Very Good! 😊',
            3: 'Good 👍',
            2: 'Could be better 😐',
            1: 'Poor 😞'
        };

        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                const rating = this.value;
                ratingText.textContent = ratingLabels[rating];
                ratingText.style.display = 'block';
            });
        });
    }

    async function submitRating(conversationId) {
        const rating = document.querySelector('input[name="rating"]:checked')?.value;
        const review = document.getElementById('reviewText').value.trim();

        if (!rating) {
            showNotification('error', 'Please select a star rating');
            return;
        }

        const submitBtn = document.querySelector('.btn-submit-rating');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        try {
            const response = await fetch('?controller=Rating&action=submitRating', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}&rating=${rating}&review=${encodeURIComponent(review)}`
            });

            const result = await response.json();

            if (result.success) {
                showNotification('success', '⭐ Thank you for your review!');
                closeRatingModal();

                // Reload to show updated status
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error(result.error || 'Failed to submit rating');
            }
        } catch (error) {
            console.error('Rating error:', error);
            showNotification('error', error.message || 'Failed to submit rating. Please try again.');

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Review';
        }
    }

    // ========================================
    // CHAT ACTION FUNCTIONS
    // ========================================

    function bookAgain(workerId) {
        if (!workerId) {
            showNotification('error', 'Invalid photographer ID');
            return;
        }
        
        // Redirect to start new booking conversation
        window.location.href = `?controller=Chat&action=startBooking&worker_id=${workerId}`;
    }

    function viewProfile(workerId) {
        if (!workerId) {
            showNotification('error', 'Invalid photographer ID');
            return;
        }
        
        // Open profile in new tab
        window.open(`?controller=Home&action=profile&worker_id=${workerId}`, '_blank');
    }

    function viewCustomerProfile(userId) {
        if (!userId) {
            showNotification('error', 'Invalid customer ID');
            return;
        }
        
        // Open customer profile in new tab
        window.open(`?controller=User&action=profile&user_id=${userId}`, '_blank');
    }

    function goToBookings(userType) {
        if (userType === 'worker') {
            // Redirect to worker bookings page
            window.location.href = '?controller=Worker&action=bookings';
        } else {
            // Redirect to user/customer bookings page
            window.location.href = '?controller=Home&action=bookings';
        }
    }

    // ========================================
    // IMAGE MODAL
    // ========================================
    
    window.openImageModal = function(imagePath) {
        // Remove existing modal if any
        const existingModal = document.getElementById('imageModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal
        const modal = document.createElement('div');
        modal.id = 'imageModal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            cursor: pointer;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        `;
        
        // Add CSS animation
        if (!document.getElementById('imageModalStyles')) {
            const style = document.createElement('style');
            style.id = 'imageModalStyles';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes scaleIn {
                    from { transform: scale(0.8); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
        
        modal.innerHTML = `
            <div style="
                position: relative; 
                width: 90vw; 
                height: 90vh; 
                display: flex;
                align-items: center;
                justify-content: center;
                animation: scaleIn 0.3s ease;
            ">
                <img src="${imagePath}" style="
                    max-width: calc(90vw - 40px); 
                    max-height: calc(90vh - 40px); 
                    width: auto;
                    height: auto;
                    border-radius: 12px;
                    object-fit: contain;
                    display: block;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
                    border: 2px solid rgba(255, 107, 0, 0.3);
                ">
                <button onclick="closeImageModal()" style="
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    background: linear-gradient(135deg, #ff6b00, #ff8533);
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    cursor: pointer;
                    font-size: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 15px rgba(255, 107, 0, 0.4);
                    z-index: 10001;
                    transition: all 0.2s ease;
                    font-weight: bold;
                " onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">&times;</button>

            </div>
        `;
        
        // Close on background click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeImageModal();
            }
        });
        
        document.body.appendChild(modal);
    };
    
    window.closeImageModal = function() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.remove();
        }
    };

    // ========================================
    // NOTIFICATION SYSTEM
    // ========================================

    function showNotification(type, message) {
        // Remove existing notification if any
        const existing = document.querySelector('.custom-notification');
        if (existing) {
            existing.remove();
        }

        const notification = document.createElement('div');
        notification.className = `custom-notification ${type}`;
        notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;

        document.body.appendChild(notification);

        // Show with animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Auto-hide after 4 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 4000);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('ratingModal');
        if (event.target === modal) {
            closeRatingModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeRatingModal();
        }
    });

    async function payFullAmount(conversationId) {
        // Removed confirm dialog - just process automatically
        try {
            const response = await fetch('?controller=Payment&action=payFull', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
            alert('Payment failed. Please try again.');
            console.error(error);
        }
    }

    async function payFullAmount(conversationId) {
        if (!confirm('Confirm that service is complete and pay the remaining balance?')) return;

        try {
            const response = await fetch('?controller=Payment&action=payFull', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
            alert('Payment failed. Please try again.');
            console.error(error);
        }
    }

    // Add this function with the other payment functions
    function togglePaymentDetails(bubbleElement) {
        const detailsDiv = bubbleElement.nextElementSibling;
        const arrow = bubbleElement.querySelector('.bubble-arrow');

        if (detailsDiv && detailsDiv.classList.contains('payment-details')) {
            if (detailsDiv.style.display === 'none' || !detailsDiv.style.display) {
                detailsDiv.style.display = 'block';
                bubbleElement.classList.add('expanded');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                detailsDiv.style.display = 'none';
                bubbleElement.classList.remove('expanded');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }
    }


</script>
</body>
</html>