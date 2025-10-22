<?php
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/AuthRepository.php";

class   AuthController
{
    public function __construct()
    {
        $this->repo = new AuthRepository();
    }

    public function signUp(): void
    {
        require "views/user/signup.php";
    }

    public function login(): void
    {
        require "views/user/login.php";
    }

    public function signUpDB(): void
    {
        // Show the form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/user/signup.php";
            return;
        }

        // Handle form submit on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = new User(
                $_POST['lastName'] ?? null,
                $_POST['firstName'] ?? null,
                $_POST['middleName'] ?? null,
                $_POST['email'] ?? null,
                $_POST['phoneNumber'] ?? null,
                password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT),
                $_POST['address'] ?? null
            );

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                // Check for duplicates
                $existingUser = $this->repo->findByEmailOrPhone($user->getEmail(), $user->getPhoneNumber());
                if ($existingUser) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Account with that email or phone number already exists.'
                    ];
                    header("Location: index.php?controller=Auth&action=signUp");
                    exit;
                }

                // Save main info
                $this->repo->signUp($user->toArray());
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Account created successfully!'
                ];
                header("Location: index.php?controller=Auth&action=login");
                exit;

            } catch (Exception $e) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                header("Location: index.php?controller=Auth&action=signUp");
                exit;
            }
        }
    }

    public function loginDB(): void
    {
        // Show the form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/user/login.php";
            return;
        }

        // Handle form submit on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? null;
            $password = $_POST['password'] ?? null;

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (strpos($identifier, '@') !== false) {
                    $user = $this->repo->findByEmail($identifier);
                } else {
                    $user = $this->repo->findByphoneNumber($identifier);
                }

                if (!$user) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'No user with that email or phone number found.'
                    ];
                    header("Location: index.php?controller=Auth&action=login");
                    exit;
                }

                if (!password_verify($password, $user['password'])) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Wrong password.'
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
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                header("Location: index.php?controller=Auth&action=login");
                exit;
            }
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();

        header("Location: index.php?controller=Home&action=homePage");
        exit;
    }
}