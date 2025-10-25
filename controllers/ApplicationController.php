<?php
require_once __DIR__ . "/../model/Application.php";
require_once __DIR__ . "/../model/repositories/ApplicationRepository.php";

class ApplicationController
{
    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        $this->repo = new ApplicationRepository();
    }

    // ========================================
    // VIEW METHODS
    // ========================================
    
    public function registration(): void
    {
        require "views/application/registration.php";
    }

    // ========================================
    // APPLICATION SUBMISSION
    // ========================================
    
    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/application/registration.php";
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
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            $userData = $validation['data'];
            
            if (!isset($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Resume/CV file is required for application'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            $resumeValidation = Validator::validateFile($_FILES['resume'], 'resume');
            if (!$resumeValidation['valid']) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Resume file error: ' . $resumeValidation['message']
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'At least 2 portfolio images are required for application'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            $imagesValidation = Validator::validateMultipleFiles($_FILES['images'], 'portfolio', 8);
            if (!$imagesValidation['valid']) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Portfolio images error: ' . implode(', ', $imagesValidation['errors'])
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            if (count($imagesValidation['files']) < 2) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'At least 2 valid portfolio images are required'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }

            try {
                $existingApp = $this->repo->findByEmailOrPhone($userData['email'], $userData['phoneNumber']);
                if ($existingApp) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'An application with this email or phone number already exists'
                    ];
                    $_SESSION['form_data'] = $_POST;
                    header("Location: index.php?controller=Application&action=registration");
                    exit;
                }

                $application = new Application(
                    $userData['lastName'],
                    $userData['firstName'],
                    $userData['middleName'],
                    $userData['email'],
                    $userData['phoneNumber'],
                    $userData['address'],
                    password_hash($userData['password'], PASSWORD_BCRYPT)
                );

                $appId = $this->repo->save($application->toArray());

                $resumePath = $this->uploadSecureFile($_FILES['resume'], "resumes", $appId, 'resume');
                $this->repo->saveResume($appId, $resumePath);

                $uploadedPaths = [];
                foreach ($imagesValidation['files'] as $index => $imageFile) {
                    $imagePath = $this->uploadSecureFile($imageFile, 'works', $appId, 'portfolio', $index);
                    $this->repo->saveWorks($appId, $imagePath);
                    $uploadedPaths[] = $imagePath;
                }

                unset($_SESSION['form_data']);

                $_SESSION['notification'] = [
                    'type' => 'success',
                    'message' => 'Application submitted successfully! Resume and ' . count($uploadedPaths) . ' portfolio images uploaded.'
                ];
                header("Location: index.php?controller=Application&action=registration");
                exit;

            } catch (Exception $e) {
                error_log("Application submission error: " . $e->getMessage());
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Failed to submit application. Please try again.'
                ];
                $_SESSION['form_data'] = $_POST;
                header("Location: index.php?controller=Application&action=registration");
                exit;
            }
        }
    }

    // ========================================
    // FILE UPLOAD METHODS
    // ========================================
    
    private function uploadSecureFile(array $file, string $folder, int $appId, string $type, int $index = 0): string
    {
        require_once __DIR__ . '/../model/Validator.php';
        
        $targetDir = "uploads/application/$folder/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $prefix = ($type === 'resume') ? "resume_app_{$appId}_" : "work_{$index}_app_{$appId}_";
        $secureFilename = Validator::generateSecureFilename($file['name'], $prefix);
        $targetPath = $targetDir . $secureFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload file: " . $file['name']);
        }

        chmod($targetPath, 0644);

        return $targetPath;
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

    // ========================================
    // APPLICATION STATUS CHECK
    // ========================================
    
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