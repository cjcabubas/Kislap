<?php
/**
 * Script to update all worker statistics including booking counts
 * Run this script to fix the "0 bookings" issue in browse
 */

require_once __DIR__ . '/model/repositories/WorkerRepository.php';

try {
    $workerRepo = new WorkerRepository();
    
    // Get all active workers
    $stmt = $workerRepo->conn->prepare("SELECT worker_id FROM workers WHERE status = 'active'");
    $stmt->execute();
    $workers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Updating statistics for " . count($workers) . " workers...\n";
    
    $updated = 0;
    foreach ($workers as $workerId) {
        if ($workerRepo->updateWorkerStatistics($workerId)) {
            $updated++;
            echo "Updated worker ID: $workerId\n";
        } else {
            echo "Failed to update worker ID: $workerId\n";
        }
    }
    
    echo "\nCompleted! Updated $updated out of " . count($workers) . " workers.\n";
    echo "The browse page should now show correct booking counts.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>