<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sample data for demonstration - Replace with actual database data
$photographers = [
        [
                'photographer_id' => 1,
                'business_name' => 'Capture Moments Studio',
                'description' => 'Professional wedding and event photographer with over 10 years of experience. Specializing in candid shots and creative compositions that tell your story.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Capture+Moments&background=ff6b00&color=000&size=200',
                'category' => 'event',
                'rating' => 4.8,
                'reviews_count' => 127,
                'price_range' => '₱15,000 - ₱35,000',
                'location' => 'Manila, Philippines',
                'years_experience' => 10,
                'is_featured' => true,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1519741497674-611481863552?w=800',
                        'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=400',
                        'https://images.unsplash.com/photo-1606216794074-735e91aa2c92?w=400',
                        'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=400'
                ]
        ],
        [
                'photographer_id' => 2,
                'business_name' => 'Portrait Perfection',
                'description' => 'Specializing in portrait photography for individuals and families. Creating timeless images that capture your personality and essence.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Portrait+Perfection&background=ff8533&color=000&size=200',
                'category' => 'portrait',
                'rating' => 4.9,
                'reviews_count' => 89,
                'price_range' => '₱8,000 - ₱20,000',
                'location' => 'Quezon City, Philippines',
                'years_experience' => 7,
                'is_featured' => false,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800',
                        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
                        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400'
                ]
        ],
        [
                'photographer_id' => 3,
                'business_name' => 'Urban Lens Productions',
                'description' => 'Commercial and product photography for businesses. Helping brands showcase their products with stunning visuals that drive sales.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Urban+Lens&background=ffa500&color=000&size=200',
                'category' => 'product',
                'rating' => 4.7,
                'reviews_count' => 156,
                'price_range' => '₱12,000 - ₱40,000',
                'location' => 'Makati, Philippines',
                'years_experience' => 12,
                'is_featured' => true,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800',
                        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
                        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',
                        'https://images.unsplash.com/photo-1560343090-f0409e92791a?w=400'
                ]
        ],
        [
                'photographer_id' => 4,
                'business_name' => 'Lifestyle Stories',
                'description' => 'Lifestyle photographer capturing authentic moments in everyday life. From family sessions to personal branding shoots.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Lifestyle+Stories&background=ff7f00&color=000&size=200',
                'category' => 'lifestyle',
                'rating' => 4.6,
                'reviews_count' => 73,
                'price_range' => '₱10,000 - ₱25,000',
                'location' => 'Pasig, Philippines',
                'years_experience' => 5,
                'is_featured' => false,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=800',
                        'https://images.unsplash.com/photo-1476234251651-f353703a034d?w=400',
                        'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=400'
                ]
        ],
        [
                'photographer_id' => 5,
                'business_name' => 'Snap & Smile Photobooth',
                'description' => 'Premium photobooth services for weddings, birthdays, and corporate events. Unlimited prints with custom templates and props.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Snap+Smile&background=ff6b00&color=000&size=200',
                'category' => 'photobooth',
                'rating' => 4.8,
                'reviews_count' => 201,
                'price_range' => '₱8,000 - ₱15,000',
                'location' => 'Taguig, Philippines',
                'years_experience' => 6,
                'is_featured' => false,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800',
                        'https://images.unsplash.com/photo-1464047736614-af63643285bf?w=400',
                        'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=400'
                ]
        ],
        [
                'photographer_id' => 6,
                'business_name' => 'Creative Vision Arts',
                'description' => 'Conceptual and artistic photography pushing creative boundaries. Perfect for editorial shoots, fashion, and unique artistic projects.',
                'profile_picture' => 'https://ui-avatars.com/api/?name=Creative+Vision&background=ff8533&color=000&size=200',
                'category' => 'creative',
                'rating' => 4.9,
                'reviews_count' => 94,
                'price_range' => '₱20,000 - ₱50,000',
                'location' => 'Baguio, Philippines',
                'years_experience' => 8,
                'is_featured' => true,
                'portfolio_images' => [
                        'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=800',
                        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=400',
                        'https://images.unsplash.com/photo-1496345875659-11f7dd282d1d?w=400',
                        'https://images.unsplash.com/photo-1509631179647-0177331693ae?w=400',
                        'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400'
                ]
        ]
];

$categories = [
        'all' => 'All Photographers',
        'event' => 'Event Photographers',
        'portrait' => 'Portrait Photographers',
        'product' => 'Product Photographers',
        'lifestyle' => 'Lifestyle Photographers',
        'photobooth' => 'Photobooth Providers',
        'creative' => 'Creative/Conceptual'
];

// Pagination
$currentPage = $_GET['page'] ?? 1;
$totalPages = 3;
$totalPhotographers = count($photographers);

