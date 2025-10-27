<?php

require_once __DIR__ . '/../model/repositories/ChatRepository.php';

class ChatController
{
    private ChatRepository $chatRepo;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->chatRepo = new ChatRepository();
    }

    // ========================================
    // MAIN CHAT PAGE
    // ========================================
    
    public function index()
    {
        $this->view();
    }

    public function view()
    {
        $user = $_SESSION['user'] ?? null;
        $worker = $_SESSION['worker'] ?? null;

        if (!$user && !$worker) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $userId = $user['user_id'] ?? $worker['worker_id'] ?? null;
        $userType = $user ? 'user' : ($worker ? 'worker' : null);

        $conversations = $this->chatRepo->getConversationsForUser($userId, $userType);

        $activeConversationId = $_GET['conversation_id'] ?? null;
        $activeConversation = null;
        $messages = [];
        $recipientInfo = null;
        $tempBooking = [];

        if ($activeConversationId) {
            $activeConversation = $this->chatRepo->getConversationById($activeConversationId);
            if ($activeConversation) {
                $messages = $this->chatRepo->getMessagesForConversation($activeConversationId);

                $recipientId = $userType === 'user'
                    ? $activeConversation['worker_id']
                    : $activeConversation['user_id'];
                $recipientInfo = $this->chatRepo->getRecipientInfo($recipientId, $userType);

                $recipientInfo['conversation_type'] = $activeConversation['type'];
                $recipientInfo['booking_status'] = $activeConversation['booking_status'];
                $recipientInfo['conversation_id'] = $activeConversation['conversation_id'];
                
                if ($userType === 'user') {
                    $recipientInfo['worker_id'] = $recipientId;
                } else {
                    $recipientInfo['user_id'] = $recipientId;
                }

                $tempBooking = $this->chatRepo->getTempBooking($activeConversationId);
            }
        }

        require __DIR__ . '/../views/home/messages.php';
    }

    // ========================================
    // BOOKING INITIATION
    // ========================================
    
    public function newBooking(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;

        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $userId = $user['user_id'];
        $workerId = $_GET['worker_id'] ?? null;

        if (!$workerId) {
            header('Location: index.php?controller=Browse&action=browse');
            exit;
        }

        $existingConversation = $this->chatRepo->findIncompleteBooking($userId, $workerId);

        if ($existingConversation) {
            header("Location: index.php?controller=Chat&action=view&conversation_id={$existingConversation['conversation_id']}");
            exit;
        }

        $conversationId = $this->chatRepo->createAiConversation($userId, $workerId);

        if ($conversationId) {
            $initialAiMessage = "Hi! I'm Kislap's AI assistant. I'm here to help you get started with your booking by asking a few quick questions. What kind of **service** are you looking for? (e.g., event, portrait, product, etc.)";

            $this->chatRepo->saveMessage(
                $conversationId,
                0,
                'ai',
                $initialAiMessage
            );

            header("Location: index.php?controller=Chat&action=view&conversation_id={$conversationId}");
            exit;
        } else {
            $_SESSION['error'] = 'Failed to start new booking.';
            header('Location: index.php?controller=Browse&action=browse');
            exit;
        }
    }

    // ========================================
    // MESSAGE HANDLING
    // ========================================
    
    public function sendMessage()
    {
        header('Content-Type: application/json');

        try {
            $user = $_SESSION['user'] ?? null;
            $worker = $_SESSION['worker'] ?? null;

            if (!$user && !$worker) {
                echo json_encode(['success' => false, 'error' => 'Not authenticated']);
                exit;
            }

            $conversationId = $_POST['conversation_id'] ?? null;
            $messageText = trim($_POST['message'] ?? '');
            $userId = $user['user_id'] ?? $worker['worker_id'];
            $userType = $user ? 'user' : 'worker';

            if (!$conversationId || !$messageText) {
                echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                exit;
            }

            $conversation = $this->chatRepo->getConversationById($conversationId);
            if (!$conversation) {
                echo json_encode(['success' => false, 'error' => 'Conversation not found']);
                exit;
            }

            $userMessage = $this->chatRepo->saveMessage(
                $conversationId,
                $userId,
                $userType,
                $messageText
            );

            $response = ['success' => true, 'message' => $userMessage];

            if ($conversation['type'] === 'ai' && $userType === 'user') {
                $aiResponse = $this->processAiMessage($conversationId, $messageText, $conversation);

                if ($aiResponse['botMessage']) {
                    $response['botMessages'] = [$aiResponse['botMessage']];
                }

                if (!empty($aiResponse['packages'])) {
                    $response['packages'] = $aiResponse['packages'];
                }
            }

            echo json_encode($response);

        } catch (Exception $e) {
            error_log('[ChatController] Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // ========================================
    // AI MESSAGE PROCESSING
    // ========================================
    
    private function processAiMessage(int $conversationId, string $userMessage, array $conversation): array
    {
        $tempBooking = $this->chatRepo->getTempBooking($conversationId);

        if ($this->wantsHumanAgent($userMessage)) {
            $this->chatRepo->updateConversationType($conversationId, 'direct');
            $this->chatRepo->updateConversationStatus($conversationId, 'pending_worker');

            $botMessage = $this->chatRepo->saveMessage(
                $conversationId, 0, 'bot',
                "I'll connect you with the photographer now. They'll respond to you shortly! 👋"
            );

            return ['botMessage' => $botMessage, 'packages' => []];
        }

        // Check if user is responding to "no packages available" question
        if ($this->allDetailsCollected($tempBooking) && $this->wantsPhotographerAfterNoPackages($userMessage)) {
            $this->chatRepo->updateConversationType($conversationId, 'direct');
            $this->chatRepo->updateConversationStatus($conversationId, 'pending_worker');

            $botMessage = $this->chatRepo->saveMessage(
                $conversationId, 0, 'bot',
                "Perfect! I'm redirecting you to the photographer now. They'll be able to discuss custom options with you directly. 📸✨"
            );

            return ['botMessage' => $botMessage, 'packages' => []];
        }

        $extracted = $this->extractBookingInfo($userMessage, $tempBooking);

        if (!empty($extracted)) {
            $this->chatRepo->updateTempBooking($conversationId, $extracted);
            $tempBooking = array_merge($tempBooking, $extracted);
        }

        $botResponseData = $this->generateBotResponse($tempBooking, $conversation, $userMessage);

        $botMessage = $this->chatRepo->saveMessage(
            $conversationId, 0, 'bot',
            $botResponseData['message']
        );

        return [
            'botMessage' => $botMessage,
            'packages' => $botResponseData['packages'] ?? []
        ];
    }

    // ========================================
    // INFORMATION EXTRACTION
    // ========================================
    
    private function extractBookingInfo(string $message, array $tempBooking): array
    {
        $extracted = [];
        $lowerMessage = strtolower(trim($message));

        if ($this->allDetailsCollected($tempBooking) && is_numeric($lowerMessage)) {
            $packageNumber = (int)$lowerMessage;
            if ($packageNumber >= 1 && $packageNumber <= 3) {
                $extracted['package_selection'] = $packageNumber;
                return $extracted;
            }
        }

        if (empty($tempBooking['event_type'])) {
            $eventTypes = [
                'wedding' => 'Wedding',
                'birthday' => 'Birthday',
                'debut' => 'Debut',
                'christening' => 'Christening',
                'baptism' => 'Baptism',
                'corporate' => 'Corporate Event',
                'portrait' => 'Portrait Session',
                'graduation' => 'Graduation',
                'anniversary' => 'Anniversary',
                'engagement' => 'Engagement'
            ];

            foreach ($eventTypes as $keyword => $fullName) {
                if (stripos($lowerMessage, $keyword) !== false) {
                    $extracted['event_type'] = $fullName;
                    return $extracted;
                }
            }

            $extracted['event_type'] = ucwords($message);
            return $extracted;
        }

        if (empty($tempBooking['event_date']) && !empty($tempBooking['event_type'])) {
            $parsedDate = $this->parseDate($message);
            if ($parsedDate) {
                $extracted['event_date'] = $parsedDate;
                return $extracted;
            }
        }

        if (empty($tempBooking['event_location']) &&
            !empty($tempBooking['event_type']) &&
            !empty($tempBooking['event_date'])) {

            if (!preg_match('/^\d+$/', $message)) {
                $extracted['event_location'] = ucwords(trim($message));
                return $extracted;
            }
        }

        if (empty($tempBooking['budget']) &&
            !empty($tempBooking['event_type']) &&
            !empty($tempBooking['event_date']) &&
            !empty($tempBooking['event_location'])) {

            if (preg_match('/(\d+(?:,\d{3})*(?:\.\d{2})?)/', $message, $matches)) {
                $budget = (float)str_replace(',', '', $matches[1]);
                if ($budget >= 100) {
                    $extracted['budget'] = $budget;
                    return $extracted;
                }
            }
        }

        return $extracted;
    }

    // ========================================
    // BOT RESPONSE GENERATION
    // ========================================
    
    private function generateBotResponse(array $tempBooking, array $conversation, string $userMessage): array
    {
        if (!empty($tempBooking['package_selection']) && empty($tempBooking['package_id'])) {
            $packages = $this->chatRepo->getSuggestedPackages(
                $conversation['worker_id'],
                $tempBooking['budget']
            );

            $packageIndex = $tempBooking['package_selection'] - 1;

            if (isset($packages[$packageIndex])) {
                $selectedPackage = $packages[$packageIndex];

                $this->chatRepo->updateTempBooking($conversation['conversation_id'], [
                    'package_id' => $selectedPackage['package_id']
                ]);

                $this->chatRepo->updateConversationType($conversation['conversation_id'], 'direct');
                $this->chatRepo->updateConversationStatus($conversation['conversation_id'], 'pending_worker');

                return [
                    'message' => "Perfect choice! 🎉\n\n" .
                        "Your booking request has been sent to the photographer.\n\n" .
                        "📋 **Booking Summary:**\n" .
                        "📸 Event: {$tempBooking['event_type']}\n" .
                        "📅 Date: " . date('F d, Y', strtotime($tempBooking['event_date'])) . "\n" .
                        "📍 Location: {$tempBooking['event_location']}\n" .
                        "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n" .
                        "📦 Package: {$selectedPackage['name']}\n\n" .
                        "The photographer will review your request and respond here. You can now chat with them directly!",
                    'packages' => []
                ];
            } else {
                return [
                    'message' => "Please select a valid package number (1, 2, or 3).",
                    'packages' => $packages
                ];
            }
        }

        $missingFields = $this->getMissingFields($tempBooking);

        if (empty($missingFields)) {
            $packages = $this->chatRepo->getSuggestedPackages(
                $conversation['worker_id'],
                $tempBooking['budget']
            );

            if (empty($packages)) {
                return [
                    'message' => "Great! I have all the details:\n\n" .
                        "📸 Event: {$tempBooking['event_type']}\n" .
                        "📅 Date: " . date('F d, Y', strtotime($tempBooking['event_date'])) . "\n" .
                        "📍 Location: {$tempBooking['event_location']}\n" .
                        "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
                        "Unfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?",
                    'packages' => []
                ];
            }

            return [
                'message' => "Perfect! Here's a summary of your event:\n\n" .
                    "📸 Event: {$tempBooking['event_type']}\n" .
                    "📅 Date: " . date('F d, Y', strtotime($tempBooking['event_date'])) . "\n" .
                    "📍 Location: {$tempBooking['event_location']}\n" .
                    "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
                    "Here are the recommended packages below. **Type the package number (1, 2, or 3)** to select one:",
                'packages' => $packages
            ];
        }

        return [
            'message' => $this->getNextQuestion($missingFields[0], $tempBooking),
            'packages' => []
        ];
    }

    // ========================================
    // HELPER FUNCTIONS
    // ========================================

    private function wantsHumanAgent(string $message): bool
    {
        $keywords = ['human', 'agent', 'person', 'photographer', 'real person', 'talk to someone'];
        $lowerMessage = strtolower($message);

        foreach ($keywords as $keyword) {
            if (stripos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function wantsPhotographerAfterNoPackages(string $message): bool
    {
        $positiveKeywords = [
            'yes', 'yeah', 'yep', 'sure', 'ok', 'okay', 'alright', 'fine',
            'i would like', 'i want', 'please', 'connect me', 'talk to',
            'speak with', 'contact', 'photographer', 'direct', 'human'
        ];
        
        $lowerMessage = strtolower(trim($message));

        // Check for positive responses
        foreach ($positiveKeywords as $keyword) {
            if (stripos($lowerMessage, $keyword) !== false) {
                return true;
            }
        }

        // Check for simple affirmative responses
        if (in_array($lowerMessage, ['y', 'yes', 'yep', 'yeah', 'sure', 'ok', 'okay'])) {
            return true;
        }

        return false;
    }

    private function allDetailsCollected(array $tempBooking): bool
    {
        return !empty($tempBooking['event_type']) &&
            !empty($tempBooking['event_date']) &&
            !empty($tempBooking['event_location']) &&
            !empty($tempBooking['budget']) &&
            empty($tempBooking['package_id']);
    }

    private function getMissingFields(array $tempBooking): array
    {
        $missing = [];

        if (empty($tempBooking['event_type'])) $missing[] = 'event_type';
        if (empty($tempBooking['event_date'])) $missing[] = 'event_date';
        if (empty($tempBooking['event_location'])) $missing[] = 'event_location';
        if (empty($tempBooking['budget'])) $missing[] = 'budget';

        return $missing;
    }

    private function getNextQuestion(string $field, array $tempBooking): string
    {
        switch ($field) {
            case 'event_type':
                return "Hi! I'm here to help you book a photographer. 📸\n\n" .
                    "What type of event are you planning?\n" .
                    "(e.g., Wedding, Birthday, Portrait, Corporate)";

            case 'event_date':
                return "Great! When is your **{$tempBooking['event_type']}** scheduled?\n" .
                    "(e.g., December 25, 2024 or 12/25/2024)";

            case 'event_location':
                return "Perfect! Where will the event take place?\n" .
                    "(e.g., Manila, Quezon City, or specific venue)";

            case 'budget':
                return "Almost done! What's your budget for photography?\n" .
                    "(e.g., 10000 or 15000)";

            default:
                return "I need a bit more information. Could you tell me about your event?";
        }
    }

    private function parseDate(string $message): ?string
    {
        // Try strtotime first
        $timestamp = strtotime($message);
        if ($timestamp !== false && $timestamp > strtotime('-1 year')) {
            return date('Y-m-d', $timestamp);
        }

        // Try common patterns: "october 25" or "oct 25"
        if (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s+(\d{1,2})/i', $message, $matches)) {
            $month = $matches[1];
            $day = $matches[2];
            $year = date('Y');

            if (preg_match('/\b(20\d{2})\b/', $message, $yearMatch)) {
                $year = $yearMatch[1];
            }

            $timestamp = strtotime("$month $day, $year");
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        // Try "MM/DD" or "MM-DD"
        if (preg_match('/(\d{1,2})[\/\-](\d{1,2})/', $message, $matches)) {
            $month = $matches[1];
            $day = $matches[2];
            $year = date('Y');

            if (preg_match('/\b(20\d{2})\b/', $message, $yearMatch)) {
                $year = $yearMatch[1];
            }

            $timestamp = strtotime("$year-$month-$day");
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        return null;
    }

    // ========================================
    // MESSAGE POLLING
    // ========================================
    
    public function fetchNewMessages()
    {
        header('Content-Type: application/json');

        $user = $_SESSION['user'] ?? null;
        $worker = $_SESSION['worker'] ?? null;

        if (!$user && !$worker) {
            echo json_encode([]);
            exit;
        }

        $conversationId = $_GET['conversation_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;

        if (!$conversationId) {
            echo json_encode([]);
            exit;
        }

        $newMessages = $this->chatRepo->getNewMessages($conversationId, $lastMessageId);
        echo json_encode($newMessages);
        exit;
    }

    // ========================================
    // PROPOSAL ACTIONS
    // ========================================

    public function acceptProposal()
    {
        header('Content-Type: application/json');

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        if ($this->chatRepo->acceptProposal($conversationId)) {
            $this->chatRepo->saveMessage(
                $conversationId,
                $user['user_id'],
                'user',
                "I accept your proposal! Let's proceed with the booking."
            );

            echo json_encode(['success' => true, 'message' => 'Proposal accepted! Booking confirmed.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to accept proposal']);
        }
        exit;
    }

    public function rejectProposal()
    {
        header('Content-Type: application/json');

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $reason = $_POST['reason'] ?? null;

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        if ($this->chatRepo->rejectProposal($conversationId, $reason)) {
            $message = "I cannot accept this proposal.";
            if ($reason) {
                $message .= " Reason: " . $reason;
            }

            $this->chatRepo->saveMessage(
                $conversationId,
                $user['user_id'],
                'user',
                $message
            );

            echo json_encode(['success' => true, 'message' => 'Proposal rejected']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to reject proposal']);
        }
        exit;
    }

    // ========================================
    // BOOKING ACTIONS
    // ========================================

    public function startBooking()
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $workerId = $_GET['worker_id'] ?? null;
        if (!$workerId) {
            header('Location: index.php?controller=Chat&action=view');
            exit;
        }

        // Check if there's already an incomplete booking
        $existingConversation = $this->chatRepo->findIncompleteBooking($user['user_id'], $workerId);
        
        if ($existingConversation) {
            // Redirect to existing conversation
            header("Location: index.php?controller=Chat&action=view&conversation_id=" . $existingConversation['conversation_id']);
            exit;
        }

        // Create new AI conversation for booking
        $conversationId = $this->chatRepo->createAiConversation($user['user_id'], $workerId);
        
        // Redirect to the new conversation
        header("Location: index.php?controller=Chat&action=view&conversation_id=" . $conversationId);
        exit;
    }

    public function cancelBooking()
    {
        header('Content-Type: application/json');
        
        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_POST['conversation_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        
        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        try {
            // Update booking status to cancelled
            $success = $this->chatRepo->updateBookingStatus($conversationId, 'cancelled');
            
            if ($success) {
                // Send cancellation message
                $this->chatRepo->saveMessage(
                    $conversationId,
                    $user['user_id'],
                    'user',
                    "🚫 I've cancelled this booking. Reason: " . ($reason ?: 'No reason provided')
                );
                
                echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to cancel booking']);
            }
        } catch (Exception $e) {
            error_log("Error cancelling booking: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }
}