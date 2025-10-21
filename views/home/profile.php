<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure worker data is passed from controller
$photographer = $worker ?? null;

if (!$photographer) {
    header('Location: ?controller=Browse&action=browse');
    exit;
}

// Extract photographer details
$photogId = $photographer['worker_id'] ?? 0;
$fullName = trim(
    ($photographer['firstName'] ?? '') . ' ' .
    ($photographer['middleName'] ?? '') . ' ' .
    ($photographer['lastName'] ?? '')
);
$businessName = $photographer['display_name'] ?? $fullName;
$bio = $photographer['bio'] ?? 'No bio available';
$profilePicture = $photographer['profile_photo'] ?? '';
$location = $photographer['address'] ?? 'Philippines';
$yearsExperience = $photographer['experience_years'] ?? 0;
$rating = floatval($photographer['rating_average'] ?? 0);
$reviewsCount = intval($photographer['total_ratings'] ?? 0);
$totalBookings = intval($photographer['total_bookings'] ?? 0);
$specialty = $photographer['specialty'] ?? 'general';
$totalEarnings = floatval($photographer['total_earnings'] ?? 0);
$email = $photographer['email'] ?? '';
$phone = $photographer['contactNumber'] ?? '';
$joinedDate = $photographer['created_at'] ?? '';

// Categories mapping
$categories = [
    'event' => 'Event Photography',
    'portrait' => 'Portrait Photography',
    'product' => 'Product Photography',
    'lifestyle' => 'Lifestyle Photography',
    'photobooth' => 'Photobooth Services',
    'creative' => 'Creative/Conceptual'
];
$categoryLabel = $categories[$specialty] ?? ucwords(str_replace('_', ' ', $specialty));

// Portfolio images
$portfolioImages = [];
if (isset($photographer['portfolio_images']) && is_array($photographer['portfolio_images'])) {
    foreach ($photographer['portfolio_images'] as $img) {
        if (is_array($img) && isset($img['image_path'])) {
            $portfolioImages[] = $img['image_path'];
        } elseif (is_string($img)) {
            $portfolioImages[] = $img;
        }
    }
}

// Price range calculation
if ($totalEarnings > 100000) {
    $priceRange = '₱₱₱₱';
    $priceDescription = 'Premium Pricing';
} elseif ($totalEarnings > 50000) {
    $priceRange = '₱₱₱';
    $priceDescription = 'High-End Pricing';
} elseif ($totalEarnings > 10000) {
    $priceRange = '₱₱';
    $priceDescription = 'Mid-Range Pricing';
} else {
    $priceRange = '₱';
    $priceDescription = 'Affordable Pricing';
}

// Featured status
$isFeatured = ($totalBookings >= 10 && $rating >= 4.5);

