<?php
// ========================================
// SESSION MANAGEMENT
// ========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    header("Location: /Kislap/views/admin/login.php");
    exit;
}
$admin = $_SESSION['admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/dashboard.css">
</head>
<body>

<!-- ========================================
     DASHBOARD CONTAINER
     ======================================== -->

<div class="dashboard-container">
    <!-- ========================================
         DASHBOARD HEADER
         ======================================== -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1>Kislap Admin Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($admin['firstName']) ?>! Here's what's happening with your platform today.</p>
        </div>
        <div class="header-actions">
            <a href="/Kislap/index.php?controller=Admin&action=createAdmin" class="btn-create-admin">
                <i class="fas fa-user-plus"></i>
                Create Admin Account
            </a>
            <a href="/Kislap/index.php?controller=Admin&action=logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <?php $stats = $stats ?? []; ?>

    <!-- ========================================
         STATISTICS GRID
         ======================================== -->
    <div class="stats-grid">
        <a href="/Kislap/index.php?controller=Admin&action=viewPendingApplications">
        <div class="stat-card pending">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>Pending Applications</h3>
                    <div class="stat-number"><?php echo $pending; ?></div>
                    <div class="stat-footer">
                    </div>
                </div>
            </div>
        </a>

        <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers">
            <div class="stat-card approved">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3>Approved Workers</h3>
                    <div class="stat-number"><?php echo $approved; ?></div>
                    <div class="stat-footer">
                    </div>
                </div>
            </div>
        </a>

        <a href="/Kislap/index.php?controller=Admin&action=viewRejectedApplications">
            <div class="stat-card rejected">
                <div class="stat-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="stat-content">
                    <h3>Rejected Workers</h3>
                    <div class="stat-number"><?php echo $rejected; ?></div>
                    <div class="stat-footer">
                    </div>
                </div>
            </div>
        </a>


    </div>
</div>

<!-- Platform Overview Section -->
<div class="platform-overview">
    <h2><i class="fas fa-chart-pie"></i> Platform Overview</h2>
    <div class="overview-cards">
        <div class="overview-card earnings">
            <div class="overview-icon">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="overview-content">
                <div class="overview-label">Platform Earnings</div>
                <div class="overview-value">₱<?php echo number_format($totalEarnings, 2); ?></div>
                <div class="overview-note">10% commission from completed bookings</div>
            </div>
        </div>

        <div class="overview-card users">
            <div class="overview-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="overview-content">
                <div class="overview-label">Total Users</div>
                <div class="overview-value"><?php echo number_format($totalUsers); ?></div>
                <div class="overview-note">Registered platform users</div>
            </div>
        </div>
    </div>
</div>

<div class="chart-section">
    <h2>Quick Statistics</h2>
    <div class="quick-stats">
        <div class="quick-stat-item" style="--card-color: #ff6b00;">
            <i class="fas fa-briefcase"></i>
            <div class="quick-stat-info">
                <h4>Active Bookings</h4>
                <p><?php echo $activeBookings ?? 0; ?></p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #ff8533;">
            <i class="fas fa-calendar-check"></i>
            <div class="quick-stat-info">
                <h4>Completed Today</h4>
                <p><?php echo $completedToday ?? 0; ?></p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #ffa500;">
            <i class="fas fa-star"></i>
            <div class="quick-stat-info">
                <h4>Avg Rating</h4>
                <p><?php echo number_format($avgRating ?? 0, 1); ?></p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #28a745;">
            <i class="fas fa-chart-line"></i>
            <div class="quick-stat-info">
                <h4>Growth Rate</h4>
                <p>
                    <?php echo ($growthRate >= 0 ? '+' : '') . number_format($growthRate ?? 0, 2) . '%'; ?>
                </p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #17a2b8;">
            <i class="fas fa-peso-sign"></i>
            <div class="quick-stat-info">
                <h4>Platform Earnings</h4>
                <p>₱<?php echo number_format($totalEarnings ?? 0, 2); ?></p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #6f42c1;">
            <i class="fas fa-users"></i>
            <div class="quick-stat-info">
                <h4>Total Users</h4>
                <p><?php echo number_format($totalUsers ?? 0); ?></p>
            </div>
        </div>

        <div class="quick-stat-item" style="--card-color: #fd7e14;">
            <i class="fas fa-camera"></i>
            <div class="quick-stat-info">
                <h4>Active Workers</h4>
                <p><?php echo number_format($totalWorkers ?? 0); ?></p>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>