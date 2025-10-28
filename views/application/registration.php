<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kislap Worker Application</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <link rel="stylesheet" href="public/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/css/registration.css" type="text/css">
</head>

<body>

<?php 
require __DIR__ . '/../shared/navbar.php';
?>

<div class="form-wrapper">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

<form class="form" method="POST" enctype="multipart/form-data" action="index.php?controller=Application&action=submit">
    <div class="fullname">
        <div class="form-group">
            <label for="lastName">Last Name</label>
            <input id="lastName" name="lastName" type="text" required placeholder="Enter your Last Name">
        </div>

        <div class="form-group">
            <label for="firstName">First Name</label>
            <input id="firstName" name="firstName" type="text" required placeholder="Enter your First Name">
        </div>

        <div class="form-group">
            <label for="middleName">Middle Name</label>
            <input id="middleName" name="middleName" type="text" placeholder="Enter your Middle Name">
        </div>
    </div>

    <div class="form-group">
        <label for="phoneNumber">Phone Number</label>
        <input id="phoneNumber" type="tel" name="phoneNumber" required
               placeholder="Enter Your Phone Number ex. 09XXXXXXXXX">
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required placeholder="example@example.com">
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <input id="address" type="text" name="address" required
               placeholder="123 Example Street, Example City, Example Province">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required placeholder="Enter your Password">
    </div>

    <div class="form-group">
        <label for="images">Upload Work Samples (Up to 8)</label>
        <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif,.jpg,.jpeg,.png,.gif,.webp,.avif">
    </div>

    <div class="form-group">
        <label for="resume">Upload Resume</label>
        <input id="resume" type="file" name="resume">
    </div>

    <button type="submit">Apply</button>
</form>
</div>

<div class="status-check-section">
    <div class="status-check-card">
        <i class="fas fa-search-plus"></i>
        <h3>Already Applied?</h3>
        <p>Check your application status here</p>
        <a href="index.php?controller=Application&action=checkStatus" class="btn-check-status">
            <i class="fas fa-clipboard-check"></i> Check Application Status
        </a>
    </div>
</div>
<script src="public/js/limit-files.js" defer></script>
</body>

</html>