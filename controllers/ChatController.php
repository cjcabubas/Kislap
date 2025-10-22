<?php

require_once __DIR__ . '/../model/repositories/ChatRepository.php';

class ChatController
{
    private ChatRepository $chatRepo;
    private string $witAiToken = 'VGDHMP7WYECECRCW4J3BWULPWVRCDO5G';
    private string $witAiUrl = 'https://api.wit.ai/message';
    private string $witAiVersion = '20241022';

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
        // Check if user is logged in
        $user = $_SESSION['user'] ?? null;
        $worker = $_SESSION['worker'] ?? null;
        
        if (!$user && !$worker) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $userId = $user['user_id'] ?? $worker['worker_id'];
        $userType = $user ? 'user' : 'worker';

        // Handle worker_id parameter for starting new chat
        $workerId = $_GET['worker_id'] ?? null;
        if ($workerId) {
            // Check if conversation already exists
            $existingConv = $this->chatRepo->findAiConversation($userId, $workerId);
            
            if ($existingConv) {
                // Redirect to existing conversation
                header("Location: index.php?controller=Chat&action=view&conversation_id={$existingConv['conversation_id']}");
                exit;
            } else {
                // Create new AI conversation
                $conversationId = $this->chatRepo->createAiConversation($userId, $workerId);
                if ($conversationId) {
                    header("Location: index.php?controller=Chat&action=view&conversation_id={$conversationId}");
                    exit;
                }
            }
        }

        // Get all conversations for sidebar
        $conversations = $this->chatRepo->getConversationsForUser($userId, $userType);

        // Get active conversation if specified
        $activeConversationId = $_GET['conversation_id'] ?? null;
        $activeConversation = null;
        $messages = [];
        $recipientInfo = null;

        if ($activeConversationId) {
            $activeConversation = $this->chatRepo->getConversationById($activeConversationId);
            if ($activeConversation) {
                $messages = $this->chatRepo->getMessagesForConversation($activeConversationId);
                
                // Get recipient info
                $recipientId = $userType === 'user' 
                    ? $activeConversation['worker_id'] 
                    : $activeConversation['user_id'];
                $recipientInfo = $this->chatRepo->getRecipientInfo($recipientId, $userType);
                
                // Add conversation type and booking status to recipient info
                $recipientInfo['conversation_type'] = $activeConversation['type'];
                $recipientInfo['booking_status'] = $activeConversation['booking_status'];
                
                // Get temp booking data (for negotiation proposals)
                $tempBooking = $this->chatRepo->getTempBooking($activeConversationId);
            }
        }

