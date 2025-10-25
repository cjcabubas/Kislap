<?php

class AdminRepository
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
    // ADMIN MANAGEMENT
    // ========================================
    
    public function signUp(array $admin): void
    {
        $admin['password'] = password_hash($admin['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO admin (username, lastName, firstName, middleName, password)
            VALUES (:username, :lastName, :firstName, :middleName, :password)
        ");
        $stmt->execute($admin);
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $admin['password'] = trim($admin['password']);
        }
        return $admin ?: null;
    }

    public function getPendingApplicationsCount()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total 
                                FROM application 
                                WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function getRejectedApplicationsCount($search = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM application WHERE status = 'rejected'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($search)) $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function getRejectedApplications(int $limit, int $offset, string $search = ''): array
    {
        // Step 1: Fetch main rejected applications
        $sql = "SELECT application_id, firstName, middleName, lastName, email, phoneNumber, address, status, created_at, updated_at
            FROM application
            WHERE status = 'rejected'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY updated_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 2: Attach resume and work images for each application
        foreach ($applications as &$app) {
            // Resume
            $stmtResume = $this->conn->prepare("
            SELECT resumeFilePath 
            FROM application_resume 
            WHERE application_id = ?
        ");
            $stmtResume->execute([$app['application_id']]);
            $resume = $stmtResume->fetch(PDO::FETCH_ASSOC);
            $app['resumeFilePath'] = $resume['resumeFilePath'] ?? '';

            // Work images
            $stmtImages = $this->conn->prepare("
            SELECT worksFilePath 
            FROM application_works 
            WHERE application_id = ?
        ");
            $stmtImages->execute([$app['application_id']]);
            $app['worksFilePath'] = $stmtImages->fetchAll(PDO::FETCH_COLUMN);
        }

        return $applications;
    }

    public function getAcceptedApplicationsCount()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total 
                                FROM application 
                                WHERE status = 'accepted'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function getUserCount(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM user");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    // ========================================
    // APPLICATION MANAGEMENT
    // ========================================
    
    public function getPendingApplications(int $limit, int $offset, string $search = ''): array
    {
        $sql = "SELECT application_id, firstName, middleName, lastName, email, phoneNumber, address, status, created_at
            FROM application
            WHERE status = 'pending'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($applications as &$app) {
            $stmtResume = $this->conn->prepare("
            SELECT resumeFilePath 
            FROM application_resume 
            WHERE application_id = ?
        ");
            $stmtResume->execute([$app['application_id']]);
            $resume = $stmtResume->fetch(PDO::FETCH_ASSOC);
            $app['resumeFilePath'] = $resume['resumeFilePath'] ?? '';

            $stmtImages = $this->conn->prepare("
            SELECT worksFilePath 
            FROM application_works 
            WHERE application_id = ?
        ");
            $stmtImages->execute([$app['application_id']]);
            $app['worksFilePath'] = $stmtImages->fetchAll(PDO::FETCH_COLUMN);
        }

        return $applications;
    }

    public function getPendingCount(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM application WHERE status = 'pending'";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($search)) $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function updateApplicationStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE application SET status = :status WHERE application_id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function getApplications($search = '')
    {
        $query = "SELECT * FROM application";

        $params = [];

        if (!empty($search)) {
            $query .= " WHERE firstName LIKE :search 
                    OR lastName LIKE :search 
                    OR email LIKE :search";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertWorker(array $data)
    {
        try {
            $sql = "INSERT INTO workers (
            application_id, lastName, firstName, middleName, email, phoneNumber, password,
            address, specialty, experience_years, bio, profile_photo, rating_average,
            total_ratings, total_bookings, total_earnings, status, created_at
        ) VALUES (
            :application_id, :lastName, :firstName, :middleName, :email, :phoneNumber, :password,
            :address, :specialty, 0, NULL, NULL, 0.00, 0, 0, 0.00, 'active', NOW()
        )";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':application_id' => $data['application_id'] ?? null,
                ':lastName' => $data['lastName'] ?? '',
                ':firstName' => $data['firstName'] ?? '',
                ':middleName' => $data['middleName'] ?? '',
                ':email' => $data['email'] ?? '',
                ':phoneNumber' => $data['phoneNumber'] ?? '',
                ':password' => $data['password'] ?? null,
                ':address' => $data['address'] ?? null,
                ':specialty' => $data['specialty'] ?? null
            ]);

            return (int)$this->conn->lastInsertId(); // return inserted worker ID
        } catch (PDOException $e) {
            error_log("Worker insert failed: " . $e->getMessage());
            return false;
        }
    }

    // In AdminRepository.php

    // ========================================
    // WORKER MANAGEMENT
    // ========================================
    
    public function getApprovedWorkersCount(string $search = '', string $statusFilter = 'all'): int
    {
        $sql = "SELECT COUNT(*) AS total FROM workers w WHERE 1=1";
        $params = [];

        // Status filter
        if ($statusFilter !== 'all') {
            $sql .= " AND w.status = :status";
            $params[':status'] = $statusFilter;
        }

        // Search filter
        if (!empty($search)) {
            $sql .= " AND (w.firstName LIKE :search OR w.lastName LIKE :search OR w.email LIKE :search OR w.phoneNumber LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function getApprovedWorkers(int $limit, int $offset, string $search = '', string $statusFilter = 'all'): array
    {
        $sql = "SELECT worker_id, firstName, middleName, lastName, email, phoneNumber, address, 
                       status AS account_status, total_bookings, total_earnings, rating_average, created_at, updated_at
                FROM workers w WHERE 1=1";
        $params = [];

        // Status filter
        if ($statusFilter !== 'all') {
            $sql .= " AND w.status = :status";
            $params[':status'] = $statusFilter;
        }

        // Search filter
        if (!empty($search)) {
            $sql .= " AND (w.firstName LIKE :search OR w.lastName LIKE :search OR w.email LIKE :search OR w.phoneNumber LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY w.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateWorkerStatus(int $workerId, string $status): bool
    {
        // Status can be 'active', 'suspended', or 'banned'
        $stmt = $this->conn->prepare("
        UPDATE workers 
        SET status = :status, updated_at = NOW() 
        WHERE worker_id = :workerId
    ");
        return $stmt->execute([
            ':status' => $status,
            ':workerId' => $workerId
        ]);
    }

    public function getApplicationWorks(string $applicationId): array
    {
        $stmt = $this->conn->prepare("SELECT work_id, worksFilePath FROM application_works WHERE application_id = ?");
        $stmt->execute([$applicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertWorkerWork(int $workerId, string $imagePath): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO worker_works (worker_id, image_path, uploaded_at) VALUES (?, ?, NOW())"
        );
        return $stmt->execute([$workerId, $imagePath]);
    }


    public function findApplicationById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM application WHERE application_id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ========================================
    // STATISTICS AND ANALYTICS
    // ========================================
    
    public function getActiveBookingsCount(): int
    {
        $sql = "SELECT COUNT(*) AS total 
            FROM conversations 
            WHERE booking_status IN ('confirmed', 'negotiating', 'pending_worker')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['total'];
    }

    public function getCompletedBookingsTodayCount(): int
    {
        $sql = "SELECT COUNT(*) AS total 
            FROM conversations c
            INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
            WHERE c.booking_status = 'completed' 
            AND atb.completed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$result['total'];
    }

    public function getAverageRating(): float
    {
        $sql = "SELECT AVG(rating) AS avg_rating FROM ratings";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return round((float)($result['avg_rating'] ?? 0), 1);
    }

    public function getBookingGrowthRate(): float
    {
        $sql_pm = "SELECT COUNT(*) AS total 
               FROM conversations 
               WHERE created_at >= DATE_SUB(LAST_DAY(NOW()), INTERVAL 1 MONTH) + INTERVAL 1 DAY
               AND created_at <= LAST_DAY(DATE_SUB(NOW(), INTERVAL 1 MONTH))";
        $stmt_pm = $this->conn->prepare($sql_pm);
        $stmt_pm->execute();
        $count_pm = (int)$stmt_pm->fetch(PDO::FETCH_ASSOC)['total'];

        $sql_mbpm = "SELECT COUNT(*) AS total 
                 FROM conversations 
                 WHERE created_at >= DATE_SUB(LAST_DAY(NOW()), INTERVAL 2 MONTH) + INTERVAL 1 DAY
                 AND created_at <= LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH))";
        $stmt_mbpm = $this->conn->prepare($sql_mbpm);
        $stmt_mbpm->execute();
        $count_mbpm = (int)$stmt_mbpm->fetch(PDO::FETCH_ASSOC)['total'];

        if ($count_mbpm == 0) {
            return ($count_pm > 0) ? 100.0 : 0.0;
        }

        $growth_rate = (($count_pm - $count_mbpm) / $count_mbpm) * 100;

        return round($growth_rate, 2);
    }

    public function getTotalEarnings(): float
    {
        $sql = "SELECT SUM(COALESCE(atb.final_price, atb.budget, 0) * 0.10) AS platform_earnings
                FROM conversations c
                INNER JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE c.booking_status = 'completed'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (float)($result['platform_earnings'] ?? 0);
    }

    public function getTotalUsers(): int
    {
        $sql = "SELECT COUNT(*) AS total_users FROM user";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['total_users'] ?? 0);
    }

    public function getTotalWorkers(): int
    {
        $sql = "SELECT COUNT(*) AS total_workers FROM workers WHERE status = 'active'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['total_workers'] ?? 0);
    }

    public function deleteApplication(int $applicationId): bool
    {
        try {
            $this->conn->beginTransaction();

            // Delete related records first
            $stmt = $this->conn->prepare("DELETE FROM application_resume WHERE application_id = ?");
            $stmt->execute([$applicationId]);

            $stmt = $this->conn->prepare("DELETE FROM application_works WHERE application_id = ?");
            $stmt->execute([$applicationId]);

            // Delete main application record
            $stmt = $this->conn->prepare("DELETE FROM application WHERE application_id = ?");
            $stmt->execute([$applicationId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error deleting application: " . $e->getMessage());
            return false;
        }
    }

}
