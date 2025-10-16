<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kislap Worker Application</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <link rel="stylesheet" href="public/css/style.css" type="text/css">
</head>

<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

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

    <div class="form-group">
        <label for="images">Upload Work Samples (Up to 4)</label>
        <input id="images" type="file" name="images[]" multiple>
    </div>

    <div class="form-group">
        <label for="resume">Upload Resume</label>
        <input id="resume" type="file" name="resume">
    </div>

    <button type="submit">Apply</button>
</form>
<script src="limit-files.js" defer></script>
</body>

</html>