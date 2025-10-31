<?php
require_once __DIR__ . '/../model/repositories/UserRepository.php';

class UserController
{
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

    private function uploadProfilePhoto(array $file, int $userId): ?string
    {
        require_once __DIR__ . '/../model/Validator.php';
        
        $validation = Validator::validateFile($file, 'profile_photo');
        if (!$validation['valid']) {
            throw new Exception('Profile photo validation failed: ' . $validation['message']);
        }

        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/Kislap/uploads/user/profile_photos/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        if (!is_writable($targetDir)) {
            throw new Exception('Upload directory is not writable');
        }

        $secureFilename = Validator::generateSecureFilename($file['name'], "profile_{$userId}_");
        $targetPath = $targetDir . $secureFilename;
        $webPath = '/Kislap/uploads/user/profile_photos/' . $secureFilename;

        if (!empty($_SESSION['user']['profilePhotoUrl'])) {
            $oldPhotoPath = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['user']['profilePhotoUrl'];
            $defaultPath = '/Kislap/public/images/user/default-profile.webp';
            
            if (file_exists($oldPhotoPath) && $_SESSION['user']['profilePhotoUrl'] !== $defaultPath) {
                unlink($oldPhotoPath);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            chmod($targetPath, 0644);
            return $webPath;
        }

        throw new Exception('Failed to upload profile photo');
    }

    public function updateProfile(): void
    {
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

            if (empty($firstName) || empty($lastName)) {
                throw new Exception('First name and last name are required');
            }

            require_once __DIR__ . '/../model/Validator.php';
            
            $firstNameValidation = Validator::validateName($firstName, 'first name');
            if (!$firstNameValidation['valid']) {
                throw new Exception($firstNameValidation['message']);
            }
            
            $lastNameValidation = Validator::validateName($lastName, 'last name');
            if (!$lastNameValidation['valid']) {
                throw new Exception($lastNameValidation['message']);
            }

            if (!empty($middleName)) {
                $middleNameValidation = Validator::validateName($middleName, 'middle name');
                if (!$middleNameValidation['valid']) {
                    throw new Exception($middleNameValidation['message']);
                }
            }

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
                $_SESSION['user']['firstName'] = $firstName;
                $_SESSION['user']['middleName'] = $middleName;
                $_SESSION['user']['lastName'] = $lastName;
                $_SESSION['user']['phoneNumber'] = $phoneNumber;
                $_SESSION['user']['address'] = $address;
                
                if ($photoPath) {
                    $_SESSION['user']['profilePhotoUrl'] = $photoPath;
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile updated successfully',
                    'photoUrl' => $_SESSION['user']['profilePhotoUrl'] ?? null,
                    'photoUploaded' => !empty($photoPath)
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'You must be logged in to change your password.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Invalid request method.';
            header("Location: index.php?controller=User&action=profile");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        try {
            if (empty($currentPassword)) {
                throw new Exception('Current password is required.');
            }

            if (empty($newPassword)) {
                throw new Exception('New password is required.');
            }

            if (empty($confirmPassword)) {
                throw new Exception('Password confirmation is required.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }

            if (strlen($newPassword) < 6) {
                throw new Exception('Password must be at least 6 characters long.');
            }
            
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

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'You must be logged in to contact support.';
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

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
            if (empty($subject)) {
                throw new Exception('Please select a subject for your support request.');
            }

            if (empty($message) || strlen(trim($message)) < 10) {
                throw new Exception('Please provide a detailed message (at least 10 characters).');
            }

            require_once __DIR__ . '/../model/Validator.php';
            $subject = Validator::sanitizeInput($subject);
            $message = Validator::sanitizeInput($message);
            $priority = Validator::sanitizeInput($priority);

            $supportEmailSent = $this->sendSupportEmail($userName, $userEmail, $subject, $message, $priority);
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

    private function sendSupportEmail(string $userName, string $userEmail, string $subject, string $message, string $priority): bool
    {
        try {
            $helpdeskEmail = 'kislaphelpdesk@gmail.com';
            $emailSubject = "Support Request: {$subject}";
            $emailBody = $this->getSupportEmailTemplate($userName, $userEmail, $subject, $message, $priority);
            
            return $this->sendWithGmailSMTP($helpdeskEmail, $emailSubject, $emailBody);
            
        } catch (Exception $e) {
            return false;
        }
    }

    private function sendSupportConfirmationToUser(string $userName, string $userEmail, string $subject, string $message, string $priority): bool
    {
        try {
            $emailSubject = "Support Request Received: {$subject}";
            $emailBody = $this->getUserConfirmationEmailTemplate($userName, $subject, $message, $priority);
            
            return $this->sendWithGmailSMTP($userEmail, $emailSubject, $emailBody);
            
        } catch (Exception $e) {
            return false;
        }
    }


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


    private function sendWithGmailSMTP(string $to, string $subject, string $message): bool
    {
        try {
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com';
            $smtpPassword = 'vbvp uokz yyfa hfnf';
            $fromName = 'Kislap Customer Support';
            
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                return false;
            }
            
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                fclose($socket);
                return false;
            }
            
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            
            fputs($socket, "EHLO localhost\r\n");
            do {
                $response = fgets($socket, 515);
            } while (substr($response, 0, 4) === '250-');
            
            fputs($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                fclose($socket);
                return false;
            }
            
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                fclose($socket);
                return false;
            }
            
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
            
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }


}