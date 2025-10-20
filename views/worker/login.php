<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photographer Login - Kislap</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
</head>

<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="form-wrapper">
    <div class="login-header">
        <div class="login-icon">
            <i class="fas fa-camera"></i>
        </div>
        <h2>Photographer Login</h2>
        <p>Sign in to manage your photography services</p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Worker&action=loginDB">

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
        <p>Looking to book a photographer?</p>
        <a href="index.php?controller=Auth&action=login" class="switch-link">
            <i class="fas fa-user"></i> Client Login
        </a>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
</body>
</html>
