<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kislap</title>
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
</head>
<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="form-wrapper">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
    
    <div class="login-header">
        <div class="login-icon">
            <i class="fas fa-key"></i>
        </div>
        <h2>Forgot Password</h2>
        <p>Enter your email to receive an OTP code</p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Auth&action=sendOTP">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="Enter your email address">
        </div>

        <div class="form-group">
            <label for="userType">Account Type</label>
            <select id="userType" name="userType" required>
                <option value="user">Client Account</option>
                <option value="worker">Photographer Account</option>
            </select>
        </div>

        <button type="submit">
            <i class="fas fa-paper-plane"></i> Send OTP
        </button>
    </form>

    <div class="login-switch">
        <p>Remember your password?</p>
        <a href="index.php?controller=Auth&action=login" class="switch-link">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
</body>
</html>