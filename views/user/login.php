<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Kislap</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
</head>

<body>
<?php 
require __DIR__ . '/../shared/navbar.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="form-wrapper">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
    
    <div class="login-header">
        <div class="login-icon client">
            <i class="fas fa-user"></i>
        </div>
        <h2>Client Login</h2>
        <p>Sign in to book photography services</p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Auth&action=loginDB">

        <div class="form-group">
            <label for="identifier">Email or Phone Number</label>
            <input id="identifier" type="text" name="identifier" required placeholder="09XXXXXXXXX or email@example.com">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit">Login</button>

    </form>

    <div class="login-switch">
        <p>Are you a photographer?</p>
        <a href="/Kislap/index.php?controller=Worker&action=login" class="switch-link">
            <i class="fas fa-camera"></i> Photographer Login
        </a>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
</body>
</html>