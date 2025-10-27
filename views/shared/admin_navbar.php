<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    header("Location: /Kislap/views/admin/login.php");
    exit;
}
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">

<!-- Admin Navbar -->
<header class="header">
    <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="logo-link">
        <div class="logo">
            <i class="fas fa-bolt"></i>
            <span class="logo-text">KISLAP ADMIN</span>
        </div>
    </a>
    <nav class="navbar">
        <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="nav-link">
            <i class="fas fa-tachometer-alt"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="/Kislap/index.php?controller=Admin&action=viewPendingApplications" class="nav-link">
            <i class="fas fa-clock"></i>
            <span class="nav-text">Applications</span>
        </a>
        <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers" class="nav-link">
            <i class="fas fa-users"></i>
            <span class="nav-text">Workers</span>
        </a>
        <a href="/Kislap/index.php?controller=Admin&action=bookings" class="nav-link">
            <i class="fas fa-calendar-check"></i>
            <span class="nav-text">Bookings</span>
        </a>
        
        <!-- Admin Profile Dropdown -->
        <div class="profile-dropdown">
            <button class="profile-btn">
                <i class="fas fa-user-shield"></i>
                <span><?php echo htmlspecialchars($admin['username']); ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
                <a href="/Kislap/index.php?controller=Admin&action=createAdmin">
                    <i class="fas fa-user-plus"></i> Create Admin
                </a>
                <div class="menu-divider"></div>
                <a href="/Kislap/index.php?controller=Admin&action=logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
</header>

<script>
// Dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const profileBtn = document.querySelector('.profile-btn');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (profileBtn && dropdownMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>