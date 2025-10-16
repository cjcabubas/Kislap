<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kislap Sign Up</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <link rel="stylesheet" href="public/css/style.css" type="text/css">
</head>

<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<form class="form" method="POST" action="index.php?controller=Auth&action=signUpDB">
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
            <input id="middleName" name="middleName" type="text" required placeholder="Enter your Middle Name">
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
               placeholder="123 Example District, Example City, Example, 1234">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required placeholder="Enter your Password">
    </div>

    <button type="submit">Sign Up</button>
</form>

<script>
    // Add active state to current page
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar a').forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
</script>
</body>
</html>