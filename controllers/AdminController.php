<?php
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/repositories/AdminRepository.php';

class AdminController
{
    private AdminRepository $repo;

    public function __construct()
    {
        $this->repo = new AdminRepository();
    }

    // Show login form
    public function login(): void
    {
        require __DIR__ . '/../views/admin/login.php';
    }

    // Handle login
    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                echo "Username and password are required.";
                return;
            }

            try {
                $admin = $this->repo->findByUsername($username);

                if (!$admin) {
                    echo "No admin account found.";
                    return;
                }

                if (!password_verify($password, $admin['password'])) {
                    echo "Incorrect password.";
                    return;
                }

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                unset($admin['password']); // never store plain password

                $_SESSION['admin'] = [
                    'admin_id'   => $admin['admin_id'],
                    'username'   => $admin['username'],
                    'firstName'  => $admin['firstName'],
                    'middleName' => $admin['middleName'],
                    'lastName'   => $admin['lastName'],
                    'createdAt'  => $admin['created_at']
                ];

                header("Location: /Kislap/views/admin/dashboard.php");
                exit;

            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            $this->login();
        }
    }

    // Logout
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /Kislap/views/admin/login.php");
        exit;
    }
}
