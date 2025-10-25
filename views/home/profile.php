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

// Get specialty categories from database
$specialtyCategories = $allSpecialties ?? [];
$categoryLabel = $specialtyCategories[$specialty] ?? ucwords(str_replace('_', ' ', $specialty));

// Use real statistics from database with fallbacks
$stats = $workerStats ?? [];
$totalBookings = intval($stats['total_bookings'] ?? $photographer['total_bookings'] ?? 0);
$totalEarnings = floatval($stats['total_earnings'] ?? $photographer['total_earnings'] ?? 0);
$rating = floatval($stats['average_rating'] ?? $photographer['average_rating'] ?? 0);
$reviewsCount = intval($stats['total_ratings'] ?? $photographer['total_ratings'] ?? 0);
$activeConversations = intval($stats['active_conversations'] ?? 0);
$completedBookings = intval($stats['completed_bookings'] ?? 0);
$pendingBookings = intval($stats['pending_bookings'] ?? 0);

// Portfolio images from recent work
$portfolioImages = [];
if (!empty($recentWork)) {
    foreach ($recentWork as $work) {
        if (!empty($work['image_path'])) {
            $portfolioImages[] = $work['image_path'];
        }
    }
}

// Calculate price range based on packages and earnings
$priceRange = '₱';
$priceDescription = 'Contact for pricing';
$priceRangeText = 'Contact for pricing';

if (!empty($packages)) {
    $prices = array_column($packages, 'price');
    $minPrice = min($prices);
    $maxPrice = max($prices);
    
    // Create actual price range text
    if ($minPrice == $maxPrice) {
        $priceRangeText = '₱' . number_format($minPrice, 0);
    } else {
        $priceRangeText = '₱' . number_format($minPrice, 0) . ' - ₱' . number_format($maxPrice, 0);
    }
    
    // Set price range symbols based on max price
    if ($maxPrice >= 50000) {
        $priceRange = '₱₱₱₱';
        $priceDescription = 'Premium Pricing';
    } elseif ($maxPrice >= 25000) {
        $priceRange = '₱₱₱';
        $priceDescription = 'High-End Pricing';
    } elseif ($maxPrice >= 10000) {
        $priceRange = '₱₱';
        $priceDescription = 'Mid-Range Pricing';
    } else {
        $priceRange = '₱';
        $priceDescription = 'Affordable Pricing';
    }
} elseif ($totalEarnings > 0 && $totalBookings > 0) {
    // Fallback to earnings-based calculation
    $avgEarningsPerBooking = $totalEarnings / $totalBookings;
    $priceRangeText = '₱' . number_format($avgEarningsPerBooking * 0.8, 0) . ' - ₱' . number_format($avgEarningsPerBooking * 1.2, 0);
    
    if ($avgEarningsPerBooking >= 50000) {
        $priceRange = '₱₱₱₱';
        $priceDescription = 'Premium Pricing';
    } elseif ($avgEarningsPerBooking >= 25000) {
        $priceRange = '₱₱₱';
        $priceDescription = 'High-End Pricing';
    } elseif ($avgEarningsPerBooking >= 10000) {
        $priceRange = '₱₱';
        $priceDescription = 'Mid-Range Pricing';
    } else {
        $priceRange = '₱';
        $priceDescription = 'Affordable Pricing';
    }
}

// Featured status based on real metrics
$isFeatured = ($completedBookings >= 5 && $rating >= 4.0 && $reviewsCount >= 3);

