<?php
require_once __DIR__ . '/../model/repositories/BrowseRepository.php';

class BrowseController
{
    private BrowseRepository $repo;

    public function __construct()
    {
        $this->repo = new BrowseRepository();
    }

    public function browse(): void
    {
        // Pagination setup
        $limit = 9; // number of workers per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Optional search filter
        $search = $_GET['search'] ?? '';

        // Fetch data
        $workers = $this->repo->getAllWorkers($limit, $offset, $search);
        $totalWorkers = $this->repo->getWorkerCount($search);
        $totalPages = ceil($totalWorkers / $limit);

        // Load view
        require 'views/home/browse.php';
    }
}
