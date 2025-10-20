<?php

class BrowseRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAllWorkers(int $limit, int $offset, string $search = ''): array
    {
        if ($search) {
            $stmt = $this->conn->prepare("
                SELECT * FROM workers 
                WHERE firstName or lastName or middleName LIKE :search OR category LIKE :search 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':search', "%$search%");
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM workers LIMIT :limit OFFSET :offset");
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWorkerCount(string $search = ''): int
    {
        if ($search) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM workers 
                WHERE firstName or lastName or middleName LIKE :search OR category LIKE :search
            ");
            $stmt->bindValue(':search', "%$search%");
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM workers");
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
