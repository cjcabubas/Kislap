<?php
require_once __DIR__ . '/../model/repositories/UserRepository.php';


class UserController
{
    // ========================================
    // PROFILE MANAGEMENT
    // ========================================
    
    public function profile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            header('Location: index.php?controller=Auth&action=login');
            exit;
        }

        $userRepo = new UserRepository();
        $freshUserData = $userRepo->getUserById($user['user_id']);
        
        if ($freshUserData) {
            $user = $freshUserData;
            $_SESSION['user'] = $user;
        }

        $photoPath = $user['profilePhotoUrl'] ?? '';
        if (!$photoPath || !file_exists($_SERVER['DOCUMENT_ROOT'] . $photoPath)) {
            unset($user['profilePhotoUrl']);
        }




        require "views/user/profile.php";
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    


    private function uploadProfilePhoto(array $file, int $userId): ?string
    {
        require_once __DIR__ . '/../model/Validator.php';
        
        // Debug: Log file info
        error_log("DEBUG: Upload attempt - File name: " . $file['name'] . ", Size: " . $file['size'] . ", Error: " . $file['error']);
        
        $validation = Validator::validateFile($file, 'profile_photo');
        if (!$validation['valid']) {
            error_log("DEBUG: Validation failed: " . $validation['message']);
            throw new Exception('Profile photo validation failed: ' . $validation['message']);
        }

        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/Kislap/uploads/user/profile_photos/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Check if directory is writable
        if (!is_writable($targetDir)) {
            throw new Exception('Upload directory is not writable');
        }

        $secureFilename = Validator::generateSecureFilename($file['name'], "profile_{$userId}_");
        $targetPath = $targetDir . $secureFilename;
        $webPath = '/Kislap/uploads/user/profile_photos/' . $secureFilename;

        // Debug: Log paths
        error_log("DEBUG: Target path: " . $targetPath);
        error_log("DEBUG: Web path: " . $webPath);

        // Remove old photo if exists
        if (!empty($_SESSION['user']['profilePhotoUrl'])) {
            $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user']['profilePhotoUrl'];
            $defaultPath = '/Kislap/public/images/user/default-profile.webp';
            
            if (file_exists($oldPhotoPath) && $_SESSION['user']['profilePhotoUrl'] !== $defaultPath) {
                unlink($oldPhotoPath);
                error_log("DEBUG: Removed old photo: " . $oldPhotoPath);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            error_log("DEBUG: Photo uploaded successfully to: " . $targetPath);
            return $webPath;
        }

        error_log("DEBUG: Failed to move uploaded file");
        throw new Exception('Failed to upload profile photo');
    }

    public function updateProfile(): void
    {
        // Set proper content type for JSON response
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            return;
        }

        try {
            $userId = $_SESSION['user']['user_id'];

            $firstName = $_POST['firstName'] ?? '';
            $middleName = $_POST['middleName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $phoneNumber = $_POST['phoneNumber'] ?? '';
            $address = $_POST['address'] ?? '';

            // Basic validation
            if (empty($firstName) || empty($lastName)) {
                throw new Exception('First name and last name are required');
            }

            // Validate name formats
            require_once __DIR__ . '/../model/Validator.php';
            
            $firstNameValidation = Validator::validateName($firstName, 'first name');
            if (!$firstNameValidation['valid']) {
                throw new Exception($firstNameValidation['message']);
            }
            
            $lastNameValidation = Validator::validateName($lastName, 'last name');
            if (!$lastNameValidation['valid']) {
                throw new Exception($lastNameValidation['message']);
            }
            
            // Validate middle name if provided
            if (!empty($middleName)) {
                $middleNameValidation = Validator::validateName($middleName, 'middle name');
                if (!$middleNameValidation['valid']) {
                    throw new Exception($middleNameValidation['message']);
                }
            }

            // Validate phone number
            $phoneValidation = Validator::validatePhoneNumber($phoneNumber);
            if (!$phoneValidation['valid']) {
                throw new Exception($phoneValidation['message']);
            }

            $photoPath = null;
            if (!empty($_FILES['profilePhotoUrl']['name'])) {
                try {
                    $photoPath = $this->uploadProfilePhoto($_FILES['profilePhotoUrl'], $userId);
                } catch (Exception $e) {
                    throw new Exception('Photo upload failed: ' . $e->getMessage());
                }
            }

            $repo = new UserRepository();
            $updateData = [
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'phoneNumber' => $phoneNumber,
                'address' => $address,
            ];

            if ($photoPath) {
                $updateData['profilePhotoUrl'] = $photoPath;
            }

            $update = $repo->updateUser($userId, $updateData);

            if ($update) {
                // Update session data
                $_SESSION['user']['firstName'] = $firstName;
                $_SESSION['user']['middleName'] = $middleName;
                $_SESSION['user']['lastName'] = $lastName;
                $_SESSION['user']['phoneNumber'] = $phoneNumber;
                $_SESSION['user']['address'] = $address;
                
                if ($photoPath) {
                    $_SESSION['user']['profilePhotoUrl'] = $photoPath;
                }

                // Debug: Log the photo path
                error_log("DEBUG: Photo path saved: " . ($photoPath ?? 'none'));
                error_log("DEBUG: Session photo URL: " . ($_SESSION['user']['profilePhotoUrl'] ?? 'none'));
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile updated successfully',
                    'photoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null,
                    'photoUploaded' => !empty($photoPath),
                    'debug' => [
                        'photoPath' => $photoPath,
                        'sessionPhotoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null
                    ]
                ]);
            } else {
                throw new Exception('Failed to update profile in database');
            }
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function changePassword(): void
    {
        error_log("DEBUG: changePassword method called");
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            error_log("DEBUG: User not logged in");
            $_SESSION['error'] = 'You must be logged in to change your password.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header("Location: index.php?controller=User&action=profile");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        error_log("DEBUG: POST data - current: " . (!empty($currentPassword) ? 'provided' : 'empty') . 
                  ", new: " . (!empty($newPassword) ? 'provided' : 'empty') . 
                  ", confirm: " . (!empty($confirmPassword) ? 'provided' : 'empty'));

        try {
            // Validate input fields
            if (empty($currentPassword)) {
                throw new Exception('Current password is required.');
            }

            if (empty($newPassword)) {
                throw new Exception('New password is required.');
            }

            if (empty($confirmPassword)) {
                throw new Exception('Password confirmation is required.');
            }

            // Check if new passwords match
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            // Simple password validation - just minimum length
            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters long.');
            }

            // Verify current password and update if valid
            $userRepo = new UserRepository();
            $result = $userRepo->changeUserPassword($userId, $currentPassword, $newPassword);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Password changed successfully!';
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?controller=User&action=profile");
        exit;
    }

    public function contactSupport(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'You must be logged in to contact support.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        // Check if request is POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header("Location: index.php?controller=User&action=profile");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userEmail = $_SESSION['user']['email'];
        $userName = $_SESSION['user']['firstName'] . ' ' . $_SESSION['user']['lastName'];
        
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';

        try {
            // Validate input fields
            if (empty($subject)) {
                throw new Exception('Please select a subject for your support request.');
            }

            if (empty($message) || strlen(trim($message)) < 10) {
                throw new Exception('Please provide a detailed message (at least 10 characters).');
            }

            // Sanitize input
            require_once __DIR__ . '/../model/Validator.php';
            $subject = Validator::sanitizeInput($subject);
            $message = Validator::sanitizeInput($message);
            $priority = Validator::sanitizeInput($priority);

            // Send email to support team
            $supportEmailSent = $this->sendSupportEmail($userName, $userEmail, $subject, $message, $priority);
            
            // Send confirmation copy to user
            $userEmailSent = $this->sendSupportConfirmationToUser($userName, $userEmail, $subject, $message, $priority);
            
            if ($supportEmailSent && $userEmailSent) {
                $_SESSION['success'] = "Your support request has been sent successfully! You'll receive a confirmation email and we'll get back to you soon.";
            } else if ($supportEmailSent) {
                $_SESSION['success'] = "Your support request has been sent successfully! We'll get back to you soon.";
            } else {
                throw new Exception('Failed to send support request. Please try again.');
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header("Location: index.php?controller=User&action=profile");
        exit;
    }

    /**
     * Send support ticket email to helpdesk
     */
    private function sendSupportEmail(string $userName, string $userEmail, string $subject, string $message, string $priority): bool
    {
        try {
            $helpdeskEmail = 'kislaphelpdesk@gmail.com';
            $emailSubject = "Support Request: {$subject}";
            $emailBody = $this->getSupportEmailTemplate($userName, $userEmail, $subject, $message, $priority);
            
            return $this->sendWithGmailSMTP($helpdeskEmail, $emailSubject, $emailBody);
            
        } catch (Exception $e) {
            error_log("Support email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send support confirmation email to user
     */
    private function sendSupportConfirmationToUser(string $userName, string $userEmail, string $subject, string $message, string $priority): bool
    {
        try {
            $emailSubject = "Support Request Received: {$subject}";
            $emailBody = $this->getUserConfirmationEmailTemplate($userName, $subject, $message, $priority);
            
            return $this->sendWithGmailSMTP($userEmail, $emailSubject, $emailBody);
            
        } catch (Exception $e) {
            error_log("User confirmation email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate user confirmation email template
     */
    private function getUserConfirmationEmailTemplate(string $userName, string $subject, string $message, string $priority): string
    {
        $priorityColor = [
            'low' => '#28a745',
            'medium' => '#ffc107', 
            'high' => '#dc3545'
        ][$priority] ?? '#ffc107';

        $currentDate = date('F j, Y \a\t g:i A');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Support Request Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-section { background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .priority-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; font-weight: bold; text-transform: uppercase; font-size: 12px; background-color: {$priorityColor}; }
                .message-section { background: #e8f4f8; border: 1px solid #007bff; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .label { font-weight: bold; color: #555; }
                .checkmark { color: #28a745; font-size: 48px; text-align: center; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Support Request Received</h1>
                    <p>Kislap Photography Platform</p>
                </div>
                <div class='content'>
                    <div class='checkmark'>✓</div>
                    <p>Hi <strong>{$userName}</strong>,</p>
                    <p>We've successfully received your support request and our team will review it shortly. Here's a copy of what you submitted:</p>
                    
                    <div class='info-section'>
                        <h3>Your Request Details</h3>
                        <p><span class='label'>Subject:</span> {$subject}</p>
                        <p><span class='label'>Priority:</span> 
                            <span class='priority-badge'>{$priority}</span>
                        </p>
                        <p><span class='label'>Submitted:</span> {$currentDate}</p>
                    </div>
                    
                    <div class='message-section'>
                        <h3>Your Message</h3>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    
                    <div class='info-section'>
                        <h3>What Happens Next?</h3>
                        <ul>
                            <li><strong>High Priority:</strong> We'll respond within 4-6 hours</li>
                            <li><strong>Medium Priority:</strong> We'll respond within 12-24 hours</li>
                            <li><strong>Low Priority:</strong> We'll respond within 24-48 hours</li>
                        </ul>
                        <p>Our support team will contact you directly at this email address with updates or questions.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2025 Kislap Photography Platform - Customer Support</p>
                    <p>This is an automated confirmation email. Please do not reply to this message.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Generate support email template
     */
    private function getSupportEmailTemplate(string $userName, string $userEmail, string $subject, string $message, string $priority): string
    {
        $priorityColor = [
            'low' => '#28a745',
            'medium' => '#ffc107', 
            'high' => '#dc3545'
        ];
        
        $priorityIcon = [
            'low' => 'info-circle',
            'medium' => 'exclamation-triangle',
            'high' => 'exclamation-circle'
        ];

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Support Request: {$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-section { background: #fff; border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; }
                .priority-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; font-weight: bold; text-transform: uppercase; font-size: 12px; }
                .message-section { background: #e8f4f8; border: 1px solid #007bff; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .label { font-weight: bold; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 New Support Request</h1>
                    <p>Kislap Photography Platform</p>
                </div>
                <div class='content'>
                    <div class='info-section'>
                        <h3>Request Information</h3>

                        <p><span class='label'>Subject:</span> {$subject}</p>
                        <p><span class='label'>Priority:</span> 
                            <span class='priority-badge' style='background-color: {$priorityColor[$priority]};'>
                                {$priority}
                            </span>
                        </p>
                        <p><span class='label'>Submitted:</span> " . date('F j, Y \a\t g:i A') . "</p>
                    </div>
                    
                    <div class='info-section'>
                        <h3>Customer Information</h3>
                        <p><span class='label'>Name:</span> {$userName}</p>
                        <p><span class='label'>Email:</span> {$userEmail}</p>
                    </div>
                    
                    <div class='message-section'>
                        <h3>Customer Message</h3>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                    
                    <div class='info-section'>
                        <h3>Next Steps</h3>
                        <ul>
                            <li>Review the customer's request and priority level</li>
                            <li>Respond within appropriate timeframe based on priority</li>
                            <li>Update ticket status in the system</li>
                            <li>Follow up with customer if needed</li>
                        </ul>
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2025 Kislap Photography Platform - Customer Support System</p>
                    <p>This ticket was submitted on " . date('F j, Y \a\t g:i A') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Send email using Gmail SMTP
     */
    private function sendWithGmailSMTP(string $to, string $subject, string $message): bool
    {
        try {
            // Gmail SMTP settings
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com';
            $smtpPassword = 'vbvp uokz yyfa hfnf';
            $fromName = 'Kislap Customer Support';
            
            // Create socket connection to Gmail SMTP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            // Send EHLO command and read all response lines
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            // Start TLS encryption
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            
            // Send EHLO again after TLS and read all response lines
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            // Authenticate
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            // Send username (base64 encoded)
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            // Send password (base64 encoded)
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send DATA command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                fclose($socket);
                return false;
            }
            
            // Send email headers and body
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$smtpUsername}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: {$subject}\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";
            
            fputs($socket, $headers . $message . "\r\n.\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }


}