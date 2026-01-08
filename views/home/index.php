<?php require __DIR__ . '/../shared/navbar.php'; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kislap Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/index.css" type="text/css">
</head>

<body>

<section class="hero-section">
    <div class="hero-container">
        <!-- Left Content -->
        <div class="hero-content">
            <h1 class="hero-title">Round off your photography journey</h1>
            <p class="hero-description">
                Round off your photography journey with opportunities that matter. Whether you're behind the lens or in front of it, our platform helps you connect, collaborate, and create lasting stories.
            </p>
            <a href="/Kislap/index.php?controller=Browse&action=browse" class="hero-cta">
                <span>Book Now</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <!-- Right Slider -->
        <div class="hero-slider">
            <div class="slider-container">
                <div class="slides-wrapper" id="slidesWrapper">
                    <div class="slide" style="background-image: url('public/images/homepages/image1.avif')"></div>
                    <div class="slide" style="background-image: url('public/images/homepages/image2.avif')"></div>
                    <div class="slide" style="background-image: url('public/images/homepages/image3.avif')"></div>
                    <div class="slide" style="background-image: url('public/images/homepages/image4.avif')"></div>
                    <div class="slide" style="background-image: url('public/images/homepages/image5.avif')"></div>
                </div>

                <!-- Navigation Arrows -->
                <div class="slider-nav prev" id="prevBtn">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="slider-nav next" id="nextBtn">
                    <i class="fas fa-chevron-right"></i>
                </div>

                <!-- Indicators -->
                <div class="slider-indicators">
                    <div class="indicator active" data-slide="0"></div>
                    <div class="indicator" data-slide="1"></div>
                    <div class="indicator" data-slide="2"></div>
                    <div class="indicator" data-slide="3"></div>
                    <div class="indicator" data-slide="4"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="features-container">
        <div class="features-header">
            <h2 class="features-title">Why Choose Kislap?</h2>
            <p class="features-subtitle">
                Discover the perfect platform where photographers and clients connect seamlessly to create stunning visual stories.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="feature-title">Find Perfect Matches</h3>
                <p class="feature-description">
                    Our advanced matching system connects you with photographers who specialize in your specific needs and style preferences.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">User Friendly</h3>
                <p class="feature-description">
                    Escrow services protect both photographers and clients by holding the payment safely until the job is done, ensuring everyone feels secure and confident throughout the booking process.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="feature-title">Quality Guarantee</h3>
                <p class="feature-description">
                    All photographers are verified professionals with portfolios and reviews to ensure you get exceptional results.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="feature-title">Quick Booking</h3>
                <p class="feature-description">
                    Book sessions instantly with real-time availability and automated scheduling that works around your timeline.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="feature-title">Mobile Ready</h3>
                <p class="feature-description">
                    Manage bookings, communicate, and track projects on-the-go with our fully responsive mobile experience.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="feature-title">24/7 Support</h3>
                <p class="feature-description">
                    Get help whenever you need it with our dedicated support team available around the clock for assistance.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number">10K+</span>
                <span class="stat-label">Active Photographers</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">50K+</span>
                <span class="stat-label">Sessions Completed</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">98%</span>
                <span class="stat-label">Satisfaction Rate</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">24hr</span>
                <span class="stat-label">Average Response</span>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="testimonials-container">
        <div class="testimonials-header">
            <h2 class="testimonials-title">What Our Community Says</h2>
            <p class="testimonials-subtitle">
                Real experiences from photographers and clients who've found success on Kislap.
            </p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "Kislap transformed my photography business. I've booked more sessions in 3 months than I did the entire previous year. The platform is incredibly easy to use."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">S</div>
                    <div class="author-info">
                        <h4>Sarah Martinez</h4>
                        <span>Wedding Photographer</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "Finding the perfect photographer for our product launch was effortless. The quality and professionalism exceeded our expectations completely."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">M</div>
                    <div class="author-info">
                        <h4>Michael Chen</h4>
                        <span>Business Owner</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <p class="testimonial-quote">
                    "As a client, I love how transparent the process is. Clear pricing, amazing portfolios, and the booking system is so smooth and professional."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">E</div>
                    <div class="author-info">
                        <h4>Emily Rodriguez</h4>
                        <span>Event Coordinator</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Freelancer Hero Section -->
<section class="freelancer-section">
    <div class="freelancer-container">
        <div class="freelancer-content">
            <!-- Left Side - Become Freelancer -->
            <div class="freelancer-left">
                <h2 class="freelancer-title">Become a Freelancer</h2>
                <div class="camera-image"></div>
            </div>

            <!-- Center Button -->
            <div class="freelancer-center">
                <a href="/Kislap/index.php?controller=Application&action=registration" class="apply-btn">
                    <span>Apply Now</span>
                    <i class="fas fa-camera"></i>
                </a>
            </div>

            <!-- Right Side - Work With Us -->
            <div class="freelancer-right">
                <h2 class="work-title">Want to work with us?</h2>
                <p class="work-description">
                    Are you a talented photographer looking for freelance opportunities? Join our growing community of professionals and get connected with clients who need your skills.
                </p>
            </div>
        </div>
    </div>
</section>
</body>

<script src="/Kislap/public/js/slides.js" defer></script>
</html>