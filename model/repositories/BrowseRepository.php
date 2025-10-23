<?php

class BrowseRepository
{
    private PDO $conn;

    public function __construct()
    {
        // CONSIDER SECURITY: Replace this with safer configuration loading (e.g., .env)
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function getSortSql(string $sort): string
    {
        return match ($sort) {
            'rating' => " ORDER BY w.rating_average DESC",
            'reviews' => " ORDER BY w.total_ratings DESC",
            'price_low' => " ORDER BY w.total_earnings ASC",
            'price_high' => " ORDER BY w.total_earnings DESC",
            'newest' => " ORDER BY w.created_at DESC",
            'featured' => " ORDER BY w.total_bookings DESC, w.rating_average DESC", // Featured: most bookings + high rating
            default => " ORDER BY w.total_bookings DESC, w.rating_average DESC",
        };
    }

    /**
     * Fetch workers with their portfolio images grouped together
     */
    public function getWorkersWithPortfolio(int $limit, int $offset, string $search = '', string $category = 'all', string $sort = 'featured'): array
    {
        // First, get the worker IDs that match our criteria with pagination
        $sql = "
            SELECT DISTINCT w.worker_id
            FROM workers w
            WHERE w.status = 'active'
        ";

        $params = [];
        $conditions = [];

        // Add Category Filter
        if ($category !== 'all' && !empty($category)) {
            $conditions[] = "w.specialty = :category";
            $params[':category'] = $category;
        }

        // Add Search Filter
        if (!empty($search)) {
            $search_term = "%$search%";
            $conditions[] = "
                (w.firstName LIKE :search_term
                OR w.lastName LIKE :search_term
                OR w.middleName LIKE :search_term
                OR w.specialty LIKE :search_term
                OR w.address LIKE :search_term
                OR w.bio LIKE :search_term)
            ";
            $params[':search_term'] = $search_term;
        }

        // Add conditions if any
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        // Apply Sorting
        $sql .= $this->getSortSql($sort);

        // Apply Pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->conn->prepare($sql);

        // Bind parameters
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        $workerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // If no workers found, return empty array
        if (empty($workerIds)) {
            return [];
        }

        // Now fetch full worker details with portfolio images
        $placeholders = implode(',', array_fill(0, count($workerIds), '?'));
        $sql = "
            SELECT 
                w.*,
                ww.work_id,
                ww.image_path,
                ww.uploaded_at as work_uploaded_at
            FROM workers w
            LEFT JOIN worker_works ww ON w.worker_id = ww.worker_id
            WHERE w.worker_id IN ($placeholders)
        ";

        // Apply the same sorting to maintain order
        $sql .= $this->getSortSql($sort);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($workerIds);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group results to aggregate portfolio images per worker
        $workers = [];
        foreach ($results as $row) {
            $id = $row['worker_id'];

            if (!isset($workers[$id])) {
                // Initialize worker data
                $workers[$id] = $row;
                $workers[$id]['portfolio_images'] = [];

                // Create display name
                $workers[$id]['display_name'] = trim(
                    ($row['firstName'] ?? '') . ' ' .
                    ($row['middleName'] ?? '') . ' ' .
                    ($row['lastName'] ?? '')
                );

                // Remove portfolio-specific fields from main array
                unset($workers[$id]['work_id']);
                unset($workers[$id]['image_path']);
                unset($workers[$id]['work_uploaded_at']);
            }

            // Add portfolio image if exists
            if (!empty($row['image_path'])) {
                $workers[$id]['portfolio_images'][] = [
                    'work_id' => $row['work_id'],
                    'image_path' => $row['image_path'],
                    'uploaded_at' => $row['work_uploaded_at']
                ];
            }
        }

        // Maintain the order from the first query
        $orderedWorkers = [];
        foreach ($workerIds as $id) {
            if (isset($workers[$id])) {
                $orderedWorkers[] = $workers[$id];
            }
        }

        return $orderedWorkers;
    }

    /**
     * Get total count of workers matching criteria
     */
    public function getWorkerCount(string $search = '', string $category = 'all'): int
    {
        $sql = "SELECT COUNT(DISTINCT w.worker_id) FROM workers w WHERE w.status = 'active'";
        $params = [];
        $conditions = [];

        // Add Category Filter
        if ($category !== 'all' && !empty($category)) {
            $conditions[] = "w.specialty = :category";
            $params[':category'] = $category;
        }

        // Add Search Filter
        if (!empty($search)) {
            $search_term = "%$search%";
            $conditions[] = "
                (w.firstName LIKE :search_term
                OR w.lastName LIKE :search_term
                OR w.middleName LIKE :search_term
                OR w.specialty LIKE :search_term
                OR w.address LIKE :search_term
                OR w.bio LIKE :search_term)
            ";
            $params[':search_term'] = $search_term;
        }

        // Add conditions if any
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getWorkerByIdWithPortfolio(int $workerId): ?array
    {
        try {
            // Fetch worker details
            $stmt = $this->conn->prepare("
                SELECT 
                    w.*,
                    COALESCE(w.firstName, '') as firstName,
                    COALESCE(w.middleName, '') as middleName,
                    COALESCE(w.lastName, '') as lastName
                FROM workers w
                WHERE w.worker_id = ? AND w.status = 'active'
                LIMIT 1
            ");

            $stmt->execute([$workerId]);
            $worker = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$worker) {
                return null;
            }

            // Fetch portfolio images for this worker
            $portfolioStmt = $this->conn->prepare("
                SELECT image_path, uploaded_at
                FROM worker_works
                WHERE worker_id = ?
                ORDER BY uploaded_at DESC
                LIMIT 8
            ");

            $portfolioStmt->execute([$workerId]);
            $portfolio = $portfolioStmt->fetchAll(PDO::FETCH_ASSOC);

            // Attach portfolio to worker data
            $worker['portfolio_images'] = $portfolio;

            return $worker;

        } catch (PDOException $e) {
            error_log("Error fetching worker profile: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all unique specialties from workers table
     */
    public function getAllSpecialties(): array
    {
        $sql = "SELECT DISTINCT specialty FROM workers WHERE status = 'active' AND specialty IS NOT NULL ORDER BY specialty";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
