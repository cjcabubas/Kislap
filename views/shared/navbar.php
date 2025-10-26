<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user    = $_SESSION['user']   ?? null;
$worker  = $_SESSION['worker'] ?? null;

// Determine login state and type
if ($user) {
    $isLoggedIn = true;
    $userType   = "Customer";
} elseif ($worker) {
    $isLoggedIn = true;
    $userType   = "Worker";
} else {
    $isLoggedIn = false;
    $userType   = null;
}
?>


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">


<!-- Navbar header -->
<header class="header">
    <a href="index.php?controller=Home&action=homePage" class="logo-link">
        <div class="logo">
            <i class="fas fa-bolt"></i>
            <span class="logo-text">KISLAP</span>
        </div>
    </a>
    <nav class="navbar">
        <a href="/Kislap/index.php?controller=Browse&action=browse" class="nav-link">
            <span class="nav-text">Explore</span>
        </a>
        <a href="/Kislap/index.php?controller=Chat&action=view" class="nav-icon" title="Messages">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </a>
        <a href="/Kislap/index.php?controller=Browse&action=browse" class="nav-icon" title="Search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </a>
        <a href="/Kislap/index.php?controller=Home&action=bookings" class="nav-icon" title="Bookings">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
        </a>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown">
            <div class="profile-btn" id="profileBtn">
                <?php if ($isLoggedIn): ?>
                    <?php 
                    // Get profile photo and user data
                    if ($userType === 'Customer') {
                        $profilePhoto = $user['profilePhotoUrl'] ?? null;
                        $firstName = $user['firstName'] ?? '';
                        $lastName = $user['lastName'] ?? '';
                        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    } else {
                        $profilePhoto = $worker['profile_photo'] ?? null;
                        $firstName = $worker['firstName'] ?? '';
                        $lastName = $worker['lastName'] ?? '';
                        $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    }
                    
                    // Fallback if initials are empty
                    if (empty($initials) || strlen($initials) < 2) {
                        $initials = $userType === 'Customer' ? 'CU' : 'WO';
                    }
                    
                    // Show profile photo if available, otherwise show initials
                    if (!empty($profilePhoto)): 
                        // Ensure proper path format
                        $photoPath = $profilePhoto;
                        if (!str_starts_with($photoPath, '/') && !str_starts_with($photoPath, 'http')) {
                            $photoPath = '/Kislap/' . ltrim($photoPath, '/');
                        }
                    ?>
                        <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile Picture" class="profile-btn-img" title="<?= htmlspecialchars($userType . ' Profile') ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-btn-initials" style="display: none;" title="<?= htmlspecialchars($userType . ': ' . $initials) ?>"><?= htmlspecialchars($initials) ?></div>
                    <?php else: ?>
                        <div class="profile-btn-initials" title="<?= htmlspecialchars($userType . ': ' . $initials) ?>"><?= htmlspecialchars($initials) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                <?php endif; ?>
            </div>

            <!-- Dropdown Menu -->
            <div class="dropdown-menu" id="dropdownMenu">
                <?php if ($isLoggedIn): ?>
                    <!-- Logged In User -->
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php 
                                $profilePhoto = null;
                                $dropdownInitials = '';
                                
                                if ($userType === 'Customer') {
                                    $profilePhoto = $user['profilePhotoUrl'] ?? null;
                                    $firstName = $user['firstName'] ?? '';
                                    $lastName = $user['lastName'] ?? '';
                                    $dropdownInitials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                } else {
                                    $profilePhoto = $worker['profile_photo'] ?? null;
                                    $firstName = $worker['firstName'] ?? '';
                                    $lastName = $worker['lastName'] ?? '';
                                    $dropdownInitials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                                }
                                
                                // Fallback if initials are empty
                                if (empty($dropdownInitials) || strlen($dropdownInitials) < 2) {
                                    $dropdownInitials = $userType === 'Customer' ? 'CU' : 'WO';
                                }
                                
                                if (!empty($profilePhoto)): 
                                    // Ensure proper path format for dropdown
                                    $dropdownPhotoPath = $profilePhoto;
                                    if (!str_starts_with($dropdownPhotoPath, '/') && !str_starts_with($dropdownPhotoPath, 'http')) {
                                        $dropdownPhotoPath = '/Kislap/' . ltrim($dropdownPhotoPath, '/');
                                    }
                                ?>
                                    <img src="<?= htmlspecialchars($dropdownPhotoPath) ?>" alt="Profile Picture" class="avatar-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span class="avatar-initials" style="display: none;"><?= htmlspecialchars($dropdownInitials) ?></span>
                                <?php else: ?>
                                    <span class="avatar-initials"><?= htmlspecialchars($dropdownInitials) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <?php 
                                if ($userType === 'Customer') {
                                    $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                                    if (empty($fullName)) {
                                        $fullName = 'Customer User';
                                    }
                                } else {
                                    $fullName = trim(($worker['firstName'] ?? '') . ' ' . ($worker['lastName'] ?? ''));
                                    if (empty($fullName)) {
                                        $fullName = 'Worker User';
                                    }
                                }
                                ?>
                                <h4><?= htmlspecialchars($fullName) ?></h4>
                                <span class="user-type-badge"><?= htmlspecialchars($userType) ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($userType === 'Worker'): ?>
                        <!-- Photographer Menu -->
                        <a href="/Kislap/index.php?controller=Worker&action=profile"><i class="fas fa-user"></i> My Profile</a>
                        <a href="/Kislap/index.php?controller=Worker&action=bookings"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <a href="/Kislap/index.php?controller=Worker&action=manageAvailability"><i class="fas fa-calendar-alt"></i> Availability Calendar</a>
                        <a href="/Kislap/index.php?controller=Worker&action=dashboard"><i class="fas fa-star"></i> Dashboard</a>
                        <div class="menu-divider"></div>
                        <button class="logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>

                    <?php else: ?>
                        <!-- Customer Menu -->
                        <a href="/Kislap/index.php?controller=User&action=profile"><i class="fas fa-user"></i> My Profile</a>
                        <a href="/Kislap/index.php?controller=Home&action=bookings"><i class="fas fa-calendar"></i> My Bookings</a>
                        <a href="/Kislap/index.php?controller=Browse&action=browse"><i class="fas fa-heart"></i> Browse Photographers</a>
                        <a href="#" onclick="openNavbarSupportModal()"><i class="fas fa-headset"></i> Customer Support</a>
                        <div class="menu-divider"></div>
                        <button class="logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Guest User -->
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user" style="font-size: 18px; color: #000;"></i>
                            </div>
                            <div class="user-details">
                                <h4>Welcome, Guest</h4>
                                <span class="user-type-badge">Not Logged In</span>
                            </div>
                        </div>
                    </div>
                    <a href="/Kislap/index.php?controller=Auth&action=login"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="/Kislap/index.php?controller=Auth&action=signUp"><i class="fas fa-user-plus"></i> Sign Up as Customer</a>
                    <a href="/Kislap/index.php?controller=Application&action=registration"><i class="fas fa-camera"></i> Sign Up as Photographer</a>
                    <div class="menu-divider"></div>
                    <a href="/Kislap/index.php?controller=Home&action=homePage"><i class="fas fa-info-circle"></i> About Kislap</a>
                    <a href="/Kislap/index.php?controller=Browse&action=browse"><i class="fas fa-question-circle"></i> Browse Photographers</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<script>
    // Toggle Dropdown
    const profileBtn = document.getElementById('profileBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    profileBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
        profileBtn.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.remove('show');
            profileBtn.classList.remove('active');
        }
    });

    // Logout functions
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '/Kislap/index.php?controller=Auth&action=logout';
        }
    }
    
    function logoutWorker() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = '/Kislap/index.php?controller=Worker&action=logout';
        }
    }
    
    // Navbar Support Modal Function
    function openNavbarSupportModal() {
        // Redirect to profile page where support modal can be opened
        window.location.href = '/Kislap/index.php?controller=User&action=profile#support';
    }
</script>