// Format joined date
$joinedYear = date('Y', strtotime($joinedDate));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($businessName); ?> - Photographer Profile | Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/viewP   rofile.css" type="text/css">
</head>
<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="profile-container">
    <!-- Back Button -->
    <div class="back-navigation">
        <a href="?controller=Browse&action=browse" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Browse</span>
        </a>
    </div>

    <!-- Profile Header Section -->
    <div class="profile-header">
        <div class="profile-header-content">
            <div class="profile-main-info">
                <div class="profile-avatar-large">
                    <?php if ($profilePicture): ?>
                        <img src="<?php echo htmlspecialchars($profilePicture); ?>"
                             alt="<?php echo htmlspecialchars($businessName); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-placeholder" style="display:none;">
                            <?php echo strtoupper(substr($businessName, 0, 2)); ?>
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($businessName, 0, 2)); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isFeatured): ?>
                        <div class="featured-crown">
                            <i class="fas fa-crown"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-details">
                    <h1 class="profile-name"><?php echo htmlspecialchars($businessName); ?></h1>
                    <p class="profile-specialty">
                        <i class="fas fa-camera"></i>
                        <?php echo htmlspecialchars($categoryLabel); ?>
                    </p>

                    <div class="profile-meta-row">
                        <div class="meta-badge">
                            <i class="fas fa-star"></i>
                            <span><?php echo number_format($rating, 1); ?></span>
                            <small>(<?php echo $reviewsCount; ?> reviews)</small>
                        </div>
                        <div class="meta-badge">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($location); ?></span>
                        </div>
                        <div class="meta-badge">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo $yearsExperience; ?>+ years experience</span>
                        </div>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $totalBookings; ?></span>
                            <span class="stat-label">Total Bookings</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $priceRange; ?></span>
                            <span class="stat-label"><?php echo $priceDescription; ?></span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($portfolioImages); ?></span>
                            <span class="stat-label">Portfolio Images</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-book-primary" onclick="bookPhotographer(<?php echo $photogId; ?>)">
                    <i class="fas fa-calendar-check"></i>
                    Book Now
                </button>
                <button class="btn-message" onclick="messagePhotographer(<?php echo $photogId; ?>)">
                    <i class="fas fa-envelope"></i>
                    Send Message
                </button>
                <button class="btn-share" onclick="shareProfile()">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="profile-content-grid">
        <!-- Left Column -->
        <div class="profile-left-column">
            <!-- About Section -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-user"></i> About</h2>
                </div>
                <div class="section-content">
                    <p class="bio-text"><?php echo nl2br(htmlspecialchars($bio)); ?></p>
                </div>
            </div>

            <!-- Portfolio Section -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-images"></i> Portfolio</h2>
                    <span class="image-count"><?php echo count($portfolioImages); ?> images</span>
                </div>
                <div class="section-content">
                    <?php if (!empty($portfolioImages)): ?>
                        <div class="portfolio-grid">
                            <?php foreach ($portfolioImages as $index => $image): ?>
                                <div class="portfolio-item" onclick="openLightbox(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($image); ?>"
                                         alt="Portfolio image <?php echo $index + 1; ?>"
                                         loading="lazy"
                                         onerror="this.parentElement.style.display='none'">
                                    <div class="portfolio-overlay">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-portfolio">
                            <i class="fas fa-camera"></i>
                            <p>No portfolio images available yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Reviews & Ratings</h2>
                    <span class="review-count"><?php echo $reviewsCount; ?> reviews</span>
                </div>
                <div class="section-content">
                    <div class="rating-overview">
                        <div class="rating-score">
                            <span class="score-number"><?php echo number_format($rating, 1); ?></span>
                            <div class="score-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= round($rating) ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="score-label">Based on <?php echo $reviewsCount; ?> reviews</p>
                        </div>

                        <div class="rating-breakdown">
                            <?php
                            // Generate sample rating distribution (in real app, fetch from database)
                            $distribution = [
                                5 => round($reviewsCount * 0.6),
                                4 => round($reviewsCount * 0.25),
                                3 => round($reviewsCount * 0.1),
                                2 => round($reviewsCount * 0.03),
                                1 => round($reviewsCount * 0.02)
                            ];
                            ?>
                            <?php foreach ($distribution as $stars => $count): ?>
                                <div class="rating-bar-row">
                                    <span class="bar-label"><?php echo $stars; ?> <i class="fas fa-star"></i></span>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: <?php echo $reviewsCount > 0 ? ($count / $reviewsCount * 100) : 0; ?>%"></div>
                                    </div>
                                    <span class="bar-count"><?php echo $count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="reviews-list">
                        <!-- Sample review (in real app, fetch from database) -->
                        <?php if ($reviewsCount > 0): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">JD</div>
                                        <div>
                                            <h4>John Doe</h4>
                                            <div class="review-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="review-date">2 weeks ago</span>
                                </div>
                                <p class="review-text">
                                    Amazing work! Very professional and captured our event perfectly.
                                    Highly recommend for any photography needs.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="no-reviews">
                                <i class="fas fa-comment-slash"></i>
                                <p>No reviews yet. Be the first to book and review!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="profile-right-column">
            <!-- Contact Information -->
            <div class="profile-section sticky-section">
                <div class="section-header">
                    <h2><i class="fas fa-address-card"></i> Contact Information</h2>
                </div>
                <div class="section-content">
                    <div class="contact-list">
                        <?php if ($email): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <span class="contact-label">Email</span>
                                    <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="contact-value">
                                        <?php echo htmlspecialchars($email); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($phone): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <span class="contact-label">Phone</span>
                                    <a href="tel:<?php echo htmlspecialchars($phone); ?>" class="contact-value">
                                        <?php echo htmlspecialchars($phone); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="contact-label">Location</span>
                                <span class="contact-value"><?php echo htmlspecialchars($location); ?></span>
                            </div>
                        </div>

                        <?php if ($joinedDate): ?>
                            <div class="contact-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <span class="contact-label">Member Since</span>
                                    <span class="contact-value"><?php echo $joinedYear; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button class="btn-contact-full" onclick="bookPhotographer(<?php echo $photogId; ?>)">
                        <i class="fas fa-calendar-check"></i>
                        Book This Photographer
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Quick Stats</h2>
                </div>
                <div class="section-content">
                    <div class="quick-stats-list">
                        <div class="quick-stat">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <span class="stat-value"><?php echo $totalBookings; ?></span>
                                <span class="stat-label">Completed Projects</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="stat-value"><?php echo $yearsExperience; ?>+</span>
                                <span class="stat-label">Years Experience</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-image"></i>
                            <div>
                                <span class="stat-value"><?php echo count($portfolioImages); ?></span>
                                <span class="stat-label">Portfolio Images</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-award"></i>
                            <div>
                                <span class="stat-value"><?php echo $isFeatured ? 'Yes' : 'No'; ?></span>
                                <span class="stat-label">Featured Status</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox for Portfolio Images -->