        require __DIR__ . '/../views/home/messages.php';
    }

    // ========================================
    // START AI CONVERSATION
    // ========================================
    public function startAiChat()
    {
        header('Content-Type: application/json');
        
        $user = $_SESSION['user'] ?? null;
        $worker = $_SESSION['worker'] ?? null;
        
        if (!$user && !$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $userId = $user['user_id'] ?? $worker['worker_id'];
        $workerId = $_POST['worker_id'] ?? null;

        if (!$workerId) {
            echo json_encode(['success' => false, 'error' => 'Worker ID required']);
            exit;
        }

        // Check if conversation already exists
        $existingConv = $this->chatRepo->findAiConversation($userId, $workerId);
        
        if ($existingConv) {
            echo json_encode([
                'success' => true,
                'conversation_id' => $existingConv['conversation_id'],
                'existing' => true
            ]);
            exit;
        }

        // Create new AI conversation
        $conversationId = $this->chatRepo->createAiConversation($userId, $workerId);

        if ($conversationId) {
            echo json_encode([
                'success' => true,
                'conversation_id' => $conversationId,
                'existing' => false
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
        }
        exit;
    }

    // ========================================
    // SEND MESSAGE (with AI processing)
    // ========================================
    public function sendMessage()
    {
        // Ensure clean JSON output
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

            // Get conversation details
            $conversation = $this->chatRepo->getConversationById($conversationId);
            if (!$conversation) {
                echo json_encode(['success' => false, 'error' => 'Conversation not found']);
                exit;
            }

            // Save user's message
            $userMessage = $this->chatRepo->saveMessage(
                $conversationId,
                $userId,
                $userType,
                $messageText
            );

            // If this is an AI conversation AND sender is a USER (not worker), process with AI
            $botResponse = null;
            if ($conversation['type'] === 'ai' && $userType === 'user') {
                $botResponse = $this->processAiMessage($conversationId, $messageText, $conversation);
            }

            // Format response for the view
            $response = [
                'success' => true,
                'message' => $userMessage
            ];
            
            // If there's a bot response, add it to botMessages array
            if ($botResponse) {
                $response['botMessages'] = [$botResponse];
                
                // Check if we should show packages (only if still in AI mode and package not selected)
                try {
                    // Re-fetch conversation to check if it's still AI type
                    $updatedConversation = $this->chatRepo->getConversationById($conversationId);
                    
                    if ($updatedConversation['type'] === 'ai') {
                        $tempBooking = $this->chatRepo->getTempBooking($conversationId);
                        
                        // Only show packages if all details collected but package not yet selected
                        if (!empty($tempBooking['event_type']) && 
                            !empty($tempBooking['event_date']) && 
                            !empty($tempBooking['event_location']) && 
                            !empty($tempBooking['budget']) &&
                            empty($tempBooking['package_id'])) {
                            
                            $packages = $this->chatRepo->getSuggestedPackages(
                                $conversation['worker_id'],
                                $tempBooking['budget']
                            );
                            
                            if (!empty($packages)) {
                                $response['packages'] = $packages;
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log('[ChatController] Error fetching packages: ' . $e->getMessage());
                    // Don't break the response, just skip packages
                }
            }
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log('[ChatController] Error in sendMessage: ' . $e->getMessage());
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
    private function processAiMessage(int $conversationId, string $userMessage, array $conversation): ?array
    {
        // Get current temp booking data
        $tempBooking = $this->chatRepo->getTempBooking($conversationId);
        
        // Detect intent
        $intent = $this->detectIntent($userMessage);
        
        // Determine if this is a complex message (multiple details at once)
        $isComplexMessage = $this->isComplexMessage($userMessage, $tempBooking);
        
        $entities = [];
        
        if ($isComplexMessage) {
            // Use Wit.ai for complex messages with multiple entities
            error_log("[AI] Using Wit.ai for complex message: $userMessage");
            $entities = $this->extractEntitiesFromWitAi($userMessage);
        }
        
        // If Wit.ai didn't extract anything OR it's a simple step-by-step response, use fallback
        if (empty($entities) && $intent === 'provide_info') {
            error_log("[AI] Using simple extraction for: $userMessage");
            $entities = $this->inferEntitiesFromContext($userMessage, $tempBooking);
        }
        
        // Handle package selection
        if (isset($entities['package_selection'])) {
            $packageNumber = $entities['package_selection'];
            
            // Get the packages again to find the selected one
            $packages = $this->chatRepo->getSuggestedPackages(
                $conversation['worker_id'],
                $tempBooking['budget']
            );
            
            if (isset($packages[$packageNumber - 1])) {
                $selectedPackage = $packages[$packageNumber - 1];
                $entities['package_id'] = $selectedPackage['package_id'];
                unset($entities['package_selection']); // Remove temporary field
                
                // Update conversation status
                $this->chatRepo->updateConversationStatus($conversationId, 'pending_confirmation');
            }
        }
        
        // Update temp booking with extracted entities
        if (!empty($entities)) {
            $this->chatRepo->updateTempBooking($conversationId, $entities);
            $tempBooking = array_merge($tempBooking, $entities);
        }
        
        // Generate bot response based on conversation state
        $botMessage = $this->generateBotResponse($tempBooking, $intent, $conversation, $userMessage);
        
        // Save bot response
        return $this->chatRepo->saveMessage(
            $conversationId,
            0,
            'bot',
            $botMessage
        );
    }

    // ========================================
    // CHECK IF MESSAGE IS COMPLEX (needs Wit.ai)
    // ========================================
    private function isComplexMessage(string $message, array $tempBooking): bool
    {
        // Count how many potential entities are in the message
        $entityCount = 0;
        
        // Check for event type keywords
        if (preg_match('/(wedding|birthday|debut|christening|corporate|portrait|graduation|anniversary|engagement)/i', $message)) {
            $entityCount++;
        }
        
        // Check for date patterns
        if (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december|\d{1,2}\/\d{1,2}|\d{4})/i', $message)) {
            $entityCount++;
        }
        
        // Check for location keywords
        if (preg_match('/(manila|quezon|makati|taguig|pasig|cebu|davao|in\s+\w+|at\s+\w+)/i', $message)) {
            $entityCount++;
        }
        
        // Check for budget/money
        if (preg_match('/(budget|₱|php|pesos|\d{4,})/i', $message)) {
            $entityCount++;
        }
        
        // If message contains 2+ entities, it's complex
        // OR if message is longer than 15 words, it might contain multiple details
        $wordCount = str_word_count($message);
        
        return $entityCount >= 2 || $wordCount > 15;
    }

    // ========================================
    // WIT.AI ENTITY EXTRACTION (for complex messages only)
    // ========================================
    private function extractEntitiesFromWitAi(string $message): array
    {
        if (empty(trim($message))) {
            return [];
        }

        try {
            $url = $this->witAiUrl . '?v=' . $this->witAiVersion . '&q=' . urlencode($message);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->witAiToken,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("[Wit.ai] API error: HTTP $httpCode - $response");
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['entities'])) {
                return [];
            }
            
            return $this->parseWitEntities($data['entities']);
            
        } catch (Exception $e) {
            error_log("[Wit.ai] Exception: " . $e->getMessage());
            return [];
        }
    }

    // ========================================
    // PARSE WIT.AI ENTITIES
    // ========================================
    private function parseWitEntities(array $witEntities): array
    {
        $entities = [];
        
        // Extract event type
        if (isset($witEntities['event_type'][0]['value'])) {
            $entities['event_type'] = ucfirst(strtolower($witEntities['event_type'][0]['value']));
        }
        
        // Extract date
        if (isset($witEntities['wit$datetime'][0]['value'])) {
            $dateValue = $witEntities['wit$datetime'][0]['value'];
            
            if (is_string($dateValue)) {
                $entities['event_date'] = date('Y-m-d', strtotime($dateValue));
            } elseif (isset($dateValue['from'])) {
                $entities['event_date'] = date('Y-m-d', strtotime($dateValue['from']));
            } elseif (isset($dateValue['value'])) {
                $entities['event_date'] = date('Y-m-d', strtotime($dateValue['value']));
            }
        }
        
        // Extract location
        if (isset($witEntities['location'][0]['value'])) {
            $entities['event_location'] = ucwords(strtolower($witEntities['location'][0]['value']));
        }
        
        // Check built-in location
        if (empty($entities['event_location']) && isset($witEntities['wit$location'][0]['resolved']['values'][0]['name'])) {
            $entities['event_location'] = $witEntities['wit$location'][0]['resolved']['values'][0]['name'];
        }
        
        // Extract budget
        if (isset($witEntities['wit$amount_of_money'][0]['value'])) {
            $entities['budget'] = (float)$witEntities['wit$amount_of_money'][0]['value'];
        }
        
        // Fallback to number for budget
        if (empty($entities['budget']) && isset($witEntities['wit$number'][0]['value'])) {
            $number = (float)$witEntities['wit$number'][0]['value'];
            if ($number >= 100) {
                $entities['budget'] = $number;
            }
        }
        
        return $entities;
    }

    // ========================================
    // INFER ENTITIES FROM CONTEXT (Fallback when Wit.ai fails)
    // ========================================
    private function inferEntitiesFromContext(string $message, array $tempBooking): array
    {
        $entities = [];
        $message = trim($message);
        
        // Check if user is selecting a package (after all details are collected)
        if (!empty($tempBooking['event_type']) && 
            !empty($tempBooking['event_date']) && 
            !empty($tempBooking['event_location']) && 
            !empty($tempBooking['budget']) &&
            empty($tempBooking['package_id'])) {
            
            // Check if message is a number (package selection)
            if (is_numeric($message)) {
                $packageNumber = (int)$message;
                // Package number 1, 2, 3 corresponds to the order shown
                // We'll store the package number for now, actual package_id will be resolved later
                $entities['package_selection'] = $packageNumber;
                error_log("[Fallback] Package selected: $packageNumber");
                return $entities;
            }
        }
        
        // If we're missing event_type, assume the message IS the event type
        if (empty($tempBooking['event_type'])) {
            // Clean up the message and use it as event type
            $eventType = ucwords(strtolower($message));
            $entities['event_type'] = $eventType;
            error_log("[Fallback] Inferred event_type: $eventType");
            return $entities;
        }
        
        // If we're missing event_date, try to parse it
        if (empty($tempBooking['event_date']) && !empty($tempBooking['event_type'])) {
            // Try to parse date from message
            $parsedDate = $this->parseDate($message);
            if ($parsedDate) {
                $entities['event_date'] = $parsedDate;
                error_log("[Fallback] Inferred event_date: $parsedDate");
                return $entities;
            }
        }
        
        // If we're missing event_location, assume the message IS the location
        if (empty($tempBooking['event_location']) && !empty($tempBooking['event_type']) && !empty($tempBooking['event_date'])) {
            // Skip if it looks like a date or number
            if (!preg_match('/\d{4}|\d{1,2}\/\d{1,2}|january|february|march|april|may|june|july|august|september|october|november|december/i', $message)) {
                $entities['event_location'] = ucwords(strtolower($message));
                error_log("[Fallback] Inferred event_location: {$entities['event_location']}");
                return $entities;
            }
        }
        
        // Try to extract budget from plain numbers
        if (empty($tempBooking['budget']) && preg_match('/(\d+(?:,\d{3})*(?:\.\d{2})?)/', $message, $matches)) {
            $budget = (float)str_replace(',', '', $matches[1]);
            if ($budget >= 100) {
                $entities['budget'] = $budget;
                error_log("[Fallback] Inferred budget: $budget");
                return $entities;
            }
        }
        
        return $entities;
    }
    
    // ========================================
    // PARSE DATE FROM USER MESSAGE
    // ========================================
    private function parseDate(string $message): ?string
    {
        // Try strtotime first
        $timestamp = strtotime($message);
        if ($timestamp !== false) {
            $date = date('Y-m-d', $timestamp);
            // Make sure it's a future date or reasonable date
            if ($timestamp > strtotime('-1 year')) {
                return $date;
            }
        }
        
        // Try common patterns
        // "october 25" or "oct 25"
        if (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s+(\d{1,2})/i', $message, $matches)) {
            $month = $matches[1];
            $day = $matches[2];
            $year = date('Y'); // Default to current year
            
            // Check if year is mentioned
            if (preg_match('/\b(20\d{2})\b/', $message, $yearMatch)) {
                $year = $yearMatch[1];
            }
            
            $dateStr = "$month $day, $year";
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }
        
        // "25 october" or "25 oct"
        if (preg_match('/(\d{1,2})\s+(january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)/i', $message, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = date('Y');
            
            if (preg_match('/\b(20\d{2})\b/', $message, $yearMatch)) {
                $year = $yearMatch[1];
            }
            
            $dateStr = "$month $day, $year";
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }
        
        // "10/25" or "10-25"
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
    // DETECT INTENT
    // ========================================
    private function detectIntent(string $message): string
    {
        $lowerMessage = strtolower(trim($message));
        
        // Confirmation
        if (preg_match('/^(yes|yep|yeah|yup|sure|ok|okay|correct|oo|opo|tama)$/i', $lowerMessage)) {
            return 'confirm_yes';
        }
        
        // Rejection
        if (preg_match('/^(no|nope|nah|hindi|mali)$/i', $lowerMessage)) {
            return 'confirm_no';
        }
        
        // Uncertainty
        $uncertainPhrases = [
            'not sure', 'don\'t know', 'dunno', 'idk', 'unsure',
            'maybe', 'skip', 'pass', 'next', 'later', 'flexible'
        ];
        
        foreach ($uncertainPhrases as $phrase) {
            if (stripos($lowerMessage, $phrase) !== false) {
                return 'uncertain';
            }
        }
        
        // Talk to human
        if (preg_match('/(human|agent|person|photographer)/i', $lowerMessage)) {
            return 'talk_to_human';
        }
        
        return 'provide_info';
    }

    // ========================================
    // GENERATE BOT RESPONSE
    // ========================================
    private function generateBotResponse(array $tempBooking, string $intent, array $conversation, string $userMessage = ''): string
    {
        // Handle special intents
        if ($intent === 'talk_to_human') {
            $this->chatRepo->updateConversationType($conversation['conversation_id'], 'direct');
            $this->chatRepo->updateConversationStatus($conversation['conversation_id'], 'pending_worker');
            return "I'll connect you with the photographer now. They'll respond to you shortly! 👋";
        }
        
        // Check if package has been selected
        if (!empty($tempBooking['package_id'])) {
            // Check if user is confirming they're done (no changes, none, no, etc.)
            $confirmationPhrases = ['no', 'none', 'nope', 'no change', 'no changes', 'nothing', 'all good', 'looks good', 'perfect'];
            $lowerMessage = strtolower(trim($userMessage));
            
            foreach ($confirmationPhrases as $phrase) {
                if (stripos($lowerMessage, $phrase) !== false || $intent === 'confirm_no') {
                    // Finalize the booking - switch to direct conversation with photographer
                    $this->chatRepo->updateConversationType($conversation['conversation_id'], 'direct');
                    $this->chatRepo->updateConversationStatus($conversation['conversation_id'], 'pending_worker');
                    
                    return "Great! Your booking request has been sent to the photographer. 🎉\n\n" .
                           "They will review your request and respond to you here in this chat.\n\n" .
                           "You can continue chatting with them directly about any additional details or questions you may have.";
                }
            }
            
            // Still in confirmation phase
            return "Perfect! You've selected a package. 🎉\n\n" .
                   "The photographer will review your booking request and get back to you shortly.\n\n" .
                   "📋 Booking Summary:\n" .
                   "📸 Event: {$tempBooking['event_type']}\n" .
                   "📅 Date: {$tempBooking['event_date']}\n" .
                   "📍 Location: {$tempBooking['event_location']}\n" .
                   "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
                   "Is there anything else you'd like to add or change? (Type 'no' if everything looks good)";
        }
        
        // Determine what information is still needed
        $missingFields = [];
        if (empty($tempBooking['event_type'])) $missingFields[] = 'event_type';
        if (empty($tempBooking['event_date'])) $missingFields[] = 'event_date';
        if (empty($tempBooking['event_location'])) $missingFields[] = 'event_location';
        if (empty($tempBooking['budget'])) $missingFields[] = 'budget';
        
        // If all info collected, show packages
        if (empty($missingFields)) {
            $packages = $this->chatRepo->getSuggestedPackages(
                $conversation['worker_id'],
                $tempBooking['budget']
            );
            
            if (empty($packages)) {
                return "Great! I have all the details:\n\n" .
                       "📸 Event: {$tempBooking['event_type']}\n" .
                       "📅 Date: {$tempBooking['event_date']}\n" .
                       "📍 Location: {$tempBooking['event_location']}\n" .
                       "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
                       "Unfortunately, there are no available packages at the moment. Would you like to speak with the photographer directly?";
            }
            
            $packageList = "\n";
            foreach ($packages as $pkg) {
                $packageList .= "\n📦 {$pkg['name']} - ₱" . number_format($pkg['price'], 2) . "\n";
                $packageList .= "   {$pkg['description']}\n";
            }
            
            return "Perfect! Here's a summary:\n\n" .
                   "📸 Event: {$tempBooking['event_type']}\n" .
                   "📅 Date: {$tempBooking['event_date']}\n" .
                   "📍 Location: {$tempBooking['event_location']}\n" .
                   "💰 Budget: ₱" . number_format($tempBooking['budget'], 2) . "\n\n" .
                   "Here are some recommended packages:" . $packageList . "\n\n" .
                   "Would you like to proceed with one of these packages? Or would you like to speak with the photographer directly?";
        }
        
        // Ask for next missing field
        $nextField = $missingFields[0];
        
        switch ($nextField) {
            case 'event_type':
                return "What type of event are you planning? (e.g., Wedding, Birthday, Portrait, Corporate)";
            
            case 'event_date':
                return "Great! When is your {$tempBooking['event_type']} scheduled? (e.g., December 25, 2025)";
            
            case 'event_location':
                return "Where will the event take place? (e.g., Manila, Quezon City)";
            
            case 'budget':
                return "What's your budget for photography? (e.g., 10000)";
            
            default:
                return "I need a bit more information. Could you tell me about your event?";
        }
    }

    // ========================================
    // POLL FOR NEW MESSAGES
    // ========================================
    public function pollMessages()
    {
        header('Content-Type: application/json');
        
        $user = $_SESSION['user'] ?? null;
        $worker = $_SESSION['worker'] ?? null;
        
        if (!$user && !$worker) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $conversationId = $_GET['conversation_id'] ?? null;
        $lastMessageId = $_GET['last_message_id'] ?? 0;

        if (!$conversationId) {
            echo json_encode(['success' => false, 'error' => 'Missing conversation ID']);
            exit;
        }

        $newMessages = $this->chatRepo->getNewMessages($conversationId, $lastMessageId);

        echo json_encode([
            'success' => true,
            'messages' => $newMessages
        ]);
        exit;
    }

    // ========================================
    // FETCH NEW MESSAGES (for polling)
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
    // CLIENT PROPOSAL ACTIONS
    // ========================================
    
    /**
     * Client accepts photographer's proposal
     */
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
            // Send confirmation message
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
    
    /**
     * Client rejects photographer's proposal
     */
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
            // Send rejection message
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
}