<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: /Kislap/views/user/login.php");
    exit;
}
$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/userProfile.css" type="text/css">
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="container">
    <form id="profileForm" method="POST" action="/Kislap/index.php?controller=User&action=updateProfile" enctype="multipart/form-data">
        <div class="profile-header">
            <div class="profile-photo-container">
                <div class="profile-photo" id="profilePhoto">
                    <?php if(!empty($user['profilePhotoUrl'])): ?>
                        <img id="profilePhotoImg" src="<?= htmlspecialchars($user['profilePhotoUrl']) ?>" alt="Profile Photo">
                    <?php else: ?>
                        <img id="profilePhotoImg" src="" alt="Profile Photo" class="hidden">
                        <span class="photo-placeholder">👤</span>
                    <?php endif; ?>
                </div>
                <button type="button" class="photo-upload-btn hidden" id="photoBtn">Edit</button>
                <input type="file" id="photoUpload" name="profilePhotoUrl" accept="image/*" class="hidden">
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['firstName'].' '.$user['lastName']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <div class="profile-body">
            <h2 class="section-title">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="<?= htmlspecialchars($user['middleName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" value="<?= htmlspecialchars($user['phoneNumber']) ?>" disabled>
                </div>
                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <span class="info-text">Email cannot be changed</span>
                </div>
                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" disabled><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
            </div>
            <div class="button-group">
                <button type="button" class="btn-edit" id="editBtn">Edit Profile</button>
                <button type="button" class="btn-cancel hidden" id="cancelBtn">Cancel</button>
                <button type="submit" class="btn-save hidden" id="saveBtn">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<script src="/Kislap/public/js/profile.js"></script>
<script src="public/js/navbaronclick.js"></script>
</body>
</html>
