<?php
// $worker variable is passed from the controller
if (!$worker) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

$specialtyLabels = [
        'event' => 'Event Photography',
        'portrait' => 'Portrait Photography',
        'product' => 'Product Photography',
        'lifestyle' => 'Lifestyle Photography',
        'photobooth' => 'Photobooth Services',
        'creative' => 'Creative/Conceptual'
];

$isEditMode = $isEditMode ?? false;

// Portfolio images (fetched from controller or default to empty)
$existingPortfolio = $existingPortfolio ?? [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditMode ? 'Edit Profile' : 'My Profile'; ?> - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/workerProfile.css" type="text/css">
</head>
<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="profile-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <?php if ($isEditMode): ?>
                <a href="?controller=Worker&action=profile" class="btn-back">
                    <i class="fas fa-arrow-left"></i>
                </a>
            <?php endif; ?>
            <div>
                <h1>
                    <i class="fas fa-<?php echo $isEditMode ? 'user-edit' : 'user-circle'; ?>"></i>
                    <?php echo $isEditMode ? 'Edit Profile' : 'My Profile'; ?>
                </h1>
                <p><?php echo $isEditMode ? 'Update your photographer profile information' : 'View and manage your photographer profile'; ?></p>
            </div>
        </div>
        <?php if (!$isEditMode): ?>
            <div class="header-right">
                <a href="?controller=Worker&action=profile&edit=true" class="btn-edit-profile">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        <?php endif; ?>
    </div>



    <?php if ($isEditMode): ?>
    <!-- EDIT MODE - Form -->
    <form method="POST" action="?controller=Worker&action=updateProfile" enctype="multipart/form-data"
          class="profile-form">
        <?php else: ?>
        <!-- VIEW MODE - Display Only -->
        <div class="profile-form">
            <?php endif; ?>

            <div class="<?php echo $isEditMode ? 'form-grid' : 'profile-view-grid'; ?>">
                <!-- Left Column - Main Info -->
                <div class="<?php echo $isEditMode ? 'form-column' : 'profile-column'; ?>">
                    <!-- Profile Photo Section -->
                    <div class="<?php echo $isEditMode ? 'photo-section' : 'view-section'; ?>">
                        <h3><i class="fas fa-camera"></i> Profile Photo</h3>

                        <?php if ($isEditMode): ?>
                            <!-- Edit Mode - Upload -->
                            <div class="photo-upload">
                                <div class="photo-preview" id="photoPreview">
                                    <?php if ($worker['profile_photo']): ?>
                                        <img src="<?php echo htmlspecialchars($worker['profile_photo']); ?>"
                                             alt="Profile Photo">
                                    <?php else: ?>
                                        <div class="photo-placeholder">
                                            <i class="fas fa-user"></i>
                                            <p>No photo uploaded</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="photo-actions">
                                    <label for="profilePhoto" class="btn-upload">
                                        <i class="fas fa-upload"></i> Change Photo
                                    </label>
                                    <input type="file" id="profilePhoto" name="profile_photo" accept="image/*"
                                           style="display: none;">
                                    <p class="photo-hint">Max size: 5MB. JPG, PNG, or GIF</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- View Mode - Display -->
                            <div class="photo-display">
                                <?php if ($worker['profile_photo']): ?>
                                    <img src="<?php echo htmlspecialchars($worker['profile_photo']); ?>"
                                         alt="Profile Photo">
                                <?php else: ?>
                                    <div class="photo-placeholder">
                                        <i class="fas fa-user"></i>
                                        <p>No photo uploaded</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Personal Information -->
                    <div class="<?php echo $isEditMode ? 'form-section' : 'view-section'; ?>">
                        <h3><i class="fas fa-id-card"></i> Personal Information</h3>

                        <?php if ($isEditMode): ?>
                            <!-- Edit Mode - Form Fields -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">First Name <span class="required">*</span></label>
                                    <input type="text" id="firstName" name="firstName"
                                           value="<?php echo htmlspecialchars($worker['firstName']); ?>"
                                           required>
                                </div>

                                <div class="form-group">
                                    <label for="middleName">Middle Name</label>
                                    <input type="text" id="middleName" name="middleName"
                                           value="<?php echo htmlspecialchars($worker['middleName']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="lastName">Last Name <span class="required">*</span></label>
                                    <input type="text" id="lastName" name="lastName"
                                           value="<?php echo htmlspecialchars($worker['lastName']); ?>"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email"
                                       value="<?php echo htmlspecialchars($worker['email']); ?>"
                                       readonly
                                       class="readonly-field">
                                <small class="field-note">
                                    <i class="fas fa-info-circle"></i> Email cannot be changed. Contact support if
                                    needed.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="phoneNumber">Phone Number <span class="required">*</span></label>
                                <input type="tel" id="phoneNumber" name="phoneNumber"
                                       value="<?php echo htmlspecialchars($worker['phoneNumber']); ?>"
                                       required
                                       placeholder="09XXXXXXXXX"
                                       pattern="^09[0-9]{9}$"
                                       title="Enter a valid Philippine phone number (e.g., 09123456789)">
                            </div>

                            <div class="form-group">
                                <label for="address">Address <span class="required">*</span></label>
                                <textarea id="address" name="address" rows="3"
                                          required><?php echo htmlspecialchars($worker['address']); ?></textarea>
                            </div>
                        <?php else: ?>
                            <!-- View Mode - Display -->
                            <div class="info-grid">
                                <div class="info-field">
                                    <label>Full Name</label>
                                    <p><?php echo htmlspecialchars($worker['firstName'] . ' ' . $worker['middleName'] . ' ' . $worker['lastName']); ?></p>
                                </div>

                                <div class="info-field">
                                    <label>Email Address</label>
                                    <p><?php echo htmlspecialchars($worker['email']); ?></p>
                                </div>

                                <div class="info-field">
                                    <label>Phone Number</label>
                                    <p><?php echo htmlspecialchars($worker['phoneNumber']); ?></p>
                                </div>

                                <div class="info-field full-width">
                                    <label>Address</label>
                                    <p><?php echo htmlspecialchars($worker['address']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Professional Information -->
                    <div class="<?php echo $isEditMode ? 'form-section' : 'view-section'; ?>">
                        <h3><i class="fas fa-briefcase"></i> Professional Information</h3>

                        <?php if ($isEditMode): ?>
                            <!-- Edit Mode - Form Fields -->
                            <div class="form-group">
                                <label for="specialty">Specialty <span class="required">*</span></label>
                                <select id="specialty" name="specialty" required>
                                    <option value="" disabled>Select your specialty</option>
                                    <option value="event" <?php echo $worker['specialty'] === 'event' ? 'selected' : ''; ?>>
                                        Event Photography
                                    </option>
                                    <option value="portrait" <?php echo $worker['specialty'] === 'portrait' ? 'selected' : ''; ?>>
                                        Portrait Photography
                                    </option>
                                    <option value="product" <?php echo $worker['specialty'] === 'product' ? 'selected' : ''; ?>>
                                        Product Photography
                                    </option>
                                    <option value="lifestyle" <?php echo $worker['specialty'] === 'lifestyle' ? 'selected' : ''; ?>>
                                        Lifestyle Photography
                                    </option>
                                    <option value="photobooth" <?php echo $worker['specialty'] === 'photobooth' ? 'selected' : ''; ?>>
                                        Photobooth Services
                                    </option>
                                    <option value="creative" <?php echo $worker['specialty'] === 'creative' ? 'selected' : ''; ?>>
                                        Creative/Conceptual
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="experienceYears">Years of Experience <span class="required">*</span></label>
                                <input type="number" id="experienceYears" name="experience_years"
                                       value="<?php echo htmlspecialchars($worker['experience_years']); ?>"
                                       min="0" max="50" required>
                            </div>

                            <div class="form-group">
                                <label for="bio">Professional Bio <span class="required">*</span></label>
                                <textarea id="bio" name="bio" rows="6" required
                                          placeholder="Tell clients about your photography style, experience, and what makes you unique..."><?php echo htmlspecialchars($worker['bio']); ?></textarea>
                                <small class="field-note">
                                    <i class="fas fa-info-circle"></i> This will be displayed on your public profile
                                </small>
                            </div>
                        <?php else: ?>
                            <!-- View Mode - Display -->
                            <div class="info-grid">
                                <div class="info-field">
                                    <label>Specialty</label>
                                    <p><?php echo htmlspecialchars($specialtyLabels[$worker['specialty']] ?? $worker['specialty']); ?></p>
                                </div>

                                <div class="info-field">
                                    <label>Years of Experience</label>
                                    <p><?php echo htmlspecialchars($worker['experience_years']); ?> years</p>
                                </div>

                                <div class="info-field full-width">
                                    <label>Professional Bio</label>
                                    <p><?php echo nl2br(htmlspecialchars($worker['bio'])); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEditMode): ?>
                        <!-- Password Change - Only in Edit Mode -->
                        <div class="form-section">
                            <h3><i class="fas fa-lock"></i> Change Password</h3>

                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" name="current_password"
                                       placeholder="Enter current password">
                            </div>

                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" name="new_password"
                                       placeholder="Enter new password">
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" id="confirmPassword" name="confirm_password"
                                       placeholder="Confirm new password">
                            </div>

                            <small class="field-note">
                                <i class="fas fa-info-circle"></i> Leave blank if you don't want to change your password
                            </small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column - Stats & Actions -->
                <div class="<?php echo $isEditMode ? 'form-column' : 'profile-column'; ?>">
                    <!-- Statistics Card -->
                    <div class="stats-card">
                        <h3><i class="fas fa-chart-line"></i> Your Statistics</h3>

                        <div class="stat-item">
                            <div class="stat-icon rating">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo number_format((float)$worker['average_rating'], 1); ?></span>
                                <span class="stat-label">Average Rating</span>
                                <span class="stat-sublabel"><?php echo $worker['total_ratings']; ?> review<?php echo $worker['total_ratings'] == 1 ? '' : 's'; ?></span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon bookings">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo number_format($worker['total_bookings']); ?></span>
                                <span class="stat-label">Total Bookings</span>
                                <span class="stat-sublabel">All time</span>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon earnings">
                                <i class="fas fa-peso-sign"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-value">₱<?php echo number_format($worker['total_earnings'], 2); ?></span>
                                <span class="stat-label">Total Earnings</span>
                                <span class="stat-sublabel">Lifetime</span>
                            </div>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="info-card">
                        <h3><i class="fas fa-info-circle"></i> Account Information</h3>

                        <div class="info-item">
                            <span class="info-label">Application ID</span>
                            <span class="info-value"><?php echo htmlspecialchars($worker['application_id']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Worker ID</span>
                            <span class="info-value">#<?php echo str_pad($worker['worker_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Account Status</span>
                            <span class="status-badge <?php echo $worker['status']; ?>">
                            <?php echo ucfirst($worker['status']); ?>
                        </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Member Since</span>
                            <span class="info-value"><?php echo date('F Y', strtotime($worker['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Deals Section -->
            <div class="packages-section">
                <h3><i class="fas fa-box-open"></i> Service Packages</h3>
                <p class="section-description"><?php echo $isEditMode ? 'Create up to 3 service packages for your clients' : 'Your service offerings'; ?></p>

                <?php if ($isEditMode): ?>
                    <!-- Edit Mode - Package Forms -->
                    <div class="packages-grid" id="packagesGrid">
                        <?php
                        // Assume $existingPackages is passed from controller (array of max 3 packages)
                        $existingPackages = $existingPackages ?? [];
                        $packageCount = count($existingPackages);

                        for ($i = 0; $i < 3; $i++):
                            $package = $existingPackages[$i] ?? null;
                            ?>
                            <div class="package-card <?php echo $package ? 'has-data' : 'empty'; ?>" data-package-index="<?php echo $i; ?>">
                                <div class="package-header">
                                    <h4>
                                        <i class="fas fa-tag"></i>
                                        Package <?php echo $i + 1; ?>
                                        <?php if ($i === 0): ?>
                                            <span class="badge-basic">Basic</span>
                                        <?php elseif ($i === 1): ?>
                                            <span class="badge-medium">Medium</span>
                                        <?php else: ?>
                                            <span class="badge-premium">Premium</span>
                                        <?php endif; ?>
                                    </h4>
                                    <?php if ($package): ?>
                                        <button type="button" class="btn-remove-package" onclick="removePackage(<?php echo $i; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <input type="hidden" name="packages[<?php echo $i; ?>][package_id]" value="<?php echo $package['package_id'] ?? ''; ?>">

                                <div class="form-group">
                                    <label>Package Name <span class="required">*</span></label>
                                    <input type="text" name="packages[<?php echo $i; ?>][name]"
                                           value="<?php echo htmlspecialchars($package['name'] ?? ''); ?>"
                                           placeholder="e.g., Basic Portrait Session"
                                            <?php echo !$package ? '' : 'required'; ?>>
                                </div>

                                <div class="form-group">
                                    <label>Description <span class="required">*</span></label>
                                    <textarea name="packages[<?php echo $i; ?>][description]" rows="3"
                                              placeholder="Describe what's included in this package..."
                                          <?php echo !$package ? '' : 'required'; ?>><?php echo htmlspecialchars($package['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="package-details-row">
                                    <div class="form-group">
                                        <label>Price (₱) <span class="required">*</span></label>
                                        <input type="number" name="packages[<?php echo $i; ?>][price]"
                                               value="<?php echo $package['price'] ?? ''; ?>"
                                               min="0" step="0.01"
                                               placeholder="1500"
                                                <?php echo !$package ? '' : 'required'; ?>>
                                    </div>

                                    <div class="form-group">
                                        <label>Duration (hrs) <span class="required">*</span></label>
                                        <input type="number" name="packages[<?php echo $i; ?>][duration_hours]"
                                               value="<?php echo $package['duration_hours'] ?? ''; ?>"
                                               min="0.5" step="0.5"
                                               placeholder="1"
                                                <?php echo !$package ? '' : 'required'; ?>>
                                    </div>
                                </div>

                                <div class="package-details-row">
                                    <div class="form-group">
                                        <label>Photo Count <span class="required">*</span></label>
                                        <input type="number" name="packages[<?php echo $i; ?>][photo_count]"
                                               value="<?php echo $package['photo_count'] ?? ''; ?>"
                                               min="1"
                                               placeholder="15"
                                                <?php echo !$package ? '' : 'required'; ?>>
                                    </div>

                                    <div class="form-group">
                                        <label>Delivery (days) <span class="required">*</span></label>
                                        <input type="number" name="packages[<?php echo $i; ?>][delivery_days]"
                                               value="<?php echo $package['delivery_days'] ?? ''; ?>"
                                               min="1"
                                               placeholder="3"
                                                <?php echo !$package ? '' : 'required'; ?>>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="packages[<?php echo $i; ?>][status]">
                                        <option value="active" <?php echo ($package['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($package['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <!-- View Mode - Display Packages -->
                    <div class="packages-display">
                        <?php if (empty($existingPackages)): ?>
                            <div class="no-packages">
                                <i class="fas fa-box-open"></i>
                                <p>No service packages created yet</p>
                                <a href="?controller=Worker&action=profile&edit=true" class="btn-add-package">
                                    <i class="fas fa-plus"></i> Add Packages
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="packages-view-grid">
                                <?php foreach ($existingPackages as $index => $package): ?>
                                    <div class="package-view-card">
                                        <div class="package-view-header">
                                            <h4>
                                                <?php if ($index === 0): ?>
                                                    <span class="badge-basic">Basic</span>
                                                <?php elseif ($index === 1): ?>
                                                    <span class="badge-medium">Medium</span>
                                                <?php else: ?>
                                                    <span class="badge-premium">Premium</span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($package['name']); ?>
                                            </h4>
                                            <span class="status-badge <?php echo $package['status']; ?>">
                                                <?php echo ucfirst($package['status']); ?>
                                            </span>
                                        </div>
                                        <p class="package-description"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                                        <div class="package-price">₱<?php echo number_format($package['price'], 2); ?></div>
                                        <div class="package-details">
                                            <div class="detail-item">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo $package['duration_hours']; ?> hours</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-camera"></i>
                                                <span><?php echo $package['photo_count']; ?> photos</span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-calendar"></i>
                                                <span><?php echo $package['delivery_days']; ?> days delivery</span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Portfolio  Gallery Section -->
            <div class="portfolio-section">
                <h3><i class="fas fa-images"></i> Portfolio Gallery</h3>
                <p class="section-description"><?php echo $isEditMode ? 'Upload up to 8 images to showcase your work' : 'Your showcase images'; ?></p>

                <?php if ($isEditMode): ?>
                    <!-- Edit Mode - Upload Area -->
                    <div class="portfolio-upload-area">
                        <label for="portfolioImages" class="upload-zone">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Click to upload or drag and drop</p>
                            <span>PNG, JPG, or GIF (Max 5MB each) - Select multiple images</span>
                        </label>
                        <input type="file"
                               id="portfolioImages"
                               name="portfolio_images[]"
                               accept="image/*"
                               multiple
                               style="display: none;">
                    </div>

                <?php endif; ?>

                <div class="portfolio-grid" id="portfolioGrid">
                    <?php
                    if (empty($existingPortfolio) && !$isEditMode): ?>
                        <div class="no-portfolio">
                            <i class="fas fa-images"></i>
                            <p>No portfolio images yet</p>
                            <a href="?controller=Worker&action=profile&edit=true" class="btn-add-portfolio">
                                <i class="fas fa-plus"></i> Add Images
                            </a>
                        </div>
                    <?php else:
                        foreach ($existingPortfolio as $index => $portfolioItem): ?>
                            <div class="<?php echo $isEditMode ? 'portfolio-item' : 'portfolio-item-view'; ?>"
                                 data-work-id="<?php echo $portfolioItem['work_id']; ?>">
                                <?php 
                                $imagePath = $portfolioItem['image_path'];
                                if (!str_starts_with($imagePath, '/Kislap/') && !str_starts_with($imagePath, 'http')) {
                                    $imagePath = '/Kislap/' . $imagePath;
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                     alt="Portfolio <?php echo $index + 1; ?>">
                                <?php if ($isEditMode): ?>
                                    <button type="button" class="btn-remove"
                                            onclick="removePortfolioImage(<?php echo $portfolioItem['work_id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach;
                    endif; ?>

                </div>

                <?php if (!empty($existingPortfolio)): ?>
                    <div class="portfolio-info">
                        <?php if ($isEditMode): ?>
                            <span id="portfolioCount"><?php echo count($existingPortfolio); ?></span> / 8 images uploaded
                        <?php else: ?>
                            <?php echo count($existingPortfolio); ?> / 8 images uploaded
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>



            <?php if ($isEditMode): ?>
                <!-- Form Actions - Only in Edit Mode -->
                <div class="form-actions">
                    <a href="?controller=Worker&action=profile" class="btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($isEditMode): ?>
    </form>
    <?php else: ?>
</div>
<?php endif; ?>
</div>
<script>
    // Store selected files (needed globally)
    let selectedPortfolioFiles = [];

    <?php if ($isEditMode): ?>
    // **MODIFIED**: Added function to handle package removal
    function removePackage(packageIndex) {
        if (!confirm("Are you sure you want to remove this package? It will be permanently deleted when you save your changes.")) {
            return;
        }

        const packageCard = document.querySelector(`.package-card[data-package-index="${packageIndex}"]`);
        if (!packageCard) return;

        // Clear all visible data fields
        packageCard.querySelector('input[name*="[name]"]').value = '';
        packageCard.querySelector('textarea[name*="[description]"]').value = '';
        packageCard.querySelector('input[name*="[price]"]').value = '';
        packageCard.querySelector('input[name*="[duration_hours]"]').value = '';
        packageCard.querySelector('input[name*="[photo_count]"]').value = '';
        packageCard.querySelector('input[name*="[delivery_days]"]').value = '';

        const packageIdInput = packageCard.querySelector('input[name*="[package_id]"]');
        if (packageIdInput) {
            packageIdInput.value = '';
        }

        // Visually update the card
        packageCard.classList.remove('has-data');
        packageCard.classList.add('empty');

        const removeButton = packageCard.querySelector('.btn-remove-package');
        if (removeButton) {
            removeButton.remove();
        }

        alert('Package cleared. Click "Save Changes" to finalize the removal.');
    }

    // Profile photo preview
    document.getElementById('profilePhoto').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('photoPreview');
                preview.innerHTML = `
                    <div class="photo-container">
                        <img src="${e.target.result}" alt="Profile Photo">
                        <button type="button" class="btn-remove-photo" onclick="removeProfilePhoto()" title="Remove photo">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove profile photo function
    function removeProfilePhoto() {
        const preview = document.getElementById('photoPreview');
        const fileInput = document.getElementById('profilePhoto');
        
        // Reset the file input
        fileInput.value = '';
        
        // Restore original preview or placeholder
        <?php if ($worker['profile_photo']): ?>
            preview.innerHTML = `<img src="<?php echo htmlspecialchars($worker['profile_photo']); ?>" alt="Profile Photo">`;
        <?php else: ?>
            preview.innerHTML = `
                <div class="photo-placeholder">
                    <i class="fas fa-user"></i>
                    <p>No photo uploaded</p>
                </div>
            `;
        <?php endif; ?>
    }

    // ===== PORTFOLIO IMAGES PREVIEW =====
    const portfolioImagesInput = document.getElementById('portfolioImages');
    if (portfolioImagesInput) {
        portfolioImagesInput.addEventListener('change', function(e) {
            const files = e.target.files;
            const portfolioGrid = document.getElementById('portfolioGrid');
            const currentImages = portfolioGrid.querySelectorAll('.portfolio-item');
            const currentCount = currentImages.length;
            const maxImages = 8;

            console.log(`Current images: ${currentCount}, New files: ${files.length}`);

            // Remove "no portfolio" placeholder if exists
            const noPortfolio = portfolioGrid.querySelector('.no-portfolio');
            if (noPortfolio) {
                noPortfolio.remove();
            }

            // Calculate how many we can still add
            const availableSlots = maxImages - currentCount;

            if (files.length > availableSlots) {
                alert(`You can only upload ${availableSlots} more image(s). Maximum is ${maxImages} images total.`);
                return;
            }

            // Simple approach - just show preview without complex file management
            Array.from(files).forEach((file, index) => {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert(`"${file.name}" is not a valid image file.`);
                    return;
                }

                // Validate file size (10MB to match server validation)
                const maxSize = 10 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert(`"${file.name}" is too large. Maximum size is 10MB.`);
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const portfolioItem = document.createElement('div');
                    portfolioItem.className = 'portfolio-item';
                    portfolioItem.setAttribute('data-preview', 'true');
                    portfolioItem.setAttribute('data-file-index', index);
                    portfolioItem.innerHTML = `
                        <img src="${e.target.result}" alt="Portfolio Preview">
                        <div class="preview-badge">
                            <i class="fas fa-clock"></i> New
                        </div>
                        <button type="button" class="btn-remove-portfolio" onclick="removePortfolioPreview(this)" title="Remove image">
                            <i class="fas fa-times"></i>
                        </button>
                    `;

                    portfolioGrid.appendChild(portfolioItem);
                    updatePortfolioCount();
                };
                reader.readAsDataURL(file);
            });

            // Don't reset the input - let the form handle the files naturally
            console.log(`Files ready for upload: ${files.length}`);
        });
    }



    // Remove portfolio preview function
    function removePortfolioPreview(button) {
        const portfolioItem = button.closest('.portfolio-item');
        const portfolioGrid = document.getElementById('portfolioGrid');
        
        // Remove the preview item
        portfolioItem.remove();
        
        // Update the file input to remove the corresponding file
        updatePortfolioFileInput();
        
        // Update count
        updatePortfolioCount();
        
        // If no images left, show placeholder
        const remainingItems = portfolioGrid.querySelectorAll('.portfolio-item');
        if (remainingItems.length === 0) {
            portfolioGrid.innerHTML = `
                <div class="no-portfolio">
                    <i class="fas fa-images"></i>
                    <p>No portfolio images uploaded yet</p>
                </div>
            `;
        }
    }

    // Update portfolio file input after removal
    function updatePortfolioFileInput() {
        const portfolioInput = document.getElementById('portfolioImages');
        const portfolioGrid = document.getElementById('portfolioGrid');
        const previewItems = portfolioGrid.querySelectorAll('.portfolio-item[data-preview="true"]');
        
        // If no preview items left, clear the input
        if (previewItems.length === 0) {
            portfolioInput.value = '';
        }
    }

    // Update portfolio count
    function updatePortfolioCount() {
        const portfolioGrid = document.getElementById('portfolioGrid');
        const count = portfolioGrid.querySelectorAll('.portfolio-item').length;

        const portfolioSection = document.querySelector('.portfolio-section');
        let infoDiv = portfolioSection.querySelector('.portfolio-info');

        if (count > 0) {
            if (!infoDiv) {
                infoDiv = document.createElement('div');
                infoDiv.className = 'portfolio-info';
                portfolioSection.appendChild(infoDiv);
            }
            infoDiv.innerHTML = `<span id="portfolioCount">${count}</span> / 8 images`;
        } else if (infoDiv) {
            infoDiv.remove();
        }
    }

    // Simple form submission handler
    document.querySelector('.profile-form').addEventListener('submit', function (e) {
        console.log('=== FORM SUBMISSION STARTED ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        
        // Log all form data
        const formData = new FormData(this);
        console.log('Form data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        
        // Add a visual indicator that form is being processed
        const submitBtn = document.querySelector('.btn-save');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
        }
        
        // Phone number validation
        const phoneNumber = document.getElementById('phoneNumber').value.trim();
        if (phoneNumber) {
            const phonePattern = /^09[0-9]{9}$/;
            if (!phonePattern.test(phoneNumber)) {
                e.preventDefault();
                alert('Please complete the phone number. It should be 11 digits starting with 09 (e.g., 09123456789)');
                document.getElementById('phoneNumber').focus();
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                    submitBtn.disabled = false;
                }
                return false;
            }
        }

        // Password validation
        const newPass = document.getElementById('newPassword').value;
        const confirmPass = document.getElementById('confirmPassword').value;

        if (newPass && newPass !== confirmPass) {
            e.preventDefault();
            alert('New passwords do not match!');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                submitBtn.disabled = false;
            }
            return false;
        }

        if (newPass && newPass.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
                submitBtn.disabled = false;
            }
            return false;
        }

        // Check if portfolio files are selected
        const portfolioInput = document.getElementById('portfolioImages');
        if (portfolioInput && portfolioInput.files.length > 0) {
            console.log(`Submitting ${portfolioInput.files.length} portfolio images`);
        }
        
        console.log('Form submission proceeding');
        return true;
    });

    // Real-time phone number validation
    const phoneInput = document.getElementById('phoneNumber');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            
            // Allow empty field
            if (value === '') {
                this.value = '';
                this.style.borderColor = '';
                this.style.backgroundColor = '';
                // Remove any error message
                const errorMsg = this.parentNode.querySelector('.phone-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
                return;
            }
            
            // Limit to 11 digits for 09XXXXXXXXX format
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            // Format as 09XXXXXXXXX only if user starts typing
            if (value.length > 0 && !value.startsWith('09')) {
                // If starts with 63, convert to 09
                if (value.startsWith('63')) {
                    value = '0' + value.slice(2);
                }
                // If starts with 9, add 0
                else if (value.startsWith('9')) {
                    value = '0' + value;
                }
                // If doesn't start with 0, add 09
                else if (!value.startsWith('0')) {
                    value = '09' + value;
                }
            }
            
            this.value = value;
            
            // Visual validation feedback
            const phonePattern = /^09[0-9]{9}$/;
            if (value.length > 0) {
                if (phonePattern.test(value)) {
                    this.style.borderColor = '#28a745';
                    this.style.backgroundColor = '';
                    // Remove any error message
                    const errorMsg = this.parentNode.querySelector('.phone-error');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                } else {
                    this.style.borderColor = '#ffc107';
                    this.style.backgroundColor = '';
                }
            }
        });
        
        phoneInput.addEventListener('blur', function() {
            // Only show validation message if field has content and is invalid
            // But don't use alert - just visual feedback
            const phonePattern = /^09[0-9]{9}$/;
            if (this.value && !phonePattern.test(this.value)) {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '';
                
                // Add or update error message below the field
                let errorMsg = this.parentNode.querySelector('.phone-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('small');
                    errorMsg.className = 'phone-error';
                    errorMsg.style.color = '#dc3545';
                    errorMsg.style.fontSize = '12px';
                    errorMsg.style.marginTop = '4px';
                    errorMsg.style.display = 'block';
                    this.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Please enter a valid Philippine phone number (e.g., 09123456789)';
            } else {
                // Remove error message if valid
                const errorMsg = this.parentNode.querySelector('.phone-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });
    }

    <?php endif; ?>

    // Remove existing portfolio image (from database) - WORKS IN BOTH MODES
    function removePortfolioImage(workId) {
        if (!confirm("Are you sure you want to remove this image from your portfolio?")) {
            return;
        }

        fetch("?controller=Worker&action=removePortfolioImage", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ work_id: workId }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Remove the image div
                    const imageDiv = document.querySelector(`[data-work-id="${workId}"]`);
                    if (imageDiv) {
                        imageDiv.remove();
                    }

                    <?php if ($isEditMode): ?>
                    updatePortfolioCount();
                    <?php endif; ?>

                    // Show "no portfolio" message if no images left
                    const portfolioGrid = document.getElementById('portfolioGrid');
                    const remainingItems = portfolioGrid.querySelectorAll('.portfolio-item, .portfolio-item-view');
                    if (remainingItems.length === 0) {
                        portfolioGrid.innerHTML = `
                        <div class="no-portfolio">
                            <i class="fas fa-images"></i>
                            <p>No portfolio images yet</p>
                            <?php if ($isEditMode): ?>
                            <?php else: ?>
                            <a href="?controller=Worker&action=profile&edit=true" class="btn-add-portfolio">
                                <i class="fas fa-plus"></i> Add Images
                            </a>
                            <?php endif; ?>
                        </div>
                    `;
                    }

                    alert('Image removed successfully!');
                } else {
                    alert(data.message || "Failed to remove image.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error removing image. Please try again.");
            });
    }
</script>

</body>
</html>