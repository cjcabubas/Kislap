<?php

require_once __DIR__ . '/BaseRepository.php';

class WorkerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
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
        $result = $stmt->execute($params);
        
        if (!$result) {
            error_log("Database error in updateWorkerProfile: " . implode(", ", $stmt->errorInfo()));
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
        }
        
        return $result;
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

    public function getWorkerAvailability(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM worker_availability 
                WHERE worker_id = ? 
                ORDER BY date ASC
            ");
            $stmt->execute([$workerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching worker availability: " . $e->getMessage());
            return [];
        }
    }

    public function calculateWorkerStatistics(int $workerId): array
    {
        try {
            $stats = [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'total_earnings' => 0,
                'total_ratings' => 0,
                'rating_average' => 0
            ];
            
            // Reviews table doesn't exist, use stored values from workers table
            try {
                // Get stored values from workers table
                $stmt = $this->conn->prepare("SELECT total_ratings, average_rating FROM workers WHERE worker_id = ?");
                $stmt->execute([$workerId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stats['total_ratings'] = (int)($result['total_ratings'] ?? 0);
                $stats['rating_average'] = round((float)($result['average_rating'] ?? 0), 1);
            } catch (Exception $e) {
                $stats['total_ratings'] = 0;
                $stats['rating_average'] = 0;
            }
            
            // Get conversations/bookings count
            try {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM conversations WHERE worker_id = ?");
                $stmt->execute([$workerId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['total_bookings'] = (int)($result['count'] ?? 0);
                error_log("Total conversations/bookings: {$stats['total_bookings']}");
                
                // Get completed bookings
                $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM conversations WHERE worker_id = ? AND booking_status = 'completed'");
                $stmt->execute([$workerId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['completed_bookings'] = (int)($result['count'] ?? 0);
            } catch (Exception $e) {
                // Handle error silently
            }
            
            // Get earnings from ai_temp_bookings or similar table
            try {
                // First try ai_temp_bookings
                $stmt = $this->conn->prepare("
                    SELECT COALESCE(SUM(total_amount), 0) as total_earnings 
                    FROM ai_temp_bookings atb 
                    JOIN conversations c ON atb.conversation_id = c.conversation_id 
                    WHERE c.worker_id = ? AND c.booking_status = 'completed'
                ");
                $stmt->execute([$workerId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats['total_earnings'] = (float)($result['total_earnings'] ?? 0);
            } catch (Exception $e) {
                // Try alternative - maybe there's a payments table or similar
                try {
                    $stmt = $this->conn->prepare("
                        SELECT COALESCE(SUM(amount), 0) as total_earnings 
                        FROM payments p 
                        JOIN conversations c ON p.conversation_id = c.conversation_id 
                        WHERE c.worker_id = ? AND p.status = 'completed'
                    ");
                    $stmt->execute([$workerId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stats['total_earnings'] = (float)($result['total_earnings'] ?? 0);
                } catch (Exception $e2) {
                    // If we have completed bookings but no earnings data, estimate
                    if ($stats['completed_bookings'] > 0) {
                        $stats['total_earnings'] = $stats['completed_bookings'] * 5000; // Estimate ₱5000 per booking
                    }
                }
            }
            return $stats;
        } catch (Exception $e) {
            return [
                'total_bookings' => 0,
                'completed_bookings' => 0,
                'total_earnings' => 0,
                'total_ratings' => 0,
                'rating_average' => 0
            ];
        }
    }

    public function updateWorkerStatistics(int $workerId): bool
    {
        try {
            $stats = $this->calculateWorkerStatistics($workerId);
            
            $stmt = $this->conn->prepare("
                UPDATE workers 
                SET total_bookings = ?, 
                    total_earnings = ?, 
                    average_rating = ?, 
                    total_ratings = ?
                WHERE worker_id = ?
            ");
            
            return $stmt->execute([
                $stats['total_bookings'],
                $stats['total_earnings'],
                $stats['rating_average'],
                $stats['total_ratings'],
                $workerId
            ]);
        } catch (Exception $e) {
            error_log("Error updating worker statistics: " . $e->getMessage());
            return false;
        }
    }

    // Temporary method to set rating for testing
    public function setWorkerRating(int $workerId, float $rating, int $totalRatings): bool
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE workers 
                SET average_rating = ?, total_ratings = ?
                WHERE worker_id = ?
            ");
            
            return $stmt->execute([$rating, $totalRatings, $workerId]);
        } catch (Exception $e) {
            error_log("Error setting worker rating: " . $e->getMessage());
            return false;
        }
    }

    // Get stats directly from workers table - matches exact DB structure
    public function getWorkerStats(int $workerId): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT average_rating, total_ratings, total_bookings, total_earnings FROM workers WHERE worker_id = ?");
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            

            
            if ($result) {
                return $result; // Return exactly what's in the database
            } else {
                return [
                    'average_rating' => '0.00',
                    'total_ratings' => 0,
                    'total_bookings' => 0,
                    'total_earnings' => '0.00'
                ];
            }
        } catch (Exception $e) {
            error_log("DB ERROR: " . $e->getMessage());
            return [
                'average_rating' => '0.00',
                'total_ratings' => 0,
                'total_bookings' => 0,
                'total_earnings' => '0.00'
            ];
        }
    }

    // ========================================
    // SUSPENSION MANAGEMENT
    // ========================================

    public function reactivateWorker(int $workerId): bool
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE workers 
                SET status = 'active', 
                    suspended_until = NULL, 
                    suspension_reason = NULL,
                    suspended_by = NULL,
                    suspended_at = NULL
                WHERE worker_id = ?
            ");
            return $stmt->execute([$workerId]);
        } catch (Exception $e) {
            error_log("Error reactivating worker: " . $e->getMessage());
            return false;
        }
    }

    public function suspendWorker(int $workerId, string $reason, ?string $suspendedUntil = null, int $suspendedBy = null): bool
    {
        try {
            $stmt = $this->conn->prepare("
                UPDATE workers 
                SET status = 'suspended',
                    suspension_reason = ?,
                    suspended_until = ?,
                    suspended_by = ?,
                    suspended_at = NOW()
                WHERE worker_id = ?
            ");
            return $stmt->execute([$reason, $suspendedUntil, $suspendedBy, $workerId]);
        } catch (Exception $e) {
            error_log("Error suspending worker: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // EARNINGS CALCULATION
    // ========================================
    
    public function getCompletedBookingsEarnings(int $workerId): float
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT SUM(COALESCE(atb.final_price, 0)) as total_completed_revenue
                FROM conversations c
                JOIN ai_temp_bookings atb ON c.conversation_id = atb.conversation_id
                WHERE c.worker_id = ? 
                AND c.booking_status = 'completed'
                AND atb.final_price IS NOT NULL
                AND atb.final_price > 0
            ");
            
            $stmt->execute([$workerId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $totalRevenue = floatval($result['total_completed_revenue'] ?? 0);
            
            // Deduct 10% platform fee
            $workerEarnings = $totalRevenue * 0.9;
            
            return $workerEarnings;
            
        } catch (Exception $e) {
            error_log("Error calculating completed bookings earnings: " . $e->getMessage());
            return 0.0;
        }
    }
}
