<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/landing.css" type="text/css">
    <title>KISLAP - Professional Photography Booking</title>
</head>
<body>
<div class="hero">
    <!-- Skip for returning visitors -->
    <a href="index.php?controller=Home&action=homePage" class="skip-link">
        <i class="fas fa-times"></i> Skip intro
    </a>

    <!-- Animated Background -->
    <div class="hero-background"></div>

    <!-- Camera Pattern -->
    <div class="camera-pattern">
        <i class="fas fa-camera camera-icon"></i>
        <i class="fas fa-camera-retro camera-icon"></i>
        <i class="fas fa-video camera-icon"></i>
        <i class="fas fa-camera camera-icon"></i>
        <i class="fas fa-camera-retro camera-icon"></i>
        <i class="fas fa-video camera-icon"></i>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="logo">
            <i class="fas fa-bolt"></i>
        </div>

        <h1>KISLAP</h1>
        <p class="tagline">CAPTURE MOMENTS. CREATE MEMORIES.</p>
        <p class="description">
            Your gateway to professional photography services in the Philippines.
            Connect with talented photographers for every occasion — from weddings and events
            to portraits and commercial shoots.
        </p>

        <!-- Photography Services -->
        <div class="services">
            <div class="service-item">
                <i class="fas fa-heart"></i>
                <h3>Weddings</h3>
                <p>Capture your special day with professional wedding photographers</p>
            </div>

            <div class="service-item">
                <i class="fas fa-birthday-cake"></i>
                <h3>Events</h3>
                <p>Make every celebration memorable with expert event photography</p>
            </div>

            <div class="service-item">
                <i class="fas fa-user-circle"></i>
                <h3>Portraits</h3>
                <p>Professional headshots and portrait sessions for every need</p>
            </div>

            <div class="service-item">
                <i class="fas fa-photo-film"></i>
                <h3>Photobooth</h3>
                <p>Elevate your day with our photobooth specialized professionals</p>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <p class="cta-text">Ready to find your perfect photographer?</p>
            <div class="cta-buttons">
                <a href="/Kislap/index.php?controller=Browse&action=browse" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Browse Photographers
                </a>
                <a href="/Kislap/index.php?controller=Auth&action=signUp" class="btn btn-secondary">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </a>
            </div>
        </div>

        <!-- Why Choose Kislap -->
        <div class="features">
            <div class="features-grid">
                <div class="feature">
                    <i class="fas fa-check"></i>

                    <h4>Verified Professionals</h4>
                    <p>All photographers are vetted and verified for your peace of mind</p>
                </div>

                <div class="feature">
                    <i class="fas fa-star"></i>
                    <h4>Rated & Reviewed</h4>
                    <p>Read authentic reviews from real clients before you book</p>
                </div>

                <div class="feature">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Easy Booking</h4>
                    <p>Simple, hassle-free booking process from start to finish</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Parallax effect for camera icons
    document.addEventListener('mousemove', function(e) {
        const icons = document.querySelectorAll('.camera-icon');
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;

        icons.forEach((icon, index) => {
            const speed = (index + 1) * 0.3;
            const moveX = (x - 0.5) * speed * 40;
            const moveY = (y - 0.5) * speed * 40;
            icon.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    });

    // Set cookie to remember user visited (7 days)
    document.querySelectorAll('.btn, .skip-link').forEach(link => {
        link.addEventListener('click', function() {
            document.cookie = "visited=true; max-age=" + (60*60*24*7) + "; path=/";
        });
    });

    setTimeout(function() {
        const visited = document.cookie.split('; ').find(row => row.startsWith('visited='));
        if (!visited) {
            window.location.href = '/Kislap/index.php';
        }
    }, 30000);
</script>
</body>
</html>