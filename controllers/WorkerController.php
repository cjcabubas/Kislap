<?php

require_once __DIR__ . "/../model/User.php";
require_once __DIR__ . "/../model/repositories/WorkerRepository.php";

class WorkerController
{
    private $repo;

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
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require "views/worker/login.php";
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? null;
            $password = $_POST['password'] ?? null;

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            try {
                if (strpos($identifier, '@') !== false) {
                    $worker = $this->repo->findByEmail($identifier);
                } else {
                    $worker = $this->repo->findByphoneNumber($identifier);
                }

                if (!$worker) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'No user with that email or phone number found.'
                    ];
                    header("Location: index.php?controller=Worker&action=login");
                    exit;
                }

                if (!password_verify($password, $worker['password'])) {
                    $_SESSION['notification'] = [
                        'type' => 'error',
                        'message' => 'Wrong password.'
                    ];
                    header("Location: index.php?controller=Worker&action=login");
                    exit;
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
                header("Location: index.php?controller=Worker&action=dashboard");
                exit;
            } catch (Exception $e) {
                $_SESSION['notification'] = [
                    'type' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                ];
                header("Location: index.php?controller=Worker&action=login");
                exit;
            }
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: /Kislap/index.php?controller=Home&action=homePage");
        exit;
    }

    public function dashboard(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $worker = $_SESSION['worker'] ?? null;
        if (!$worker) {
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        
        $workerId = $worker['worker_id'];
        
        $workerStats = $this->getWorkerStatistics($workerId);
        $detailedStats = $chatRepo->getWorkerBookingStats($workerId);
        $recentBookings = $chatRepo->getWorkerBookings($workerId, null, 5);
        
        $stats = array_merge($detailedStats, [
            'total_bookings' => $workerStats['total_bookings'],
            'avg_booking_value' => $workerStats['total_bookings'] > 0 ? 
                ($workerStats['total_earnings'] / $workerStats['total_bookings']) : 0
        ]);
        
        $earningsData = [
            'total_earnings' => $workerStats['total_earnings'],
            'rating_average' => $workerStats['rating_average'],
            'total_ratings' => $workerStats['total_ratings']
        ];
        
        require __DIR__ . '/../views/worker/dashboard.php';
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

        $workerId = $worker['worker_id'];
        $workerData = $this->repo->getWorkerById($workerId);

        if (!$workerData) {
            echo "Worker not found.";
            exit;
        }

        $workerStats = $this->getWorkerStatistics($workerId);
        $worker = array_merge($worker, $workerData, $workerStats);
        $_SESSION['worker'] = array_merge($_SESSION['worker'], $workerStats);
        
        $existingPortfolio = $this->repo->getWorkerPortfolio($workerId);
        $existingPackages = $this->repo->getWorkerPackages($workerId);
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
            $_SESSION['error'] = 'Session expired. Please log in again.';
            header("Location: index.php?controller=Worker&action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=Worker&action=profile");
            exit;
        }

        $workerId = $worker['worker_id'];
        $messages = [];

        try {
            // 1. UPDATE BASIC PROFILE DATA
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

            // 2. HANDLE PROFILE PHOTO UPLOAD
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = "uploads/workers/{$workerId}/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = $_FILES['profile_photo']['name'];
                $tmpName = $_FILES['profile_photo']['tmp_name'];
                $fileSize = $_FILES['profile_photo']['size'];
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($tmpName);
                
                if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $secureFilename = "worker{$workerId}_profile_" . time() . "." . $extension;
                    $targetPath = $uploadDir . $secureFilename;
                    
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        chmod($targetPath, 0644);
                        $profileData['profile_photo'] = $targetPath;
                        $messages[] = "Profile photo updated";
                    }
                }
            }

            // 3. UPDATE DATABASE
            $this->repo->updateWorkerProfile($workerId, $profileData);

            // 4. UPDATE SESSION
            foreach ($profileData as $key => $value) {
                $_SESSION['worker'][$key] = $value;
            }

            // 5. HANDLE PORTFOLIO IMAGES
            if (isset($_FILES['portfolio_images']) && !empty($_FILES['portfolio_images']['tmp_name'][0])) {
                $portfolioResult = $this->handlePortfolioUpload($_FILES['portfolio_images'], $workerId);
                if ($portfolioResult['success']) {
                    $messages[] = $portfolioResult['message'];
                } else {
                    $messages[] = "Portfolio error: " . $portfolioResult['message'];
                }
            }

            // 6. SET SUCCESS MESSAGE
            if (empty($messages)) {
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['success'] = 'Profile updated! ' . implode(' | ', $messages);
            }

            header("Location: index.php?controller=Worker&action=profile");
            exit;

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile: ' . $e->getMessage();
            header("Location: index.php?controller=Worker&action=profile&edit=true");
            exit;
        }
    }

    private function handlePortfolioUpload(array $files, int $workerId): array
    {
        $uploadedCount = 0;
        $errors = [];
        
        // Create upload directory
        $uploadDir = "uploads/workers/{$workerId}/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Handle multiple files
        if (is_array($files['name'])) {
            $fileCount = count($files['name']);
            
            for ($i = 0; $i < $fileCount; $i++) {
                // Skip if no file or error
                if ($files['error'][$i] !== UPLOAD_ERR_OK || empty($files['tmp_name'][$i])) {
                    continue;
                }
                
                // Basic validation
                $fileName = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $fileSize = $files['size'][$i];
                
                // Check file type
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($tmpName);
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Invalid file type: {$fileName}";
                    continue;
                }
                
                // Check file size (10MB max)
                if ($fileSize > 10 * 1024 * 1024) {
                    $errors[] = "File too large: {$fileName}";
                    continue;
                }
                
                // Generate secure filename
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $secureFilename = "worker{$workerId}_work_" . time() . "_" . $i . "." . $extension;
                $targetPath = $uploadDir . $secureFilename;
                
                // Move file and save to database
                if (move_uploaded_file($tmpName, $targetPath)) {
                    chmod($targetPath, 0644);
                    
                    if ($this->repo->insertWorkerWork($workerId, $targetPath)) {
                        $uploadedCount++;
                    } else {
                        $errors[] = "Database error: {$fileName}";
                        unlink($targetPath);
                    }
                } else {
                    $errors[] = "Upload failed: {$fileName}";
                }
            }
        }
        
        // Return result
        if ($uploadedCount > 0) {
            $message = "{$uploadedCount} image(s) uploaded successfully!";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            return ['success' => true, 'message' => $message];
        }
        
        return ['success' => false, 'message' => 'No files uploaded. ' . implode(', ', $errors)];
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
            $work = $this->repo->getWorkerWorkById($workId, $workerId);

            if (!$work) {
                echo json_encode(['success' => false, 'message' => 'Image not found']);
                exit;
            }

            $this->repo->deleteWorkerWork($workId, $workerId);

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

    private function getWorkerStatistics(int $workerId): array
    {
        try {
            $conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("
                SELECT 
                    COALESCE(AVG(r.rating), 0) as rating_average,
                    COALESCE(COUNT(DISTINCT r.rating_id), 0) as total_ratings,
                    COALESCE(COUNT(DISTINCT c.conversation_id), 0) as total_bookings,
                    COALESCE(SUM(CASE WHEN atb.final_price > 0 THEN atb.final_price ELSE atb.budget END), 0) as total_earnings
                FROM workers w
                LEFT JOIN conversations c ON w.worker_id = c.worker_id
                LEFT JOIN ratings r ON c.conversation_id = r.conversation_id AND r.worker_id = w.worker_id
                LEFT JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE w.worker_id = ?
            ");
            
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: [
                'rating_average' => 0,
                'total_ratings' => 0,
                'total_bookings' => 0,
                'total_earnings' => 0
            ];
        } catch (Exception $e) {
            error_log("Error fetching worker statistics: " . $e->getMessage());
            return [
                'rating_average' => 0,
                'total_ratings' => 0,
                'total_bookings' => 0,
                'total_earnings' => 0
            ];
        }
    }
}