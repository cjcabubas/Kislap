<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/WorkerRepository.php";

class WorkerController
{

    public function __construct()
    {
        $this->repo = new WorkerRepository();
    }

    public function login(): void
    {
        require "views/worker/login.php";
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
                    'worker_id' => $worker['worker_id'],
                    'application_id' => $worker['application_id'],
                    'lastName' => $worker['lastName'],
                    'firstName' => $worker['firstName'],
                    'middleName' => $worker['middleName'],
                    'email' => $worker['email'],
                    'phoneNumber' => $worker['phoneNumber'],
                    'address' => $worker['address'],
                    'specialty' => $worker['specialty'],
                    'experience_years' => $worker['experience_years'],
                    'bio' => $worker['bio'],
                    'profile_photo' => $worker['profile_photo'],
                    'rating_average' => $worker['rating_average'],
                    'total_ratings' => $worker['total_ratings'],
                    'total_bookings' => $worker['total_bookings'],
                    'total_earnings' => $worker['total_earnings'],
                    'status' => $worker['status'],
                    'created_at' => $worker['created_at'],
                    'role' => 'worker'

                ];
                header("Location: index.php?controller=Home&action=homePage");
                exit;
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }

    public function profile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        // Get fresh data from database
        $workerId = $worker['worker_id'];
        $workerData = $this->repo->getWorkerById($workerId);

        if (!$workerData) {
            echo "Worker not found.";
            exit;
        }

        // Merge with session data (use DB as primary source)
        $worker = array_merge($worker, $workerData);

        // Get portfolio images
        $existingPortfolio = $this->repo->getWorkerPortfolio($workerId);


        // Check if we're in edit mode
        $isEditMode = isset($_GET['edit']) && $_GET['edit'] === 'true';

        require __DIR__ . '/../views/worker/profile.php';
    }

    public function updateProfile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Worker&action=profile");
            exit;
        }

        $workerId = $worker['worker_id'];

        try {
            // Prepare profile data
            $profileData = [
                'firstName' => trim($_POST['firstName'] ?? ''),
                'middleName' => trim($_POST['middleName'] ?? ''),
                'lastName' => trim($_POST['lastName'] ?? ''),
                'phoneNumber' => trim($_POST['phoneNumber'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'specialty' => trim($_POST['specialty'] ?? ''),
                'experience_years' => (int)($_POST['experience_years'] ?? 0),
                'bio' => trim($_POST['bio'] ?? '')
            ];

            // Validate required fields
            if (empty($profileData['firstName']) || empty($profileData['lastName']) ||
                empty($profileData['phoneNumber']) || empty($profileData['address']) ||
                empty($profileData['specialty']) || empty($profileData['bio'])) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                header("Location: index.php?controller=Worker&action=profile&edit=true");
                exit;
            }

            // Handle profile photo upload
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/workers/{$workerId}/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
                $fileName = "worker{$workerId}_profile_photo." . $ext;
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
                    $profileData['profile_photo'] = $targetFile;
                } else {
                    $_SESSION['warning'] = 'Failed to upload profile photo.';
                }
            }


            // Update basic profile
            $this->repo->updateWorkerProfile($workerId, $profileData);

            // Handle password change
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if (!empty($currentPassword) && !empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $_SESSION['error'] = 'New passwords do not match.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                if (strlen($newPassword) < 6) {
                    $_SESSION['error'] = 'Password must be at least 6 characters long.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                // Verify current password
                $workerData = $this->repo->getWorkerById($workerId);
                if (!password_verify($currentPassword, $workerData['password'])) {
                    $_SESSION['error'] = 'Current password is incorrect.';
                    header("Location: index.php?controller=Worker&action=profile&edit=true");
                    exit;
                }

                // Update password
                $this->repo->updateWorkerPassword($workerId, $newPassword);
            }

            // Handle portfolio images upload
            if (isset($_FILES['portfolio_images']) && !empty($_FILES['portfolio_images']['tmp_name'][0])) {
                $this->handlePortfolioUpload($_FILES['portfolio_images'], $workerId);
            }

            // Update session data
            $_SESSION['worker']['firstName'] = $profileData['firstName'];
            $_SESSION['worker']['middleName'] = $profileData['middleName'];
            $_SESSION['worker']['lastName'] = $profileData['lastName'];
            $_SESSION['worker']['phoneNumber'] = $profileData['phoneNumber'];
            $_SESSION['worker']['address'] = $profileData['address'];
            $_SESSION['worker']['specialty'] = $profileData['specialty'];
            $_SESSION['worker']['experience_years'] = $profileData['experience_years'];
            $_SESSION['worker']['bio'] = $profileData['bio'];

            if (isset($profileData['profile_photo'])) {
                $_SESSION['worker']['profile_photo'] = $profileData['profile_photo'];
            }

            $_SESSION['success'] = 'Profile updated successfully!';
            header("Location: index.php?controller=Worker&action=profile");
            exit;

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
            header("Location: index.php?controller=Worker&action=profile&edit=true");
            exit;
        }
    }

    private function handlePortfolioUpload(array $files, int $workerId): void
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/avif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $maxImages = 8;

        // Get current portfolio count
        $currentCount = $this->repo->getWorkerPortfolioCount($workerId);

        $uploadDir = "uploads/workers/{$workerId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedCount = 0;

        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            if ($currentCount + $uploadedCount >= $maxImages) {
                $_SESSION['warning'] = "Maximum of {$maxImages} portfolio images allowed.";
                break;
            }

            if (!in_array($files['type'][$key], $allowedTypes)) {
                continue;
            }

            if ($files['size'][$key] > $maxSize) {
                continue;
            }

            $extension = pathinfo($files['name'][$key], PATHINFO_EXTENSION);
            $filename = "worker" . $workerId . "_" . 'work'. uniqid() . "." . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $dbPath = $uploadDir . $filename;
                $this->repo->insertWorkerWork($workerId, $dbPath);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            if (!isset($_SESSION['success'])) {
                $_SESSION['success'] = "{$uploadedCount} portfolio image(s) uploaded successfully!";
            }
        }
    }

    public function removePortfolioImage(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $workId = (int)($data['work_id'] ?? 0);
        $workerId = $worker['worker_id'];

        if (!$workId) {
            echo json_encode(['success' => false, 'message' => 'Invalid work ID']);
            exit;
        }

        try {
            // Get image path before deleting
            $work = $this->repo->getWorkerWorkById($workId, $workerId);

            if (!$work) {
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }

            // Delete from database
            $this->repo->deleteWorkerWork($workId, $workerId);

            // Delete physical file
            $filePath = $work['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
        } catch (Exception $e) {
            error_log("Remove portfolio error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to remove image']);
        }
    }

}