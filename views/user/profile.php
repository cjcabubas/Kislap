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
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/userProfile.css" type="text/css">
</head>
<body>
<header class="header">
    <a href="../../main pages/index.html"><img src="../../imgs/Logo.png" class="logo" alt="DaguPin Logo"></a>
    <nav class="navbar">
        <a href="../../main pages/index.html" class="navtxt">Explore Now</a>
        <a href="../../main pages/bestspots.html">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 6h-18v12h4v4l4-4h10v-12z"/>
            </svg>
        </a>
        <a href="../../main pages/gallery.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </a>
        <a href="../../main pages/map.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
        </a>
        <a href="../../main pages/about.html">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z"/>
            </svg>
        </a>
    </nav>
</header>

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
</body>
</html>
