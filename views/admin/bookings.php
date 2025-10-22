<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    header("Location: /Kislap/index.php?controller=Admin&action=login");
    exit;
}

$bookings = $bookings ?? [];
$status = $_GET['status'] ?? 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/admin.css">
    <style>
        .bookings-container {
            padding: 30px;
        }
        .filters {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .filter-btn {
            padding: 10px 20px;
            border: none;
            background: #333;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #ff6b00;
        }
        .bookings-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .bookings-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .bookings-table th {
            background: #ff6b00;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .bookings-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-confirmed { background: #28a745; color: white; }
        .status-pending_worker { background: #ffc107; color: black; }
        .status-completed { background: #17a2b8; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .status-negotiating { background: #ff9800; color: white; }
    </style>
</head>
<body>

<?php require __DIR__ . '/../shared/admin_navbar.php'; ?>

<div class="bookings-container">
    <h1><i class="fas fa-calendar-check"></i> Bookings Management</h1>
    
    <div class="filters">
        <a href="?controller=Admin&action=bookings&status=all" class="filter-btn <?php echo $status == 'all' ? 'active' : ''; ?>">All</a>
        <a href="?controller=Admin&action=bookings&status=pending_worker" class="filter-btn <?php echo $status == 'pending_worker' ? 'active' : ''; ?>">Pending</a>
        <a href="?controller=Admin&action=bookings&status=confirmed" class="filter-btn <?php echo $status == 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
        <a href="?controller=Admin&action=bookings&status=completed" class="filter-btn <?php echo $status == 'completed' ? 'active' : ''; ?>">Completed</a>
        <a href="?controller=Admin&action=bookings&status=cancelled" class="filter-btn <?php echo $status == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
    </div>
    
    <div class="bookings-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Photographer</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Price</th>
                    <th>Deposit</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">No bookings found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['conversation_id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['user_first'] . ' ' . $booking['user_last']); ?></td>
                            <td><?php echo htmlspecialchars($booking['worker_first'] . ' ' . $booking['worker_last']); ?></td>
                            <td><?php echo htmlspecialchars($booking['event_type'] ?? 'N/A'); ?></td>
                            <td><?php echo $booking['event_date'] ? date('M d, Y', strtotime($booking['event_date'])) : 'TBD'; ?></td>
                            <td>₱<?php echo number_format($booking['final_price'] ?? $booking['budget'] ?? 0, 2); ?></td>
                            <td><?php echo $booking['deposit_paid'] ? '✅ ₱' . number_format($booking['deposit_amount'], 2) : '❌'; ?></td>
                            <td><span class="status-badge status-<?php echo $booking['booking_status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
