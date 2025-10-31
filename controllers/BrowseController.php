<?php

require_once __DIR__ . "/../model/repositories/BrowseRepository.php";

class BrowseController
{
    private BrowseRepository $repo;

    public function __construct()
    {
        $this->repo = new BrowseRepository();
    }

    private function updateAllWorkerStats(): void
    {
        require_once __DIR__ . '/../model/repositories/WorkerRepository.php';
        $workerRepo = new WorkerRepository();
        
        // Get all active workers using the browse repository
        $workers = $this->repo->getWorkersWithPortfolio(1000, 0, '', 'all', 'newest'); // Get all workers
        
        $updated = 0;
        foreach ($workers as $worker) {
            if ($workerRepo->updateWorkerStatistics($worker['worker_id'])) {
                $updated++;
            }
        }
        
        error_log("Updated statistics for $updated workers");
        
        // Set a session message to show success
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['success'] = "Worker statistics updated successfully! Updated $updated photographers.";
    }

    public function browse(): void
    {
        // Start session to get user info
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($_GET['update_stats']) && $_GET['update_stats'] === '1') {
            $this->updateAllWorkerStats();
        }

        $userId = $_SESSION['user']['user_id'] ?? null;

        $limit = 9; // number of workers per page
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? 'all');
        $sort = $_GET['sort'] ?? 'rating';

        $validSorts = ['rating', 'reviews', 'price_low', 'price_high', 'newest'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'rating';
        }

        $workers = $this->repo->getWorkersWithPortfolio($limit, $offset, $search, $category, $sort);

        if ($userId) {
            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();

            foreach ($workers as &$worker) {
                $incompleteBooking = $chatRepo->findIncompleteBooking($userId, $worker['worker_id']);
                $hasCompleted = $chatRepo->hasCompletedBooking($userId, $worker['worker_id']);

                $worker['has_incomplete_booking'] = !empty($incompleteBooking);
                $worker['has_completed_booking'] = $hasCompleted;
                $worker['incomplete_conversation_id'] = $incompleteBooking['conversation_id'] ?? null;
            }
            unset($worker);
        }

        $totalWorkers = $this->repo->getWorkerCount($search, $category);
        $totalPages = max(1, ceil($totalWorkers / $limit));

        $availableSpecialties = $this->repo->getAllSpecialties();

        require 'views/home/browse.php';
    }

    public function viewProfile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $workerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($workerId <= 0) {
            header('Location: ?controller=Browse&action=browse');
            exit;
        }

        $worker = $this->repo->getWorkerByIdWithPortfolio($workerId);

        if (!$worker) {
            $_SESSION['error'] = 'Photographer not found.';
            header('Location: ?controller=Browse&action=browse');
            exit;
        }

        $hasBookedBefore = false;
        $currentUser = $_SESSION['user'] ?? null;
        
        if ($currentUser) {
            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();
            $hasBookedBefore = $chatRepo->hasUserBookedWorker($currentUser['user_id'], $workerId);
        }

        require_once __DIR__ . '/../model/repositories/RatingRepository.php';
        $ratingRepo = new RatingRepository();

        $reviews = $ratingRepo->getWorkerRatings($workerId, 10);
        $ratingStats = $ratingRepo->getWorkerRatingStats($workerId);

        if (!empty($ratingStats) && $ratingStats['total_ratings'] > 0) {
            $worker['average_rating'] = floatval($ratingStats['average_rating']);
            $worker['total_ratings'] = intval($ratingStats['total_ratings']);
        }

        $packages = $this->repo->getWorkerPackages($workerId);

        $recentWork = $this->repo->getWorkerRecentWork($workerId);

        error_log("DEBUG BrowseController: recentWork for worker $workerId = " . print_r($recentWork, true));

        $workerStats = $this->repo->getWorkerStatistics($workerId);

        $allSpecialties = $this->repo->getSpecialtyCategories();

        require 'views/home/profile.php';
    }



    public function getWorkerWithBookingStatus(int $workerId, int $userId): array
    {
        $worker = $this->repo->getWorkerByIdWithPortfolio($workerId);

        if (!$worker) {
            return [];
        }

        require_once __DIR__ . '/../model/repositories/ChatRepository.php';
        $chatRepo = new ChatRepository();
        $incompleteBooking = $chatRepo->findIncompleteBooking($userId, $workerId);
        $hasCompleted = $chatRepo->hasCompletedBooking($userId, $workerId);

        $worker['has_incomplete_booking'] = !empty($incompleteBooking);
        $worker['has_completed_booking'] = $hasCompleted;
        $worker['incomplete_conversation_id'] = $incompleteBooking['conversation_id'] ?? null;

        return $worker;
    }
}