<?php if (!empty($portfolioImages)): ?>
    <div id="lightbox" class="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">
                <i class="fas fa-times"></i>
            </button>
            <button class="lightbox-prev" onclick="changeLightboxImage(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-next" onclick="changeLightboxImage(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <img id="lightbox-image" src="" alt="Portfolio image">
            <div class="lightbox-counter">
                <span id="lightbox-current">1</span> / <span id="lightbox-total"><?php echo count($portfolioImages); ?></span>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Portfolio lightbox functionality
    const portfolioImages = <?php echo json_encode($portfolioImages); ?>;
    let currentImageIndex = 0;

    function openLightbox(index) {
        currentImageIndex = index;
        updateLightboxImage();
        document.getElementById('lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }

    function changeLightboxImage(direction) {
        currentImageIndex += direction;
        if (currentImageIndex < 0) {
            currentImageIndex = portfolioImages.length - 1;
        } else if (currentImageIndex >= portfolioImages.length) {
            currentImageIndex = 0;
        }
        updateLightboxImage();
    }

    function updateLightboxImage() {
        document.getElementById('lightbox-image').src = portfolioImages[currentImageIndex];
        document.getElementById('lightbox-current').textContent = currentImageIndex + 1;
    }

    // Keyboard navigation for lightbox
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('lightbox').classList.contains('active')) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') changeLightboxImage(-1);
            if (e.key === 'ArrowRight') changeLightboxImage(1);
        }
    });

    // Close lightbox on background click
    document.getElementById('lightbox')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeLightbox();
        }
    });

    // Booking function
    function bookPhotographer(photographerId) {
        <?php if (isset($_SESSION['user_id'])): ?>
        window.location.href = `?controller=Booking&action=create&photographer_id=${photographerId}`;
        <?php else: ?>
        alert('Please log in to book this photographer');
        window.location.href = `?controller=Auth&action=login&redirect=?controller=Booking%26action=create%26photographer_id=${photographerId}`;
        <?php endif; ?>
    }

    // Message function
    function messagePhotographer(photographerId) {
        <?php if (isset($_SESSION['user_id'])): ?>
        alert('Messaging feature coming soon!');
        // TODO: Implement messaging
        <?php else: ?>
        alert('Please log in to send a message');
        window.location.href = '?controller=Auth&action=login';
        <?php endif; ?>
    }

    // Share function
    function shareProfile() {
        const url = window.location.href;
        if (navigator.share) {
            navigator.share({
                title: '<?php echo htmlspecialchars($businessName); ?> - Photographer Profile',
                text: 'Check out this photographer on Kislap!',
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Profile link copied to clipboard!');
        }
    }

    // Smooth scroll for back button
    document.querySelector('.back-btn')?.addEventListener('click', function(e) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
</body>
</html>