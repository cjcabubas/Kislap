<?php
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/AuthRepository.php";
require_once __DIR__ . "/../model/repositories/OTPRepository.php";

class AuthController
{
    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        $this->repo = new AuthRepository();
        $this->otpRepo = new OTPRepository();
    }

    // ========================================
    // VIEW METHODS
    // ========================================
    
    public function signUp(): void
    {
        require "views/user/signup.php";
    }

    public function login(): void
    {
        require "views/user/login.php";
    }

    // ========================================
    // USER REGISTRATION
    // ========================================
    
    public function signUpDB(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/user/signup.php";
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Validator.php';
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $validation = Validator::validateUserRegistration($_POST);
            
            if (!$validation['valid']) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Please fix the following errors: ' . implode(', ', $validation['errors'])
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Auth&action=signUp");
                exit;
            }

            $userData = $validation['data'];
            
            try {
                $existingUser = $this->repo->findByEmailOrPhone($userData['email'], $userData['phoneNumber']);
                if ($existingUser) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Account with that email or phone number already exists.'
                    ];
                    $_SESSION['form_data'] = $_POST;
                    header("Location: index.php?controller=Auth&action=signUp");
                    exit;
                }

                $userData['password'] = password_hash($userData['password'], PASSWORD_BCRYPT);
                
                $user = new User(
                    $userData['lastName'],
                    $userData['firstName'],
                    $userData['middleName'],
                    $userData['email'],
                    $userData['phoneNumber'],
                    $userData['password'],
                    $userData['address']
                );

                $this->repo->signUp($user->toArray());
                
                unset($_SESSION['form_data']);
                
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Account created successfully! You can now log in.'
                ];
                header("Location: index.php?controller=Auth&action=login");
                exit;

            } catch (Exception $e) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error creating account: ' . $e->getMessage()
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Auth&action=signUp");
                exit;
            }
        }
    }

    // ========================================
    // USER LOGIN
    // ========================================
    
    public function loginDB(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/user/login.php";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/../model/Validator.php';
            
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (empty($identifier) || empty($password)) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Please enter both email/phone and password.'
                ];
                header("Location: index.php?controller=Auth&action=login");
                exit;
            }

            $isEmail = strpos($identifier, '@') !== false;
            if ($isEmail) {
                $emailValidation = Validator::validateEmail($identifier);
                if (!$emailValidation['valid']) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => $emailValidation['message']
                    ];
                    header("Location: index.php?controller=Auth&action=login");
                    exit;
                }
            } else {
                $phoneValidation = Validator::validatePhoneNumber($identifier);
                if (!$phoneValidation['valid']) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => $phoneValidation['message']
                    ];
                    header("Location: index.php?controller=Auth&action=login");
                    exit;
                }
            }

            try {
                if ($isEmail) {
                    $user = $this->repo->findByEmail(strtolower($identifier));
                } else {
                    $user = $this->repo->findByphoneNumber($identifier);
                }

                if (!$user) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'No account found with that email or phone number.'
                    ];
                    header("Location: index.php?controller=Auth&action=login");
                    exit;
                }

                if (!password_verify($password, $user['password'])) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Incorrect password. Please try again.'
                    ];
                    header("Location: index.php?controller=Auth&action=login");
                    exit;
                }

                unset($user['password']);

                $_SESSION['user'] = [
                    'user_id'        => $user['user_id'],
                    'lastName'       => $user['lastName'],
                    'firstName'      => $user['firstName'],
                    'middleName'     => $user['middleName'],
                    'email'          => $user['email'],
                    'phoneNumber'    => $user['phoneNumber'],
                    'address'        => $user['address'],
                    'profilePhotoUrl'=> $user['profilePhotoUrl'],
                    'createdAt'      => $user['createdAt'],
                    'role'           => 'user'
                ];
                
                header("Location: index.php?controller=Home&action=homePage");
                exit;
                
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Login failed. Please try again.'
                ];
                header("Location: index.php?controller=Auth&action=login");
                exit;
            }
        }
    }



    // ========================================
    // FORGOT PASSWORD WITH OTP
    // ========================================
    
    public function forgotPassword(): void
    {
        require "views/user/forgot-password.php";
    }

    public function sendOTP(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $userType = $_POST['userType'] ?? 'user';

        if (empty($email)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please enter your email address.'
            ];
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please enter a valid email address.'
            ];
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        try {
            // Check if email exists
            $user = $this->otpRepo->emailExists($email, $userType);
            
            if (!$user) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'No account found with that email address.'
                ];
                header("Location: index.php?controller=Auth&action=forgotPassword");
                exit;
            }

            // Generate and store OTP
            $otpCode = $this->generateOTP();
            $otpStored = $this->otpRepo->storeOTP($email, $otpCode, $userType);

            if (!$otpStored) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Failed to generate OTP. Please try again.'
                ];
                header("Location: index.php?controller=Auth&action=forgotPassword");
                exit;
            }

            // Send OTP email
            $userName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
            $emailSent = $this->sendOTPEmail($email, $otpCode, $userName);

            if ($emailSent) {
                $_SESSION['otp_email'] = $email;
                $_SESSION['otp_user_type'] = $userType;
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'OTP sent to your email address. Please check your inbox.'
                ];
                header("Location: index.php?controller=Auth&action=verifyOTP");
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Failed to send OTP email. Please try again.'
                ];
                header("Location: index.php?controller=Auth&action=forgotPassword");
            }
            exit;

        } catch (Exception $e) {
            error_log("Send OTP error: " . $e->getMessage());
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'An error occurred. Please try again.'
            ];
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }
    }

    public function verifyOTP(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if we have email in session
        if (empty($_SESSION['otp_email'])) {
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        require "views/user/verify-otp.php";
    }

    public function verifyOTPCode(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_SESSION['otp_email'] ?? '';
        $userType = $_SESSION['otp_user_type'] ?? 'user';
        $otpCode = trim($_POST['otp_code'] ?? '');

        if (empty($email) || empty($otpCode)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please enter the OTP code.'
            ];
            header("Location: index.php?controller=Auth&action=verifyOTP");
            exit;
        }

        try {
            $isValid = $this->otpRepo->verifyOTP($email, $otpCode, $userType);

            if ($isValid) {
                $_SESSION['otp_verified'] = true;
                $_SESSION['otp_verified_time'] = time(); // Add timestamp
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'OTP verified successfully. Please set your new password.'
                ];
                header("Location: index.php?controller=Auth&action=resetPassword");
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Invalid or expired OTP code. Please try again.'
                ];
                header("Location: index.php?controller=Auth&action=verifyOTP");
            }
            exit;

        } catch (Exception $e) {
            error_log("Verify OTP error: " . $e->getMessage());
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'An error occurred. Please try again.'
            ];
            header("Location: index.php?controller=Auth&action=verifyOTP");
            exit;
        }
    }

    public function resetPassword(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if OTP was verified with extended timeout (30 minutes)
        $otpVerifiedTime = $_SESSION['otp_verified_time'] ?? 0;
        $timeoutDuration = 30 * 60; // 30 minutes in seconds
        
        if (empty($_SESSION['otp_verified']) || empty($_SESSION['otp_email']) || (time() - $otpVerifiedTime) > $timeoutDuration) {
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        require "views/user/reset-password.php";
    }

    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_SESSION['otp_email'] ?? '';
        $userType = $_SESSION['otp_user_type'] ?? 'user';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify session state with extended timeout (30 minutes)
        $otpVerifiedTime = $_SESSION['otp_verified_time'] ?? 0;
        $timeoutDuration = 30 * 60; // 30 minutes in seconds
        
        if (empty($_SESSION['otp_verified']) || empty($email) || (time() - $otpVerifiedTime) > $timeoutDuration) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Session expired. Please start the password reset process again.'
            ];
            header("Location: index.php?controller=Auth&action=forgotPassword");
            exit;
        }

        // Validate passwords
        if (empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please fill in both password fields.'
            ];
            header("Location: index.php?controller=Auth&action=resetPassword");
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Passwords do not match.'
            ];
            header("Location: index.php?controller=Auth&action=resetPassword");
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Password must be at least 6 characters long.'
            ];
            header("Location: index.php?controller=Auth&action=resetPassword");
            exit;
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $success = $this->otpRepo->updatePasswordByEmail($email, $hashedPassword, $userType);

            if ($success) {
                // Clear session data
                unset($_SESSION['otp_email']);
                unset($_SESSION['otp_user_type']);
                unset($_SESSION['otp_verified']);
                unset($_SESSION['otp_verified_time']);

                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Password reset successfully! You can now log in with your new password.'
                ];
                
                $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
                header("Location: $redirectUrl");
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Failed to update password. Please try again.'
                ];
                header("Location: index.php?controller=Auth&action=resetPassword");
            }
            exit;

        } catch (Exception $e) {
            error_log("Update password error: " . $e->getMessage());
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'An error occurred. Please try again.'
            ];
            header("Location: index.php?controller=Auth&action=resetPassword");
            exit;
        }
    }

    // ========================================
    // EMAIL METHODS
    // ========================================
    
    /**
     * Generate a 6-digit OTP code
     */
    private function generateOTP(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP email for password reset
     */
    private function sendOTPEmail(string $toEmail, string $otpCode, string $userName = ''): bool
    {
        try {
            $subject = 'Password Reset OTP - Kislap';
            $message = $this->getOTPEmailTemplate($otpCode, $userName);
            
            return $this->sendEmail($toEmail, $subject, $message);
        } catch (Exception $e) {
            error_log("Failed to send OTP email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using SMTP or basic mail function
     */
    private function sendEmail(string $to, string $subject, string $message): bool
    {
        // For development/testing, log emails instead of sending
        if ($this->isTestMode()) {
            return $this->logEmail($to, $subject, $message);
        }

        // Production email settings
        $fromEmail = 'kislaphelpdesk@gmail.com';
        $fromName = 'Kislap Photography Platform';
        
        // Log email attempt
        error_log("Attempting to send email to: $to, Subject: $subject");
        
        // Use Gmail SMTP
        $result = $this->sendWithGmailSMTP($to, $subject, $message, $fromEmail, $fromName);
        
        if ($result) {
            error_log("Email sent successfully to: $to");
        } else {
            error_log("Failed to send email to: $to");
        }
        
        return $result;
    }

    /**
     * Send email using Gmail SMTP
     */
    private function sendWithGmailSMTP(string $to, string $subject, string $message, string $fromEmail, string $fromName): bool
    {
        try {
            // Gmail SMTP settings
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpUsername = 'kislaphelpdesk@gmail.com'; // Gmail address
            $smtpPassword = 'vbvp uokz yyfa hfnf';      // Gmail App Password
            
            // Create socket connection to Gmail SMTP
            $socket = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
            if (!$socket) {
                error_log("SMTP connection failed: $errstr ($errno)");
                return false;
            }
            
            // Read initial response
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("SMTP initial response failed: $response");
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
                error_log("STARTTLS failed: $response");
                fclose($socket);
                return false;
            }
            
            // Enable crypto
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                error_log("Failed to enable TLS encryption");
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
                error_log("AUTH LOGIN failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send username (base64 encoded)
            fputs($socket, base64_encode($smtpUsername) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '334') {
                error_log("Username authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send password (base64 encoded)
            fputs($socket, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '235') {
                error_log("Password authentication failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send MAIL FROM
            fputs($socket, "MAIL FROM: <$smtpUsername>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("MAIL FROM failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send RCPT TO
            fputs($socket, "RCPT TO: <$to>\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("RCPT TO failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send DATA command
            fputs($socket, "DATA\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '354') {
                error_log("DATA command failed: $response");
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
                error_log("Email sending failed: $response");
                fclose($socket);
                return false;
            }
            
            // Send QUIT
            fputs($socket, "QUIT\r\n");
            fclose($socket);
            
            // Log successful email
            $this->logEmail($to, $subject, "Email sent successfully via Gmail SMTP");
            
            return true;
            
        } catch (Exception $e) {
            error_log("Gmail SMTP error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using basic mail function (fallback)
     */
    private function sendWithBasicMail(string $to, string $subject, string $message, string $fromEmail, string $fromName): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion()
        ];

        $headersString = implode("\r\n", $headers);
        return mail($to, $subject, $message, $headersString);
    }

    /**
     * Generate OTP email template
     */
    private function getOTPEmailTemplate(string $otpCode, string $userName): string
    {
        $greeting = !empty($userName) ? "Hello {$userName}," : "Hello,";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Password Reset OTP</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ff6b00, #ff8533); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: #fff; border: 2px solid #ff6b00; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
                .otp-number { font-size: 32px; font-weight: bold; color: #ff6b00; letter-spacing: 5px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                    <p>Kislap Photography Platform</p>
                </div>
                <div class='content'>
                    <p>{$greeting}</p>
                    <p>We received a request to reset your password. Use the OTP code below to proceed with your password reset:</p>
                    
                    <div class='otp-code'>
                        <p style='margin: 0; font-size: 16px;'>Your OTP Code:</p>
                        <div class='otp-number'>{$otpCode}</div>
                        <p style='margin: 0; font-size: 14px; color: #666;'>Valid for 10 minutes</p>
                    </div>
                    
                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong>
                        <ul style='margin: 10px 0 0 0;'>
                            <li>This OTP is valid for 10 minutes only</li>
                            <li>Do not share this code with anyone</li>
                            <li>If you didn't request this, please ignore this email</li>
                        </ul>
                    </div>
                    
                    <p>If you have any questions or need assistance, please contact our support team.</p>
                </div>
                <div class='footer'>
                    <p>© 2025 Kislap Photography Platform. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Check if we're in test mode (for development)
     */
    private function isTestMode(): bool
    {
        return false; // Production mode - send real emails
    }

    /**
     * Log email instead of sending (for development)
     */
    private function logEmail(string $to, string $subject, string $message): bool
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'message' => strip_tags($message)
        ];
        
        $logFile = __DIR__ . '/../logs/emails.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        
        return true;
    }

    // ========================================
    // LOGOUT
    // ========================================
    
    public function logout() {
        session_start();
        session_unset();
        session_destroy();

        header("Location: index.php?controller=Home&action=homePage");
        exit;
    }
}