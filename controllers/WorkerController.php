<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/WorkerRepository.php";

class WorkerController
{

    public function __construct()
    {
        $this->repo = new AuthRepository();
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
                $worker = $this->repo->findByEmail($identifier);
            } else {
                $worker = $this->repo->findByphoneNumber($identifier);
            }

            if (!$worker) {
                echo "No user with that email or phone number found.";
                return;
            }

            if (!password_verify($password, $worker['password'])) {
                echo "Wrong password.";
                return;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            unset($worker['password']);

            $_SESSION['worker'] = [
                'worker_id'        => $worker['worker_id'],
                'lastName'       => $worker['lastName'],
                'firstName'      => $worker['firstName'],
                'middleName'     => $worker['middleName'],
                'email'          => $worker['email'],
                'phoneNumber'    => $worker['phoneNumber'],
                'address'        => $worker['address'],
                'profilePhotoUrl'=> $worker['profilePhotoUrl'],
                'createdAt'      => $worker['createdAt'],
                'role'           => 'user'

            ];
            header("Location: index.php?controller=Home&action=homePage");
            exit;
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}

}