// Format joined date
$joinedYear = $joinedDate ? date('Y', strtotime($joinedDate)) : date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($businessName); ?> - Photographer Profile | Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/viewProfile.css" type="text/css">
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
                            <span class="stat-number price-range"><?php echo $priceRangeText; ?></span>
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
                <?php if (isset($currentUser) && $currentUser): ?>
                    <button class="btn-book-primary" onclick="bookPhotographer(<?php echo $photogId; ?>)">
                        <i class="fas fa-calendar-<?php echo $hasBookedBefore ? 'plus' : 'check'; ?>"></i>
                        <?php echo $hasBookedBefore ? 'Book Again' : 'Book Now'; ?>
                    </button>
                <?php else: ?>
                    <button class="btn-book-primary" onclick="redirectToLogin()">
                        <i class="fas fa-calendar-check"></i>
                        Book Now
                    </button>
                <?php endif; ?>
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
                    <span class="image-count"><?php echo count($recentWork); ?> images</span>
                </div>
                <div class="section-content">
                    <?php if (!empty($recentWork)): ?>
                        <div class="portfolio-grid">
                            <?php foreach ($recentWork as $index => $work): ?>
                                <div class="portfolio-item" onclick="openLightbox(<?php echo $index; ?>)">
                                    <img src="<?php echo htmlspecialchars($work['image_path']); ?>"
                                         alt="<?php echo htmlspecialchars($work['title'] ?? 'Portfolio image'); ?>"
                                         loading="lazy"
                                         onerror="this.parentElement.style.display='none'">
                                    <div class="portfolio-overlay">
                                        <i class="fas fa-search-plus"></i>
                                        <?php if ($work['title']): ?>
                                            <span class="portfolio-title"><?php echo htmlspecialchars($work['title']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($portfolioImages)): ?>
                        <!-- Fallback to old portfolio format if available -->
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
                            // Use real rating distribution from database
                            $distribution = [
                                5 => $ratingStats['five_star'] ?? 0,
                                4 => $ratingStats['four_star'] ?? 0,
                                3 => $ratingStats['three_star'] ?? 0,
                                2 => $ratingStats['two_star'] ?? 0,
                                1 => $ratingStats['one_star'] ?? 0
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
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <?php if ($review['user_photo']): ?>
                                                    <img src="<?php echo htmlspecialchars($review['user_photo']); ?>" alt="Reviewer">
                                                <?php else: ?>
                                                    <?php echo strtoupper(substr($review['user_name'], 0, 2)); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($review['user_name']); ?></h4>
                                                <div class="review-stars">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                    </div>
                                    <?php if ($review['review']): ?>
                                        <p class="review-text">
                                            <?php echo nl2br(htmlspecialchars($review['review'])); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-reviews">
                                <i class="fas fa-comment-slash"></i>
                                <p>No reviews yet. Be the first to book and review!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Packages Section -->
            <?php if (!empty($packages)): ?>
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-box"></i> Packages & Pricing</h2>
                    <span class="package-count"><?php echo count($packages); ?> packages</span>
                </div>
                <div class="section-content">
                    <div class="packages-grid">
                        <?php foreach ($packages as $package): ?>
                            <div class="package-card">
                                <div class="package-header">
                                    <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                    <div class="package-price">₱<?php echo number_format($package['price'], 2); ?></div>
                                </div>
                                <div class="package-details">
                                    <?php if ($package['duration']): ?>
                                        <div class="package-duration">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $package['duration']; ?> hours</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($package['description']): ?>
                                        <p class="package-description">
                                            <?php echo nl2br(htmlspecialchars($package['description'])); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Package features based on actual database columns -->
                                    <div class="package-features">
                                        <?php if ($package['photo_count']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-camera"></i>
                                                <span><?php echo $package['photo_count']; ?> photos</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($package['delivery_days']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-truck"></i>
                                                <span><?php echo $package['delivery_days']; ?> days delivery</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($package['duration']): ?>
                                            <div class="feature-item">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $package['duration']; ?> hour session</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button class="btn-select-package" onclick="selectPackage(<?php echo $package['package_id']; ?>, '<?php echo htmlspecialchars($package['name']); ?>', <?php echo $package['price']; ?>)">
                                    <i class="fas fa-check-circle"></i>
                                    Select Package
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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
                                <span class="stat-value"><?php echo $completedBookings; ?></span>
                                <span class="stat-label">Completed Bookings</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-clock"></i>
                            <div>
                                <span class="stat-value"><?php echo $pendingBookings; ?></span>
                                <span class="stat-label">Active Bookings</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-image"></i>
                            <div>
                                <span class="stat-value"><?php echo count($recentWork); ?></span>
                                <span class="stat-label">Portfolio Works</span>
                            </div>
                        </div>
                        <div class="quick-stat">
                            <i class="fas fa-star"></i>
                            <div>
                                <span class="stat-value"><?php echo number_format($rating, 1); ?></span>
                                <span class="stat-label">Average Rating</span>
                            </div>
                        </div>
                        <?php if (!empty($packages)): ?>
                        <div class="quick-stat">
                            <i class="fas fa-box"></i>
                            <div>
                                <span class="stat-value"><?php echo count($packages); ?></span>
                                <span class="stat-label">Available Packages</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="quick-stat">
                            <i class="fas fa-comments"></i>
                            <div>
                                <span class="stat-value"><?php echo $activeConversations; ?></span>
                                <span class="stat-label">Active Conversations</span>
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
    const portfolioImages = <?php echo json_encode(array_column($recentWork, 'image_path')); ?>;
    const portfolioTitles = <?php echo json_encode(array_column($recentWork, 'title')); ?>;
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
        <?php if (isset($_SESSION['user'])): ?>
        // Use the new startBooking action for better booking flow
        window.location.href = `?controller=Chat&action=startBooking&worker_id=${photographerId}`;
        <?php else: ?>
        alert('Please log in to book this photographer');
        window.location.href = `?controller=Auth&action=login&redirect=${encodeURIComponent('?controller=Chat&action=startBooking&worker_id=' + photographerId)}`;
        <?php endif; ?>
    }

    // Login redirect function
    function redirectToLogin() {
        alert('Please log in to book this photographer');
        window.location.href = '?controller=Auth&action=login';
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

    // Package selection function
    function selectPackage(packageId, packageName, packagePrice) {
        <?php if (isset($_SESSION['user'])): ?>
        // Store package selection and redirect to booking
        sessionStorage.setItem('selectedPackage', JSON.stringify({
            id: packageId,
            name: packageName,
            price: packagePrice
        }));
        window.location.href = `?controller=Chat&action=startBooking&worker_id=<?php echo $photogId; ?>&package_id=${packageId}`;
        <?php else: ?>
        alert('Please log in to select a package');
        window.location.href = '?controller=Auth&action=login';
        <?php endif; ?>
    }

    // Enhanced lightbox with titles
    function updateLightboxImage() {
        const img = document.getElementById('lightbox-image');
        const title = document.getElementById('lightbox-title');
        
        if (img && portfolioImages[currentImageIndex]) {
            img.src = portfolioImages[currentImageIndex];
            if (title && portfolioTitles[currentImageIndex]) {
                title.textContent = portfolioTitles[currentImageIndex];
                title.style.display = 'block';
            } else if (title) {
                title.style.display = 'none';
            }
        }
        
        const current = document.getElementById('lightbox-current');
        if (current) {
            current.textContent = currentImageIndex + 1;
        }
    }

    // Smooth scroll for back button
    document.querySelector('.back-btn')?.addEventListener('click', function(e) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>
</body>
</html>