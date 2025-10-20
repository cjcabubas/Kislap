<?php

class WorkerRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // ===== EXISTING LOGIN METHODS =====

    public function findByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT * FROM workers WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByphoneNumber($phoneNumber)
    {
        $stmt = $this->conn->prepare("SELECT * FROM workers WHERE phoneNumber = ? LIMIT 1");
        $stmt->execute([$phoneNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ===== NEW PROFILE MANAGEMENT METHODS =====

    public function getWorkerById(int $workerId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM workers WHERE worker_id = ? LIMIT 1
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateWorkerProfile(int $workerId, array $data): bool
    {
        $sql = "UPDATE workers SET 
                firstName = :firstName,
                middleName = :middleName,
                lastName = :lastName,
                phoneNumber = :phoneNumber,
                address = :address,
                specialty = :specialty,
                experience_years = :experience_years,
                bio = :bio";

        $params = [
            ':firstName' => $data['firstName'],
            ':middleName' => $data['middleName'],
            ':lastName' => $data['lastName'],
            ':phoneNumber' => $data['phoneNumber'],
            ':address' => $data['address'],
            ':specialty' => $data['specialty'],
            ':experience_years' => $data['experience_years'],
            ':bio' => $data['bio'],
            ':worker_id' => $workerId
        ];

        if (isset($data['profile_photo'])) {
            $sql .= ", profile_photo = :profile_photo";
            $params[':profile_photo'] = $data['profile_photo'];
        }

        $sql .= " WHERE worker_id = :worker_id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateWorkerPassword(int $workerId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("
            UPDATE workers SET password = ? WHERE worker_id = ?
        ");

        return $stmt->execute([$hashedPassword, $workerId]);
    }

    public function getWorkerPortfolio(int $workerId): array
    {
        $stmt = $this->conn->prepare("
            SELECT work_id, image_path, uploaded_at 
            FROM worker_works 
            WHERE worker_id = ? 
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkerPortfolioCount(int $workerId): int
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total FROM worker_works WHERE worker_id = ?
        ");
        $stmt->execute([$workerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    public function insertWorkerWork(int $workerId, string $imagePath): bool
    {
        $stmt = $this->conn->prepare("
            INSERT INTO worker_works (worker_id, image_path, uploaded_at) 
            VALUES (?, ?, NOW())
        ");
        return $stmt->execute([$workerId, $imagePath]);
    }

    public function getWorkerWorkById(int $workId, int $workerId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM worker_works 
            WHERE work_id = ? AND worker_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$workId, $workerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteWorkerWork(int $workId, int $workerId): bool
    {
        $stmt = $this->conn->prepare("
            DELETE FROM worker_works 
            WHERE work_id = ? AND worker_id = ?
        ");
        return $stmt->execute([$workId, $workerId]);
    }


    public function getPortfolioImages($workerId): array
    {
        $stmt = $this->conn->prepare("SELECT image_path FROM worker_works WHERE worker_id = ? LIMIT 8");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}