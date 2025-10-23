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
     * Main browse action
     */
    public function browse(): void
    {
        // Pagination setup
        $limit = 9; // number of workers per page
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        // Filters and Sorting
        $search = trim($_GET['search'] ?? '');
        $category = trim($_GET['category'] ?? 'all');
        $sort = $_GET['sort'] ?? 'featured';

        // Validate sort parameter
        $validSorts = ['featured', 'rating', 'reviews', 'price_low', 'price_high', 'newest'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'featured';
        }

        // Fetch data
        $workers = $this->repo->getWorkersWithPortfolio($limit, $offset, $search, $category, $sort);
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

        // Load profile view
        require 'views/home/profile.php';
    }
}
