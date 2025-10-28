<?php
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/AuthRepository.php";

class AuthController
{
    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        $this->repo = new AuthRepository();
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
    // CHANGE PASSWORD
    // ========================================
    
    public function changePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Auth&action=login");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $phoneNumber = trim($_POST['phoneNumber'] ?? '');
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $userType = $_POST['userType'] ?? 'user'; // 'user' or 'worker'

        // Validation - Only require email AND phone (both must match)
        if (empty($email) || empty($phoneNumber) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please fill in all required fields.'
            ];
            $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
            header("Location: $redirectUrl");
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Please enter a valid email address.'
            ];
            $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
            header("Location: $redirectUrl");
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'New password and confirmation do not match.'
            ];
            $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
            header("Location: $redirectUrl");
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Password must be at least 6 characters long.'
            ];
            $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
            header("Location: $redirectUrl");
            exit;
        }

        try {
            // Verify BOTH email AND phone match the same account
            $user = $this->repo->verifyEmailAndPhone($email, $phoneNumber, $userType);
            
            if (!$user) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Email and phone number do not match any account or do not belong to the same account.'
                ];
                $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
                header("Location: $redirectUrl");
                exit;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $success = $this->repo->updatePassword($user['id'], $hashedPassword, $userType);

            if ($success) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Password changed successfully! You can now log in with your new password.'
                ];
            } else {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Failed to update password. Please try again.'
                ];
            }

        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'An error occurred while changing password. Please try again.'
            ];
        }

        $redirectUrl = $userType === 'worker' ? 'index.php?controller=Worker&action=login' : 'index.php?controller=Auth&action=login';
        header("Location: $redirectUrl");
        exit;
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