<?php
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
    <title>Kislap Dashboard - Overview</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/dashboard.css" type="text/css">
</head>
<body>


<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Kislap Admin Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($admin['firstName']) ?>! Here's what's happening with your platform
            today.</p>
    </div>

    <div class="stats-grid">
        <!-- Pending Applications -->
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

        <!-- Approved Workers -->
        <div class="stat-card approved">
            <div class="stat-icon">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3>Approved Workers</h3>
                <div class="stat-number"><?php echo $accepted; ?></div>
                <div class="stat-footer">
                </div>
            </div>
        </div>

        <!-- Rejected Applications -->
        <div class="stat-card rejected">
            <div class="stat-icon">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="stat-content">
                <h3>Rejected Applications</h3>
                <div class="stat-number"><?php echo $rejected; ?></div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="stat-card users">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Active Users</h3>
                <div class="stat-number"><?php echo $userCount; ?></div>
            </div>
        </div>

        <!-- Total Earnings -->
        <div class="stat-card earnings">
            <div class="stat-icon">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="stat-content">
                <h3>Total Earnings</h3>
                <div class="stat-number">₱0</div>
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
                <p>87</p>
            </div>
        </div>
        <div class="quick-stat-item" style="--card-color: #ff8533;">
            <i class="fas fa-calendar-check"></i>
            <div class="quick-stat-info">
                <h4>Completed Today</h4>
                <p>23</p>
            </div>
        </div>
        <div class="quick-stat-item" style="--card-color: #ffa500;">
            <i class="fas fa-star"></i>
            <div class="quick-stat-info">
                <h4>Avg Rating</h4>
                <p>4.8</p>
            </div>
        </div>
        <div class="quick-stat-item" style="--card-color: #ff7f00;">
            <i class="fas fa-chart-line"></i>
            <div class="quick-stat-info">
                <h4>Growth Rate</h4>
                <p>+18%</p>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>