<?php

class WorkerRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=kislap", "root", "");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


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

    // ===== NEW PACKAGE MANAGEMENT METHODS =====

    public function getWorkerPackages(int $workerId): array
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM packages 
            WHERE worker_id = ? 
            ORDER BY package_id ASC 
            LIMIT 3
        ");
        $stmt->execute([$workerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function syncWorkerPackages(int $workerId, array $submittedPackages): void
    {

        $stmt = $this->conn->prepare("SELECT package_id FROM packages WHERE worker_id = ?");
        $stmt->execute([$workerId]);
        $dbPackageIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $submittedPackageIds = [];
        // Collect IDs from submitted packages that have content
        foreach ($submittedPackages as $pkg) {
            if (!empty($pkg['name']) && !empty($pkg['package_id'])) {
                $submittedPackageIds[] = $pkg['package_id'];
            }
        }

        $idsToDelete = array_diff($dbPackageIds, $submittedPackageIds);

        $this->conn->beginTransaction();

        try {
            // Step 1: Delete packages that were removed from the form
            if (!empty($idsToDelete)) {
                $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
                $deleteStmt = $this->conn->prepare(
                    "DELETE FROM packages WHERE worker_id = ? AND package_id IN ($placeholders)"
                );
                $deleteStmt->execute(array_merge([$workerId], $idsToDelete));
            }

            // Step 2: Upsert (Update or Insert) packages
            foreach ($submittedPackages as $pkg) {
                // Only process if the package has a name, otherwise skip
                if (empty(trim($pkg['name']))) {
                    continue;
                }

                if (!empty($pkg['package_id'])) {
                    // UPDATE existing package
                    $updateStmt = $this->conn->prepare("
                        UPDATE packages SET
                            name = :name, description = :description, price = :price,
                            duration_hours = :duration_hours, photo_count = :photo_count,
                            delivery_days = :delivery_days, status = :status
                        WHERE package_id = :package_id AND worker_id = :worker_id
                    ");
                    $updateStmt->execute([
                        ':name' => $pkg['name'],
                        ':description' => $pkg['description'],
                        ':price' => $pkg['price'],
                        ':duration_hours' => $pkg['duration_hours'],
                        ':photo_count' => $pkg['photo_count'],
                        ':delivery_days' => $pkg['delivery_days'],
                        ':status' => $pkg['status'],
                        ':package_id' => $pkg['package_id'],
                        ':worker_id' => $workerId
                    ]);
                } else {
                    // INSERT new package
                    $insertStmt = $this->conn->prepare("
                        INSERT INTO packages (worker_id, name, description, price, duration_hours, photo_count, delivery_days, status)
                        VALUES (:worker_id, :name, :description, :price, :duration_hours, :photo_count, :delivery_days, :status)
                    ");
                    $insertStmt->execute([
                        ':worker_id' => $workerId,
                        ':name' => $pkg['name'],
                        ':description' => $pkg['description'],
                        ':price' => $pkg['price'],
                        ':duration_hours' => $pkg['duration_hours'],
                        ':photo_count' => $pkg['photo_count'],
                        ':delivery_days' => $pkg['delivery_days'],
                        ':status' => $pkg['status']
                    ]);
                }
            }

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e; // Re-throw the exception to be caught by the controller
        }
    }

    public function getWorkerEarnings(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    total_earnings,
                    total_bookings,
                    rating_average,
                    total_ratings
                FROM workers 
                WHERE worker_id = ?
            ");
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: [
                'total_earnings' => 0,
                'total_bookings' => 0,
                'rating_average' => 0,
                'total_ratings' => 0
            ];
        } catch (Exception $e) {
            error_log("Error fetching worker earnings: " . $e->getMessage());
            return [
                'total_earnings' => 0,
                'total_bookings' => 0,
                'rating_average' => 0,
                'total_ratings' => 0
            ];
        }
    }
}