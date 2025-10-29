<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the view uses the $workers variable passed from the controller
$photographers = $workers ?? [];

// Categories mapping (specialty field values)
$categories = [
        'all' => 'All Categories',
        'event' => 'Event Photography',
        'portrait' => 'Portrait Photography',
        'product' => 'Product Photography',
        'lifestyle' => 'Lifestyle Photography',
        'photobooth' => 'Photobooth Services',
        'creative' => 'Creative/Conceptual'
];

// Merge with available specialties from database if needed
if (isset($availableSpecialties) && !empty($availableSpecialties)) {
    foreach ($availableSpecialties as $specialty) {
        if (!isset($categories[$specialty])) {
            $categories[$specialty] = ucwords(str_replace('_', ' ', $specialty));
        }
    }
}

// Filters (from Controller)
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalPages = $totalPages ?? 1;
$totalPhotographers = $totalWorkers ?? count($photographers);

$selectedCategory = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'rating';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Photographers - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/browse.css" type="text/css">
</head>
<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="browse-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Browse Photographers</h1>
        <p>Discover talented photographers for your next project</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
        <form action="?controller=Browse&action=browse" method="GET" class="search-form" id="filterForm">
            <!-- Preserve controller and action -->
            <input type="hidden" name="controller" value="Browse">
            <input type="hidden" name="action" value="browse">

            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by name, location, or specialty..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>

            <div class="filter-group">
                <div class="custom-select">
                    <select name="category" onchange="this.form.submit()">
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>"
                                    <?php echo $selectedCategory === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>

                <div class="custom-select">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                        <option value="reviews" <?php echo $sortBy === 'reviews' ? 'selected' : ''; ?>>Most Reviews</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </form>

        <div class="results-count">
            <i class="fas fa-users"></i>
            <span><?php echo $totalPhotographers; ?> photographer<?php echo $totalPhotographers !== 1 ? 's' : ''; ?> found</span>
        </div>
    </div>

    <!-- Photographers Grid -->
    <div class="photographers-grid">
        <?php if (empty($photographers)): ?>
            <div class="empty-state">
                <i class="fas fa-camera-retro"></i>
                <h3>No photographers found</h3>
                <p>Try adjusting your search or filters to find more results</p>
                <a href="?controller=Browse&action=browse" class="btn-reset">
                    <i class="fas fa-redo"></i> Clear Filters
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($photographers as $photographer): ?>
                <?php
                // --- MAPPING WORKER TABLE COLUMNS ---
                $photogId = $photographer['worker_id'] ?? 0;

                // Full name composition
                $fullName = trim(
                        ($photographer['firstName'] ?? '') . ' ' .
                        ($photographer['middleName'] ?? '') . ' ' .
                        ($photographer['lastName'] ?? '')
                );
                $businessName = $photographer['display_name'] ?? $fullName;

                // Details
                $description = $photographer['bio'] ?? 'No bio available';
                $profilePicture = $photographer['profile_photo'] ?? '';
                $location = $photographer['address'] ?? 'Philippines';
                $yearsExperience = $photographer['experience_years'] ?? 0;

                // Ratings
                $rating = floatval($photographer['average_rating'] ?? 0);
                $reviewsCount = intval($photographer['total_ratings'] ?? 0);
                $totalBookings = intval($photographer['total_bookings'] ?? 0);

                // Category
                $categoryKey = $photographer['specialty'] ?? 'general';
                $categoryLabel = $categories[$categoryKey] ?? ucwords(str_replace('_', ' ', $categoryKey));

                // Remove featured status as it's no longer needed

                // Price range based on packages or earnings
                $priceRange = 'Contact for pricing';
                
                if ($photographer['has_packages'] && $photographer['min_package_price']) {
                    $minPrice = floatval($photographer['min_package_price']);
                    $maxPrice = floatval($photographer['max_package_price']);
                    
                    if ($minPrice == $maxPrice) {
                        $priceRange = '₱' . number_format($minPrice, 0);
                    } else {
                        $priceRange = '₱' . number_format($minPrice, 0) . ' - ₱' . number_format($maxPrice, 0);
                    }
                } else {
                    // Fallback to earnings-based estimation
                    $totalEarnings = floatval($photographer['total_earnings'] ?? 0);
                    $totalBookings = intval($photographer['total_bookings'] ?? 0);
                    
                    if ($totalEarnings > 0 && $totalBookings > 0) {
                        $avgPrice = $totalEarnings / $totalBookings;
                        $minEstimate = $avgPrice * 0.8;
                        $maxEstimate = $avgPrice * 1.2;
                        $priceRange = '₱' . number_format($minEstimate, 0) . ' - ₱' . number_format($maxEstimate, 0);
                    }
                }

                // Portfolio images (array of image paths)
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

                // Shorten description
                $shortDescription = strlen($description) > 120
                        ? substr($description, 0, 120) . '...'
                        : $description;
                ?>

                <div class="photographer-card" data-photographer-id="<?php echo $photogId; ?>"><?php // Removed featured badge as requested ?>

                    <!-- Portfolio Preview -->
                    <div class="portfolio-preview">
                        <?php if (!empty($portfolioImages)): ?>
                            <div class="portfolio-main">
                                <?php 
                                $mainImagePath = $portfolioImages[0];
                                if (!str_starts_with($mainImagePath, '/Kislap/') && !str_starts_with($mainImagePath, 'http')) {
                                    $mainImagePath = '/Kislap/' . $mainImagePath;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($mainImagePath); ?>"
                                     alt=""
                                     onerror="this.parentElement.innerHTML='<div class=\'no-portfolio\'><i class=\'fas fa-camera\'></i><p>Image unavailable</p></div>'">
                            </div>
                            <?php if (count($portfolioImages) > 1): ?>
                                <div class="portfolio-thumbnails">
                                    <?php for ($i = 1; $i <= min(3, count($portfolioImages) - 1); $i++): ?>
                                        <div class="thumbnail">
                                            <?php 
                                            $thumbImagePath = $portfolioImages[$i];
                                            if (!str_starts_with($thumbImagePath, '/Kislap/') && !str_starts_with($thumbImagePath, 'http')) {
                                                $thumbImagePath = '/Kislap/' . $thumbImagePath;
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($thumbImagePath); ?>"
                                                 alt=""
                                                 onerror="this.style.display='none'">
                                        </div>
                                    <?php endfor; ?>
                                    <?php if (count($portfolioImages) > 4): ?>
                                        <div class="thumbnail more">
                                            <span>+<?php echo count($portfolioImages) - 4; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-portfolio">
                                <i class="fas fa-camera"></i>
                                <p>No portfolio images</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Content -->
                    <div class="card-content">
                        <div class="photographer-header">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <?php if ($profilePicture): ?>
                                        <?php 
                                        $profileImagePath = $profilePicture;
                                        if (!str_starts_with($profileImagePath, '/Kislap/') && !str_starts_with($profileImagePath, 'http')) {
                                            $profileImagePath = '/Kislap/' . $profileImagePath;
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($profileImagePath); ?>"
                                             alt="<?php echo htmlspecialchars($businessName); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="profile-placeholder" style="display:none;">
                                            <?php echo strtoupper(substr($businessName, 0, 2)); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="profile-placeholder">
                                            <?php echo strtoupper(substr($businessName, 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($businessName); ?></h3>
                                    <span class="category-tag">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($categoryLabel); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                <span><?php echo number_format($rating, 1); ?></span>
                                <small>(<?php echo $reviewsCount; ?>)</small>
                            </div>
                        </div>

                        <p class="description"><?php echo htmlspecialchars($shortDescription); ?></p>

                        <div class="card-meta">
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($location); ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-briefcase"></i>
                                <span><?php echo $yearsExperience; ?>+ years</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span><?php echo htmlspecialchars($priceRange); ?></span>
                            </div>
                        </div>

                        <div class="card-stats">
                            <div class="stat-item">
                                <i class="fas fa-calendar-check"></i>
                                <span><?php echo $totalBookings; ?> bookings</span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="?controller=Browse&action=viewProfile&id=<?php echo $photogId; ?>"
                               class="btn-secondary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>

                            <?php if (isset($_SESSION['user'])): ?>
                                <?php if (!empty($photographer['has_incomplete_booking'])): ?>
                                    <!-- Continue Existing Booking -->
                                    <a href="index.php?controller=Chat&action=view&conversation_id=<?php echo $photographer['incomplete_conversation_id']; ?>"
                                       class="btn-primary">
                                        <i class="fas fa-comments"></i> Continue Booking
                                    </a>
                                <?php elseif (!empty($photographer['has_completed_booking'])): ?>
                                    <!-- Book Again (replaces Book Now for users with completed bookings) -->
                                    <a href="index.php?controller=Chat&action=newBooking&worker_id=<?php echo $photogId; ?>"
                                       class="btn-primary">
                                        <i class="fas fa-redo"></i> Book Again
                                    </a>
                                <?php else: ?>
                                    <!-- Start New Booking (for first-time users) -->
                                    <a href="index.php?controller=Chat&action=newBooking&worker_id=<?php echo $photogId; ?>"
                                       class="btn-primary">
                                        <i class="fas fa-calendar-check"></i> Book Now
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Not logged in - show login prompt -->
                                <a href="?controller=Auth&action=login&redirect=<?php echo urlencode('?controller=Chat&action=newBooking&worker_id=' . $photogId); ?>"
                                   class="btn-primary">
                                    <i class="fas fa-calendar-check"></i> Book Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            // Build base URL with current filters
            $baseUrl = "?controller=Browse&action=browse&category=" . urlencode($selectedCategory) .
                    "&search=" . urlencode($searchQuery) . "&sort=" . urlencode($sortBy);
            ?>

            <?php if ($currentPage > 1): ?>
                <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage - 1; ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>

            <div class="page-numbers">
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1): ?>
                    <a href="<?php echo $baseUrl; ?>&page=1" class="page-number">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>"
                       class="page-number <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                    <a href="<?php echo $baseUrl; ?>&page=<?php echo $totalPages; ?>"
                       class="page-number"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
                <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage + 1; ?>" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function bookPhotographer(photographerId) {
        <?php if (isset($_SESSION['user'])): ?>
        // User is logged in, start AI chat
        window.location.href = `?controller=Chat&action=view&worker_id=${photographerId}`;
        <?php else: ?>
        // User not logged in, redirect to login
        alert('Please log in to book a photographer');
        window.location.href = `?controller=Auth&action=login&redirect=${encodeURIComponent('?controller=Chat&action=view&worker_id=' + photographerId)}`;
        <?php endif; ?>
    }


    // Optional: Add loading state for form submissions
    document.getElementById('filterForm')?.addEventListener('submit', function() {
        const submitBtn = this.querySelector('.search-btn');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            submitBtn.disabled = true;
        }
    });
</script>
</body>
</html>
