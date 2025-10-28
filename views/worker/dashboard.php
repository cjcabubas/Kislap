<?php
if (!isset($_SESSION['worker'])) {
    header("Location: /Kislap/views/worker/login.php");
    exit;
}
$worker = $_SESSION['worker'];
$stats = $stats ?? [];
$recentBookings = $recentBookings ?? [];
$earningsData = $earningsData ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/worker_dashboard.css">
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome back, <?= htmlspecialchars($worker['firstName']) ?>!</h1>
        <p>Here's your photography business overview</p>
        
        <div class="quick-actions">
            <a href="/Kislap/index.php?controller=Worker&action=bookings" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                Manage Bookings
            </a>
            <a href="/Kislap/index.php?controller=Worker&action=profile" class="action-btn">
                <i class="fas fa-user-edit"></i>
                Edit Profile
            </a>
            <a href="/Kislap/index.php?controller=Worker&action=manageAvailability" class="action-btn">
                <i class="fas fa-calendar-alt"></i>
                Set Availability
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card bookings">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3>Total Bookings</h3>
                <div class="stat-number"><?php echo $stats['total_bookings'] ?? 0; ?></div>
                <div class="stat-footer">
                    <span class="trend up">
                        <i class="fas fa-arrow-up"></i>
                        All time
                    </span>
                </div>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pending Requests</h3>
                <div class="stat-number"><?php echo $stats['pending_bookings'] ?? 0; ?></div>
                <div class="stat-footer">
                    <span class="trend">
                        <i class="fas fa-hourglass-half"></i>
                        Awaiting response
                    </span>
                </div>
            </div>
        </div>

        <div class="stat-card completed">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Completed Jobs</h3>
                <div class="stat-number"><?php echo $stats['completed_bookings'] ?? 0; ?></div>
                <div class="stat-footer">
                    <span class="trend up">
                        <i class="fas fa-trophy"></i>
                        Success rate: <?php echo $stats['total_bookings'] > 0 ? round(($stats['completed_bookings'] / $stats['total_bookings']) * 100, 1) : 0; ?>%
                    </span>
                </div>
            </div>
        </div>

        <div class="stat-card earnings">
            <div class="stat-icon">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="stat-content">
                <h3>Total Earnings</h3>
                <div class="stat-number">₱<?php echo number_format($earningsData['total_earnings'] ?? 0, 2); ?></div>
                <div class="stat-footer">
                    <span class="trend up">
                        <i class="fas fa-chart-line"></i>
                        Avg: ₱<?php echo number_format($stats['avg_booking_value'] ?? 0, 2); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="stat-card rating">
            <div class="stat-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3>Average Rating</h3>
                <div class="stat-number"><?php echo number_format($earningsData['rating_average'] ?? 0, 1); ?></div>
                <div class="stat-footer">
                    <span class="trend">
                        <i class="fas fa-users"></i>
                        <?php echo $earningsData['total_ratings'] ?? 0; ?> reviews
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="recent-bookings">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> Recent Bookings</h2>
                <a href="/Kislap/index.php?controller=Worker&action=bookings" class="view-all">View All</a>
            </div>

            <?php if (empty($recentBookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No recent bookings</h3>
                    <p>Your booking requests will appear here</p>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($recentBookings as $booking): ?>
                        <div class="booking-item status-<?php echo strtolower($booking['booking_status']); ?>">
                            <div class="booking-info">
                                <div class="client-name">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($booking['user_first'] . ' ' . $booking['user_last']); ?>
                                </div>
                                <div class="booking-details">
                                    <span class="event-type">
                                        <i class="fas fa-camera"></i>
                                        <?php echo htmlspecialchars($booking['event_type'] ?? 'Photography Service'); ?>
                                    </span>
                                    <?php if ($booking['event_date']): ?>
                                        <span class="event-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M d, Y', strtotime($booking['event_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="booking-price">
                                        <i class="fas fa-peso-sign"></i>
                                        ₱<?php echo number_format($booking['final_price'] ?? $booking['budget'] ?? 0, 2); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="booking-status">
                                <span class="status-badge status-<?php echo strtolower($booking['booking_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                                </span>
                                <div class="booking-date">
                                    <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="quick-stats">
            <div class="section-header">
                <h2><i class="fas fa-chart-bar"></i> Quick Stats</h2>
            </div>

            <div class="stats-summary">
                <div class="summary-item">
                    <div class="summary-icon confirmed">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="summary-info">
                        <h4>Confirmed Bookings</h4>
                        <p><?php echo $stats['confirmed_bookings'] ?? 0; ?> active</p>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="summary-info">
                        <h4>Cancelled Bookings</h4>
                        <p><?php echo $stats['cancelled_bookings'] ?? 0; ?> total</p>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon revenue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="summary-info">
                        <h4>Total Revenue</h4>
                        <p>₱<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Add any dashboard-specific JavaScript here
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh stats every 5 minutes
        setInterval(function() {
            // You can add AJAX call to refresh stats here
        }, 300000);
    });
</script>

</body>
</html>