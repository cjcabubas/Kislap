
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kislap Login</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
</head>

<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<form class="form" method="POST" action="index.php?controller=Auth&action=loginDB">

    <div class="form-group">
        <label for="identifier">Email or Phone Number</label>
        <input id="identifier" type="text" name="identifier" required placeholder="Enter Your Phone Number or Email ex. 09XXXXXXXXX/example@example.com">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required placeholder="Enter your Password">
    </div>

    <button type="submit">Login</button>

</form>

<script src="public/js/navbaronclick.js"></script>
</body>
</html>