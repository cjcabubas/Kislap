<?php

class BrowseRepository
{
    private PDO $conn;

    // ========================================
    // CONSTRUCTOR
    // ========================================
    
    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    private function getSortSql(string $sort): string
    {
        return match ($sort) {
            'rating' => " ORDER BY w.average_rating DESC",
            'reviews' => " ORDER BY w.total_ratings DESC",
            'price_low' => " ORDER BY w.total_earnings ASC",
            'price_high' => " ORDER BY w.total_earnings DESC",
            'newest' => " ORDER BY w.created_at DESC",
            'featured' => " ORDER BY w.total_bookings DESC, w.average_rating DESC", // Featured: most bookings + high rating
            default => " ORDER BY w.total_bookings DESC, w.average_rating DESC",
        };
    }

    // ========================================
    // WORKER BROWSING
    // ========================================
    
    public function getWorkersWithPortfolio(int $limit, int $offset, string $search = '', string $category = 'all', string $sort = 'featured'): array
    {
        $sql = "
            SELECT DISTINCT w.worker_id
            FROM workers w
            WHERE w.status = 'active'
        ";

        $params = [];
        $conditions = [];

        if ($category !== 'all' && !empty($category)) {
            $conditions[] = "w.specialty = :category";
            $params[':category'] = $category;
        }

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

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        $sql .= $this->getSortSql($sort);
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

        $workers = [];
        foreach ($results as $row) {
            $id = $row['worker_id'];

            if (!isset($workers[$id])) {
                $workers[$id] = $row;
                $workers[$id]['portfolio_images'] = [];

                $workers[$id]['display_name'] = trim(
                    ($row['firstName'] ?? '') . ' ' .
                    ($row['middleName'] ?? '') . ' ' .
                    ($row['lastName'] ?? '')
                );

                unset($workers[$id]['work_id']);
                unset($workers[$id]['image_path']);
                unset($workers[$id]['work_uploaded_at']);
            }

            if (!empty($row['image_path'])) {
                $workers[$id]['portfolio_images'][] = [
                    'work_id' => $row['work_id'],
                    'image_path' => $row['image_path'],
                    'uploaded_at' => $row['work_uploaded_at']
                ];
            }
        }

        foreach ($workers as $workerId => &$worker) {
            $packageStmt = $this->conn->prepare("
                SELECT MIN(price) as min_price, MAX(price) as max_price, COUNT(*) as package_count
                FROM packages 
                WHERE worker_id = ? AND status = 'active'
            ");
            $packageStmt->execute([$workerId]);
            $packageData = $packageStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($packageData && $packageData['package_count'] > 0) {
                $worker['min_package_price'] = $packageData['min_price'];
                $worker['max_package_price'] = $packageData['max_price'];
                $worker['has_packages'] = true;
            } else {
                $worker['min_package_price'] = null;
                $worker['max_package_price'] = null;
                $worker['has_packages'] = false;
            }
        }

        $orderedWorkers = [];
        foreach ($workerIds as $id) {
            if (isset($workers[$id])) {
                $orderedWorkers[] = $workers[$id];
            }
        }

        return $orderedWorkers;
    }

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

    // ========================================
    // WORKER DETAILS AND METADATA
    // ========================================
    
    public function getAllSpecialties(): array
    {
        $sql = "SELECT DISTINCT specialty FROM workers WHERE status = 'active' AND specialty IS NOT NULL ORDER BY specialty";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getWorkerPackages(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT package_id, name, description, price, duration_hours as duration, photo_count, delivery_days, status
                FROM packages 
                WHERE worker_id = ? AND status = 'active' 
                ORDER BY price ASC
            ");
            $stmt->execute([$workerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching packages: " . $e->getMessage());
            return [];
        }
    }

    public function getWorkerRecentWork(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT work_id, image_path, uploaded_at 
                FROM worker_works 
                WHERE worker_id = ? 
                ORDER BY uploaded_at DESC 
                LIMIT 12
            ");
            $stmt->execute([$workerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching recent work: " . $e->getMessage());
            return [];
        }
    }

    public function getWorkerStatistics(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    w.total_bookings,
                    w.total_earnings,
                    w.average_rating,
                    w.total_ratings,
                    w.created_at,
                    COUNT(DISTINCT c.conversation_id) as active_conversations,
                    COUNT(DISTINCT CASE WHEN c.booking_status = 'completed' THEN c.conversation_id END) as completed_bookings,
                    COUNT(DISTINCT CASE WHEN c.booking_status IN ('confirmed', 'negotiating') THEN c.conversation_id END) as pending_bookings
                FROM workers w
                LEFT JOIN conversations c ON w.worker_id = c.worker_id
                WHERE w.worker_id = ?
                GROUP BY w.worker_id
            ");
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'total_bookings' => 0,
                    'total_earnings' => 0,
                    'average_rating' => 0,
                    'total_ratings' => 0,
                    'active_conversations' => 0,
                    'completed_bookings' => 0,
                    'pending_bookings' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error fetching worker statistics: " . $e->getMessage());
            return [
                'total_bookings' => 0,
                'total_earnings' => 0,
                'average_rating' => 0,
                'total_ratings' => 0,
                'active_conversations' => 0,
                'completed_bookings' => 0,
                'pending_bookings' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
    }

    public function getSpecialtyCategories(): array
    {
        return [
            'event' => 'Event Photography',
            'portrait' => 'Portrait Photography', 
            'product' => 'Product Photography',
            'lifestyle' => 'Lifestyle Photography',
            'photobooth' => 'Photobooth Services',
            'creative' => 'Creative/Conceptual',
            'wedding' => 'Wedding Photography',
            'corporate' => 'Corporate Photography',
            'fashion' => 'Fashion Photography',
            'nature' => 'Nature Photography'
        ];
    }
}
