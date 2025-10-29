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

<div class="form-wrapper">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
            <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

<form class="form" method="POST" action="/Kislap/index.php?controller=Admin&action=handleLogin">
    <h2>Admin Login</h2>
    
    <div class="form-group">
        <label for="username">Admin Username</label>
        <input id="username" type="text" name="username" required placeholder="Enter Admin Username">
    </div>

    <div class="form-group">
        <label for="password">Admin Password</label>
        <input id="password" type="password" name="password" required placeholder="Enter Admin Password">
    </div>

    <button type="submit">Login as Admin</button>
</form>
</div>
</body>
</html>

