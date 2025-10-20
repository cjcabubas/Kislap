<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$photographers = $workers ?? [];

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