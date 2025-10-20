<?php

class AdminRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Create a new admin
    public function signUp(array $admin): void
    {
        $admin['password'] = password_hash($admin['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            INSERT INTO admin (username, lastName, firstName, middleName, password)
            VALUES (:username, :lastName, :firstName, :middleName, :password)
        ");
        $stmt->execute($admin);
    }

    // Find admin by username
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $admin['password'] = trim($admin['password']); // remove hidden chars
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

    public function getRejectedApplicationsCount()
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total 
                                FROM application 
                                WHERE status = 'rejected'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
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

    public function getPendingApplications(int $limit, int $offset, string $search = ''): array
    {
        // Step 1: Fetch main pending applications
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
                ':lastName'       => $data['lastName'] ?? '',
                ':firstName'      => $data['firstName'] ?? '',
                ':middleName'     => $data['middleName'] ?? '',
                ':email'          => $data['email'] ?? '',
                ':phoneNumber'    => $data['phoneNumber'] ?? '',
                ':password'       => $data['password'] ?? null,
                ':address'        => $data['address'] ?? null,
                ':specialty'      => $data['specialty'] ?? null
            ]);

            return (int)$this->conn->lastInsertId(); // return inserted worker ID
        } catch (PDOException $e) {
            error_log("Worker insert failed: " . $e->getMessage());
            return false;
        }
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



}
