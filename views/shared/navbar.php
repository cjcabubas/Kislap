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
        <a href="explore-now" class="nav-link">
            <span class="nav-text">Explore</span>
        </a>
        <a href="messages" class="nav-icon" title="Messages">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </a>
        <a href="search" class="nav-icon" title="Search">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </a>
        <a href="bookings" class="nav-icon" title="Bookings">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
        </a>

        <!-- Profile Dropdown -->
        <div class="profile-dropdown">
            <div class="profile-btn" id="profileBtn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
            </div>

            <!-- Dropdown Menu -->
            <div class="dropdown-menu" id="dropdownMenu">
                <?php if ($isLoggedIn): ?>
                    <!-- Logged In User -->
                    <div class="dropdown-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            </div>
                            <div class="user-details">
                                <h4><?php echo htmlspecialchars($userName); ?></h4>
                                <span class="user-type-badge">
                                    <?php echo $userType === 'photographer' ? 'Photographer' : 'Customer'; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($userType === 'photographer'): ?>
                        <!-- Photographer Menu -->
                        <a href="profile"><i class="fas fa-user"></i> My Profile</a>
                        <a href="portfolio"><i class="fas fa-images"></i> Portfolio</a>
                        <a href="bookings"><i class="fas fa-calendar-check"></i> My Bookings</a>
                        <a href="reviews"><i class="fas fa-star"></i> Reviews</a>
                        <div class="menu-divider"></div>
                        <button class="logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>

                    <?php else: ?>
                        <!-- Customer Menu -->
                        <a href="index.php?controller=User&action=profile"><i class="fas fa-user"></i> My Profile</a>
                        <a href="my-bookings"><i class="fas fa-calendar"></i> My Bookings</a>
                        <a href="favorites"><i class="fas fa-heart"></i> Favorites</a>
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
                    <a href="index.php?controller=Auth&action=login"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="index.php?controller=Auth&action=SignUp"><i class="fas fa-user-plus"></i> Sign Up as Customer</a>
                    <a href="index.php?controller=Application&action=registration"><i class="fas fa-camera"></i> Sign Up as Photographer</a>
                    <div class="menu-divider"></div>
                    <a href="about"><i class="fas fa-info-circle"></i> About Kislap</a>
                    <a href="help"><i class="fas fa-question-circle"></i> Help Center</a>
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

    // Logout function
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = 'logout.php';
        }
    }
</script>
