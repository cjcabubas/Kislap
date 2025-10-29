<?php

require_once __DIR__ . "/../model/repositories/BrowseRepository.php";

class BrowseController
{
    private BrowseRepository $repo;

    public function __construct()
    {
        $this->repo = new BrowseRepository();
    }

    /**
     * Update worker statistics (can be called via URL parameter)
     */
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

    /**
     * Main browse action
     */
    public function browse(): void
    {
        // Start session to get user info
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Update worker stats if requested (for fixing the booking count issue)
        if (isset($_GET['update_stats']) && $_GET['update_stats'] === '1') {
            $this->updateAllWorkerStats();
        }

        // Get current user ID if logged in
        $userId = $_SESSION['user']['user_id'] ?? null;

        // Pagination setup
        $limit = 9; // number of workers per page
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        // Filters and Sorting
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? 'all');
        $sort = $_GET['sort'] ?? 'rating';

        // Validate sort parameter
        $validSorts = ['rating', 'reviews', 'price_low', 'price_high', 'newest'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'rating';
        }

        // Fetch data
        $workers = $this->repo->getWorkersWithPortfolio($limit, $offset, $search, $category, $sort);

        // Add booking status for each worker if user is logged in
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
            unset($worker); // Break reference
        }

        $totalWorkers = $this->repo->getWorkerCount($search, $category);
        $totalPages = max(1, ceil($totalWorkers / $limit));

        $availableSpecialties = $this->repo->getAllSpecialties();

        // Pass all data to the view
        require 'views/home/browse.php';
    }

    /**
     * View individual photographer profile
     */
    public function viewProfile(): void
    {
        // Start session to get user info
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $workerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($workerId <= 0) {
            // Redirect back to browse if invalid ID
            header('Location: ?controller=Browse&action=browse');
            exit;
        }

        // Fetch specific worker by ID
        $worker = $this->repo->getWorkerByIdWithPortfolio($workerId);

        if (!$worker) {
            // Worker not found, redirect to browse
            $_SESSION['error'] = 'Photographer not found.';
            header('Location: ?controller=Browse&action=browse');
            exit;
        }

        // Check if current user has booked this photographer before
        $hasBookedBefore = false;
        $currentUser = $_SESSION['user'] ?? null;
        
        if ($currentUser) {
            require_once __DIR__ . '/../model/repositories/ChatRepository.php';
            $chatRepo = new ChatRepository();
            $hasBookedBefore = $chatRepo->hasUserBookedWorker($currentUser['user_id'], $workerId);
        }

        // Fetch additional profile data
        require_once __DIR__ . '/../model/repositories/RatingRepository.php';
        $ratingRepo = new RatingRepository();
        
        // Get reviews and ratings
        $reviews = $ratingRepo->getWorkerRatings($workerId, 10);
        $ratingStats = $ratingRepo->getWorkerRatingStats($workerId);
        
        // Ensure worker has the most up-to-date rating data
        if (!empty($ratingStats) && $ratingStats['total_ratings'] > 0) {
            $worker['average_rating'] = floatval($ratingStats['average_rating']);
            $worker['total_ratings'] = intval($ratingStats['total_ratings']);
        }
        
        // Get packages if they exist
        $packages = $this->repo->getWorkerPackages($workerId);
        
        // Get recent work/portfolio
        $recentWork = $this->repo->getWorkerRecentWork($workerId);
        
        // Debug: Check what portfolio data we're getting
        error_log("DEBUG BrowseController: recentWork for worker $workerId = " . print_r($recentWork, true));
        
        // Get worker statistics from database
        $workerStats = $this->repo->getWorkerStatistics($workerId);
        
        // Get specialty categories from database
        $allSpecialties = $this->repo->getSpecialtyCategories();

        // Load profile view
        require 'views/home/profile.php';
    }



    public function getWorkerWithBookingStatus(int $workerId, int $userId): array
    {
        // Get worker info from repository
        $worker = $this->repo->getWorkerByIdWithPortfolio($workerId);

        if (!$worker) {
            return [];
        }

        // Check booking status
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
