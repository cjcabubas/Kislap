<?php
require_once __DIR__ . "/../model/Application.php";
require_once __DIR__ . "/../model/repositories/ApplicationRepository.php";

class ApplicationController
{
    public function __construct()
    {
        $this->repo = new ApplicationRepository();
    }

    public function registration(): void
    {
        require "views/application/registration.php";
    }

    public function submit(): void
    {
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

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                // save main info
                $appId = $this->repo->save($application->toArray());

                // save resume (single file)
                if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                    $filePaths = $this->uploadFiles($_FILES['resume'], "resumes", $appId, true);
                    $this->repo->saveResume($appId, $filePaths[0]); // only one resume file
                }

                // save works (multiple files)
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $filePaths = $this->uploadFiles($_FILES['images'], 'works', $appId, false);

                    foreach ($filePaths as $path) {
                        $this->repo->saveWorks($appId, $path);
                    }
                }

                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Application submitted successfully! All files uploaded.'
                ];
                header("Location: index.php?controller=Application&action=registration");
                exit;

            } catch (Exception $e) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }
        }
    }

    public function uploadFiles(array $files, string $folder, int $appId, bool $isResume): array
    {
        $uploadedFiles = [];
        $targetDir = "uploads/application/$folder/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        if ($isResume) {
            $extension = pathinfo($files["name"], PATHINFO_EXTENSION);
            $filename = "resume_application_number_" . $appId . "_" . $extension;
            move_uploaded_file($files['tmp_name'], $targetDir . $filename);
            $uploadedFiles[] = $targetDir . $filename;
        } else {
            // Multiple works
            $count = count($files['name']);
            for ($i = 0; $i < $count && $i < 4; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                    $filename = "work_" . $i . "_app_" . $appId . "." . $extension;
                    move_uploaded_file($files['tmp_name'][$i], $targetDir . $filename);
                    $uploadedFiles[] = $targetDir . $filename;
                }
            }
        }
        return $uploadedFiles;
    }

    public function checkStatus(): void {

        require_once __DIR__ . "/../views/application/checkstatus.php";

    }

    public function submitCheckStatus() {
        $result = null;
        $errorMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $identifier = $_POST['identifier'] ?? '';

            $result = $this->repo->findByEmailAndIdentifier($email, $identifier);

            if ($result) {
                $result['status'] = strtoupper($result['status']);
            } else {
                $errorMessage = "No application found with that information.";
            }
        }

        include 'views/application/checkstatus.php';
    }



}