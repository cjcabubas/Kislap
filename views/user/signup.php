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

<?php 
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);
?>

<form class="form" method="POST" action="index.php?controller=Auth&action=signUpDB">
    <div class="fullname">
        <div class="form-group">
            <label for="lastName">Last Name <span class="required">*</span></label>
            <input id="lastName" name="lastName" type="text" required 
                   placeholder="Enter your Last Name" 
                   pattern="[a-zA-Z\s\-\'\.]{2,50}"
                   title="Name must be 2-50 characters, letters only"
                   value="<?php echo htmlspecialchars($formData['lastName'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="firstName">First Name <span class="required">*</span></label>
            <input id="firstName" name="firstName" type="text" required 
                   placeholder="Enter your First Name"
                   pattern="[a-zA-Z\s\-\'\.]{2,50}"
                   title="Name must be 2-50 characters, letters only"
                   value="<?php echo htmlspecialchars($formData['firstName'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="middleName">Middle Name</label>
            <input id="middleName" name="middleName" type="text" 
                   placeholder="Enter your Middle Name (Optional)"
                   title="Optional - leave blank if not applicable"
                   value="<?php echo htmlspecialchars($formData['middleName'] ?? ''); ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="phoneNumber">Phone Number <span class="required">*</span></label>
        <input id="phoneNumber" type="tel" name="phoneNumber" required
               placeholder="09XXXXXXXXX (Philippine mobile number)"
               pattern="^(09[0-9]{9}|639[0-9]{9}|\+639[0-9]{9})$"
               title="Enter a valid Philippine phone number (e.g., 09123456789)"
               value="<?php echo htmlspecialchars($formData['phoneNumber'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="email">Email Address <span class="required">*</span></label>
        <input id="email" type="email" name="email" required 
               placeholder="user@example.com"
               title="Enter a valid email address"
               value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="address">Complete Address <span class="required">*</span></label>
        <input id="address" type="text" name="address" required
               placeholder="House No., Street, Barangay, City, Province"
               minlength="10" maxlength="255"
               title="Enter your complete address (at least 10 characters)"
               value="<?php echo htmlspecialchars($formData['address'] ?? ''); ?>">
    </div>

    <div class="form-group">
        <label for="password">Password <span class="required">*</span></label>
        <input id="password" type="password" name="password" required 
               placeholder="Enter your password"
               minlength="6" maxlength="128"
               title="Password must be at least 6 characters">
        <small class="form-help">Password must be at least 6 characters</small>
    </div>

    <button type="submit">Sign Up</button>
</form>
</div>

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