// Filters
$selectedCategory = $_GET['category'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'featured';
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
<?php  require __DIR__ . '/../shared/navbar.php'; ?>

<div class="browse-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Browse Photographers</h1>
        <p>Discover talented photographers for your next project</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="search-filter-bar">
        <form action="" method="GET" class="search-form">
            <div class="search-group">
                <i class="fas fa-search"></i>
                <input type="text"
                       name="search"
                       placeholder="Search by name, location, or style..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
            </div>

            <div class="filter-group">
                <div class="custom-select">
                    <select name="category" onchange="this.form.submit()">
                        <option value="" disabled>Select Category</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo $selectedCategory === $key ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>

                <div class="custom-select">
                    <select name="sort" onchange="this.form.submit()">
                        <option value="" disabled>Sort By</option>
                        <option value="featured" <?php echo $sortBy === 'featured' ? 'selected' : ''; ?>>Featured</option>
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
            <span><?php echo $totalPhotographers; ?> photographers</span>
        </div>
    </div>

    <!-- Photographers Grid -->
    <div class="photographers-grid">
        <?php if (empty($photographers)): ?>
            <div class="empty-state">
                <i class="fas fa-camera-retro"></i>
                <h3>No photographers found</h3>
                <p>Try adjusting your search or filters to find more results</p>
                <a href="?" class="btn-reset">
                    <i class="fas fa-redo"></i> Clear Filters
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($photographers as $photographer): ?>
                <?php
                $photogId = $photographer['photographer_id'] ?? 0;
                $businessName = $photographer['business_name'] ?? 'Unknown';
                $description = $photographer['description'] ?? 'No description available';
                $profilePicture = $photographer['profile_picture'] ?? '';
                $category = $photographer['category'] ?? 'general';
                $rating = $photographer['rating'] ?? 0;
                $reviewsCount = $photographer['reviews_count'] ?? 0;
                $priceRange = $photographer['price_range'] ?? 'Contact for pricing';
                $location = $photographer['location'] ?? 'Philippines';
                $yearsExperience = $photographer['years_experience'] ?? 0;
                $isFeatured = $photographer['is_featured'] ?? false;
                $portfolioImages = $photographer['portfolio_images'] ?? [];

                $shortDescription = strlen($description) > 120
                        ? substr($description, 0, 120) . '...'
                        : $description;
                ?>

                <div class="photographer-card">
                    <?php if ($isFeatured): ?>
                        <div class="featured-badge">
                            <i class="fas fa-crown"></i> Featured
                        </div>
                    <?php endif; ?>

                    <!-- Portfolio Preview -->
                    <div class="portfolio-preview">
                        <?php if (!empty($portfolioImages)): ?>
                            <div class="portfolio-main">
                                <img src="<?php echo htmlspecialchars($portfolioImages[0]); ?>" alt="Portfolio">
                            </div>
                            <?php if (count($portfolioImages) > 1): ?>
                                <div class="portfolio-thumbnails">
                                    <?php for ($i = 1; $i <= min(3, count($portfolioImages) - 1); $i++): ?>
                                        <div class="thumbnail">
                                            <img src="<?php echo htmlspecialchars($portfolioImages[$i]); ?>" alt="Portfolio">
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
                                        <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="<?php echo htmlspecialchars($businessName); ?>">
                                    <?php else: ?>
                                        <div class="profile-placeholder">
                                            <?php echo strtoupper(substr($businessName, 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($businessName); ?></h3>
                                    <span class="category-tag"><?php echo htmlspecialchars(ucfirst($category)); ?></span>
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
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($priceRange); ?></span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="?controller=Browse&action=viewProfile&id=<?php echo $photogId; ?>" class="btn-secondary">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <button class="btn-primary" onclick="bookPhotographer(<?php echo $photogId; ?>)">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"
                   class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>

            <div class="page-numbers">
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $currentPage + 2);

                if ($startPage > 1): ?>
                    <a href="?page=1&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"
                       class="page-number">1</a>
                    <?php if ($startPage > 2): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"
                       class="page-number <?php echo $i == $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <span class="ellipsis">...</span>
                    <?php endif; ?>
                    <a href="?page=<?php echo $totalPages; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"
                       class="page-number"><?php echo $totalPages; ?></a>
                <?php endif; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&category=<?php echo $selectedCategory; ?>&search=<?php echo urlencode($searchQuery); ?>&sort=<?php echo $sortBy; ?>"
                   class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function bookPhotographer(photographerId) {
        window.location.href = `?controller=Booking&action=create&photographer_id=${photographerId}`;
    }
</script>
</body>
</html>