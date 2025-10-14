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

    public function getPendingApplications(int $limit, int $offset, string $search = ''): array {
        // Step 1: Get main applications
        $sql = "SELECT application_id, firstName, middleName, lastName, email, phoneNumber, address, status, createdAt
            FROM application
            WHERE status = 'pending'";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        $sql .= " ORDER BY createdAt DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        if (!empty($search)) $stmt->bindValue(':search', $params[':search'], PDO::PARAM_STR);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 2: Get resumes and images for each application
        foreach ($applications as &$app) {
            // Resume
            $stmtResume = $this->conn->prepare("SELECT resume_path FROM application_resume WHERE application_id = ?");
            $stmtResume->execute([$app['application_id']]);
            $resume = $stmtResume->fetch(PDO::FETCH_ASSOC);
            $app['resume_path'] = $resume['resume_path'] ?? '';

            // Images
            $stmtImages = $this->conn->prepare("SELECT image_path FROM application_works WHERE application_id = ?");
            $stmtImages->execute([$app['application_id']]);
            $app['images'] = $stmtImages->fetchAll(PDO::FETCH_COLUMN);
        }

        return $applications;
    }

    public function getPendingCount(string $search = ''): int {
        $sql = "SELECT COUNT(*) AS total FROM applications WHERE status = 'pending'";
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

    public function updateApplicationStatus(int $applicationId, string $status): void {
        $stmt = $this->conn->prepare("UPDATE applications SET status = :status WHERE application_id = :id");
        $stmt->execute([':status' => $status, ':id' => $applicationId]);
    }


}
