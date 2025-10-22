<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kislap Admin Login</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <link rel="stylesheet" href="public/css/style.css" type="text/css">
    <link rel="stylesheet" href="public/css/login.css" type="text/css">

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

<form class="form" method="POST" action="/Kislap/index.php?controller=Admin&action=handleLogin">
    <div class="form-group">
        <label for="email">Email or Phone Number</label>
        <input id="username" type="text" name="username" required placeholder="Enter Admin Username">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required placeholder="Enter your Password">
    </div>

    <button type="submit">Login</button>
</form>
</div>
</body>
</html>

