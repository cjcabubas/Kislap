<?php
require_once __DIR__ . "/../model/Application.php";
require_once __DIR__ . "/../model/repositories/ApplicationRepository.php";
class ApplicationController
{
    public function __construct() {
        $this->repo = new ApplicationRepository();
    }
    public function registration(): void
    {
        require "views/application/registration.php";
    }

    public function submit(): void {
        // Show the form on GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/application/registration.php";
            return;
        }

        // Handle form submit on POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $application = new Application(
                $_POST['lastName'] ?? null,
                $_POST['firstName'] ?? null,
                $_POST['middleName'] ?? null,
                $_POST['email'] ?? null,
                $_POST['phoneNumber'] ?? null,
                $_POST['address'] ?? null,
                password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT),
            );

            try {
                $appId = $this->repo->save($application->toArray());
                echo "✅ Application submitted successfully!";









            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }
}

