<?php
require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/AuthRepository.php";

class AuthController
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
            require "views/application/signUp.php";
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

            try {
                // Check for duplicates
                $existingUser = $this->repo->findByEmailOrPhone($user->getEmail(), $user->getPhoneNumber());
                if ($existingUser) {
                    echo "❌ Account with that email or phone number already exists.";
                    return;
                }

                // Save main info
                $this->repo->signUp($user->toArray());
                echo "✅ Account created successfully!";

            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }

    public function loginDB(): void
    {
        // Show the form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/application/login.php";
            return;
        }

        // Handle form submit on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? null;
            $password = $_POST['password'] ?? null;

            try {
                if (strpos($identifier, '@') !== false) {
                    $user = $this->repo->findByEmail($identifier);
                } else {
                    $user = $this->repo->findByphoneNumber($identifier);
                }

                if (!$user) {
                    echo "No user with that email or phone number found.";
                }

                if (!password_verify($password, $user['password'])) {
                    echo "Wrong password.";
                    return;
                }
                unset($user->password);

                $_SESSION['user'] = [
                    'user_id'        => $user['user_id'],
                    'lastName'       => $user['lastName'],
                    'firstName'      => $user['firstName'],
                    'middleName'     => $user['middleName'],
                    'email'          => $user['email'],
                    'phoneNumber'    => $user['phoneNumber'],
                    'address'        => $user['address'],
                    'profilePhotoUrl'=> $user['profilePhotoUrl'],
                    'createdAt'      => $user['createdAt']

                ];
                echo "✅ Logged in successfully!" . $user['firstName'];
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }
}