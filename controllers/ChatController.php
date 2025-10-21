<?php

require_once __DIR__ . "/../model/repositories/ChatRepository.php";

class ChatController
{
    private ChatRepository $repo;
    private $currentUser;
    private $userType;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->repo = new ChatRepository();

        // Set current user context
        $this->currentUser = $_SESSION['user'] ?? $_SESSION['worker'] ?? null;
        $this->userType = isset($_SESSION['user']) ? 'user' : 'worker';
    }

    public function view()
    {
        if (!$this->currentUser) {
            header("Location: /Kislap/index.php?controller=Auth&action=login");
            exit;
        }

        $currentUserId = $this->currentUser[$this->userType . '_id'];

        $conversations = $this->repo->getConversationsForUser($currentUserId, $this->userType);
        $activeConversation = null;
        $messages = [];
        $recipientInfo = null;

        $conversationId = filter_input(INPUT_GET, 'conversation_id', FILTER_SANITIZE_NUMBER_INT);
        $startWithWorkerId = filter_input(INPUT_GET, 'worker_id', FILTER_SANITIZE_NUMBER_INT);

        if ($startWithWorkerId && $this->userType === 'user') {
            // User clicked "Book Now" - Start AI chat
            $existing = $this->repo->findAiConversation($currentUserId, $startWithWorkerId);
            if ($existing) {
                $conversationId = $existing['conversation_id'];
            } else {
                $conversationId = $this->repo->createAiConversation($currentUserId, $startWithWorkerId);
            }
            // Redirect to the clean URL
            header("Location: ?controller=Chat&action=view&conversation_id=" . $conversationId);
            exit;

        } elseif ($conversationId) {
            // Load an existing conversation
            $activeConversation = $this->repo->getConversationById($conversationId);
            if ($activeConversation) {
                $messages = $this->repo->getMessagesForConversation($conversationId);

                // Determine recipient ID based on conversation type
                if ($activeConversation['type'] === 'ai' && $this->userType === 'user') {
                    // For AI conversations, show worker info
                    $recipientId = $activeConversation['worker_id'];
                    $recipientType = 'worker';
                } else {
                    // For human conversations
                    $recipientId = $this->userType === 'user'
                        ? $activeConversation['worker_id']
                        : $activeConversation['user_id'];
                    $recipientType = $this->userType === 'user' ? 'worker' : 'user';
                }

                $recipientInfo = $this->repo->getRecipientInfo($recipientId, $recipientType);

                // Add conversation type to recipient info
                $recipientInfo['conversation_type'] = $activeConversation['type'];
                $recipientInfo['booking_status'] = $activeConversation['booking_status'];
            }
        }

        // Pass data to the view
        require __DIR__ . "/../views/home/messages.php";
    }

    public function sendMessage()
    {
        // Add at the very start of the method
        error_log("=== sendMessage called ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("Session user: " . print_r($this->currentUser, true));

        if (!$this->currentUser) {
            error_log("ERROR: No authenticated user");
            $this->jsonResponse(['success' => false, 'error' => 'Not authenticated'], 401);
            return;
        }

        $conversationId = filter_input(INPUT_POST, 'conversation_id', FILTER_SANITIZE_NUMBER_INT);
        $messageText = trim($_POST['message'] ?? ''); // Changed from filter_input to handle special chars

        error_log("Conversation ID: $conversationId");
        error_log("Message text: $messageText");

        if (empty($conversationId)) {
            error_log("ERROR: Missing conversation_id");
            $this->jsonResponse(['success' => false, 'error' => 'Missing conversation ID'], 400);
            return;
        }

        if (empty($messageText)) {
            error_log("ERROR: Empty message");
            $this->jsonResponse(['success' => false, 'error' => 'Empty message'], 400);
            return;
        }

        try {
            $currentUserId = $this->currentUser[$this->userType . '_id'];
            error_log("Current user ID: $currentUserId, Type: {$this->userType}");

            // 1. Save the user's message
            $userMessage = $this->repo->saveMessage($conversationId, $currentUserId, $this->userType, $messageText);
            error_log("User message saved: " . print_r($userMessage, true));

            $conversation = $this->repo->getConversationById($conversationId);
            $botMessages = [];

            // 2. If conversation is 'ai', process the message
            if ($conversation && $conversation['type'] === 'ai' && $this->userType === 'user') {
                error_log("Processing AI response...");
                $botMessages = $this->processAiMessage($conversationId, $messageText);
                error_log("Bot messages: " . print_r($botMessages, true));
            }

            // 3. Return response
            $this->jsonResponse([
                'success' => true,
                'message' => $userMessage,
                'botMessages' => $botMessages
            ]);

        } catch (Exception $e) {
            error_log("ERROR in sendMessage: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function fetchNewMessages()
    {
        if (!$this->currentUser) {
            $this->jsonResponse([], 401);
            return;
        }

        $conversationId = filter_input(INPUT_GET, 'conversation_id', FILTER_SANITIZE_NUMBER_INT);
        $lastMessageId = filter_input(INPUT_GET, 'last_message_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$conversationId) {
            $this->jsonResponse([]);
            return;
        }

        $newMessages = $this->repo->getNewMessages($conversationId, $lastMessageId);
        $this->jsonResponse($newMessages);
    }
    private function processAiMessage(int $conversationId, string $userMessage): array
    {
        $conversation = $this->repo->getConversationById($conversationId);
        $tempBooking = $this->repo->getTempBooking($conversationId);
        $replies = [];

        // Simple NLP: Intent Detection
        $intent = $this->detectIntent($userMessage);

        // Simple NLP: Entity Extraction
        $entities = $this->extractEntities($userMessage);

        // Update temp booking with any new info
        if (!empty($entities)) {
            $this->repo->updateTempBooking($conversationId, $entities);
            // Refresh tempBooking object
            $tempBooking = array_merge($tempBooking, $entities);
        }

        // Global override: Talk to human
        if ($intent === 'talk_to_human' && $conversation['type'] === 'ai') {
            $this->repo->updateConversationType($conversationId, 'human');
            $this->repo->updateConversationStatus($conversationId, 'pending_worker');
            $replies = ["No problem! I'm connecting you with the photographer now. They will respond to you shortly. 🤝"];

            // Save all bot replies
            $savedBotMessages = [];
            foreach ($replies as $replyText) {
                $savedBotMessages[] = $this->repo->saveMessage($conversationId, 0, 'bot', $replyText);
            }
            return $savedBotMessages;
        }

        // AI State Machine
        switch ($conversation['booking_status']) {
            case 'pending_ai':
            case 'pending_details':
                $this->repo->updateConversationStatus($conversationId, 'pending_details');

                // Ask for the next missing piece of info
                if (empty($tempBooking['event_type'])) {
                    $replies[] = "Got it! 📸 What type of event is it? (e.g., Wedding, Birthday, Portrait, Corporate Event)";
                } elseif (empty($tempBooking['event_date'])) {
                    $replies[] = "Perfect! 📅 What is the date of your event? (Please use format: YYYY-MM-DD, e.g., 2025-12-25)";
                } elseif (empty($tempBooking['event_location'])) {
                    $replies[] = "Great! 📍 Where will the event be held? (City or specific venue)";
                } elseif (empty($tempBooking['budget'])) {
                    $replies[] = "Almost done! 💰 What is your approximate budget for photography? (e.g., ₱5000 or 5000)";
                } else {
                    // All details collected! Move to confirmation.
                    $this->repo->updateConversationStatus($conversationId, 'pending_confirmation');

                    // Suggest packages
                    $packages = $this->repo->getSuggestedPackages($conversation['worker_id'], $tempBooking['budget']);

                    if (!empty($packages)) {
                        $packageMsg = "Based on your budget, here are some recommended packages:\n\n";
                        foreach ($packages as $pkg) {
                            $packageMsg .= "📦 *{$pkg['name']}* - ₱" . number_format($pkg['price'], 2) . "\n";
                            if (!empty($pkg['description'])) {
                                $packageMsg .= "   " . substr($pkg['description'], 0, 80) . "...\n";
                            }
                        }
                        $replies[] = $packageMsg;
                    }

                    // Build confirmation message
                    $confirmationMsg = $this->buildConfirmationMessage($tempBooking);
                    $replies[] = $confirmationMsg;
                }
                break;

            case 'pending_confirmation':
                if ($intent === 'confirm_yes') {
                    // Handoff to human
                    $this->repo->updateConversationType($conversationId, 'human');
                    $this->repo->updateConversationStatus($conversationId, 'pending_worker');

                    $replies[] = "Excellent! ✅ I've summarized your request and notified the photographer. They will get back to you here to finalize the booking.\n\nYou are now connected directly with them. 🤝";

                } elseif ($intent === 'confirm_no' || $intent === 'change_detail') {
                    $this->repo->updateConversationStatus($conversationId, 'pending_details');
                    $replies[] = "No problem! Let me help you correct that. Which detail would you like to change?\n\nYou can say:\n• 'Change date to 2025-12-25'\n• 'Update location to Manila'\n• 'Change event type to Wedding'\n• 'Update budget to 10000'";

                } else {
                    $replies[] = "I didn't quite understand that. 🤔 Are the details I listed correct?\n\nPlease reply with:\n• **Yes** to confirm\n• **No** to make changes";
                }
                break;

            case 'pending_worker':
            case 'confirmed':
                // Once handed off to human, AI doesn't respond unless explicitly asked
                if ($intent === 'talk_to_ai') {
                    $replies[] = "Hi again! 👋 Your booking request has been sent to the photographer. Is there something else I can help you with?";
                }
                break;
        }

        // Save all bot replies to the database
        $savedBotMessages = [];
        foreach ($replies as $replyText) {
            $savedBotMessages[] = $this->repo->saveMessage($conversationId, 0, 'bot', $replyText);
        }
        return $savedBotMessages;
    }

    private function buildConfirmationMessage(array $tempBooking): string
    {
        $date = $tempBooking['event_date'] ? date('F j, Y', strtotime($tempBooking['event_date'])) : 'Not set';

        return "📋 Please confirm if these details are correct:\n\n" .
            "• **Event Type:** {$tempBooking['event_type']}\n" .
            "• **Date:** $date\n" .
            "• **Location:** {$tempBooking['event_location']}\n" .
            "• **Budget:** ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
            "Is everything correct? (Reply **Yes** to confirm or **No** to make changes)";
    }

    // --- Simple NLP Methods ---

    private function detectIntent(string $message): string
    {
        $message = strtolower($message);

        // Confirmation
        if (preg_match('/(^yes$|^yep$|^yeah$|^correct$|^confirm$|sounds good|looks good|that\'?s right)/i', $message)) {
            return 'confirm_yes';
        }

        // Rejection
        if (preg_match('/(^no$|^nope$|^wrong$|^incorrect$|not right)/i', $message)) {
            return 'confirm_no';
        }

        // Change request
        if (preg_match('/(change|update|edit|modify|fix)/i', $message)) {
            return 'change_detail';
        }

        // Human agent request
        if (preg_match('/(human|agent|person|real person|talk to (?:a )?(?:real )?(?:person|human|someone))/i', $message)) {
            return 'talk_to_human';
        }

        // Back to AI
        if (preg_match('/(talk to (?:the )?(?:ai|bot|assistant))/i', $message)) {
            return 'talk_to_ai';
        }

        return 'provide_info';
    }

    private function extractEntities(string $message): array
    {
        $entities = [];

        // Date extraction (YYYY-MM-DD or MM/DD/YYYY or natural language)
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $message, $m)) {
            $entities['event_date'] = $m[1];
        } elseif (preg_match('!(\d{1,2}/\d{1,2}/\d{4})!', $message, $m)) {
            $entities['event_date'] = date('Y-m-d', strtotime($m[1]));
        } elseif (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{1,2}(?:,?\s+\d{4})?/i', $message, $m)) {
            $entities['event_date'] = date('Y-m-d', strtotime($m[0]));
        }

        // Budget extraction
        if (preg_match('/(₱|php|pesos?|budget(?:\s+(?:of|is))?)[\s:]*([0-9,]+(?:\.\d{2})?)/i', $message, $m)) {
            $entities['budget'] = (float)str_replace(',', '', $m[2]);
        } elseif (preg_match('/\b([0-9,]+(?:\.\d{2})?)\s*(?:₱|php|pesos?)\b/i', $message, $m)) {
            $entities['budget'] = (float)str_replace(',', '', $m[1]);
        }

        // Location extraction
        if (preg_match('/(at|in|near|location:?)\s+([\w\s,]+?)(?:\.|$|,\s*(?:on|at|in|near|budget))/i', $message, $m)) {
            $entities['event_location'] = ucwords(trim($m[2]));
        }

        // Event type extraction
        if (preg_match('/(wedding|birthday|portrait|corporate|christening|debut|anniversary|graduation|engagement|pre-?nup|prenuptial)/i', $message, $m)) {
            $entities['event_type'] = ucfirst(strtolower($m[1]));
        }

        return $entities;
    }

    private function jsonResponse(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
