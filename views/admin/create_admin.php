<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    header("Location: /Kislap/views/admin/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/create_admin.css">
</head>
<body>

<div class="container">
    <div class="page-header-wrapper">
        <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>

        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-user-plus"></i> Create Admin Account</h1>
                <p>Add a new administrator to the platform</p>
            </div>
        </div>
    </div>

    <div class="form-container">
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
                <i class="fas fa-<?php echo $_SESSION['notification']['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <?php 
        // Clear form data after displaying
        unset($_SESSION['form_data']); 
        ?>

        <form class="admin-form" method="POST" action="index.php?controller=Admin&action=handleCreateAdmin">
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name <span class="required">*</span></label>
                        <input type="text" id="firstName" name="firstName" required 
                               placeholder="Enter first name" 
                               pattern="[a-zA-Z\s\-\'\.]{2,50}"
                               title="Name must be 2-50 characters, letters only"
                               value="<?php echo htmlspecialchars(($_SESSION['form_data']['firstName'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" name="middleName" 
                               placeholder="Enter middle name (optional)" 
                               pattern="[a-zA-Z\s\-\'\.]{2,50}"
                               title="Name must be 2-50 characters, letters only"
                               value="<?php echo htmlspecialchars(($_SESSION['form_data']['middleName'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="lastName" name="lastName" required 
                               placeholder="Enter last name" 
                               pattern="[a-zA-Z\s\-\'\.]{2,50}"
                               title="Name must be 2-50 characters, letters only"
                               value="<?php echo htmlspecialchars(($_SESSION['form_data']['lastName'] ?? '')); ?>">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-key"></i> Account Credentials</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Enter unique username" 
                               pattern="^[a-zA-Z][a-zA-Z0-9_-]{2,29}$"
                               title="Username must start with a letter, 3-30 characters, letters/numbers/underscore/hyphen only"
                               value="<?php echo htmlspecialchars(($_SESSION['form_data']['username'] ?? '')); ?>">
                        <small class="form-help">Username must be unique and will be used for login</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required 
                                   placeholder="Enter secure password" 
                                   minlength="6"
                                   title="Password must be at least 6 characters long">
                            <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="form-help">Password must be at least 6 characters long</small>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                        <div class="password-input">
                            <input type="password" id="confirmPassword" name="confirmPassword" required 
                                   placeholder="Confirm password" 
                                   minlength="6"
                                   title="Re-enter the same password">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-cancel" onclick="window.location.href='/Kislap/index.php?controller=Admin&action=showDashboard'">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="submit" class="btn btn-create">
                    <i class="fas fa-user-plus"></i>
                    Create Admin Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    // Username validation (basic)
    document.getElementById('username').addEventListener('input', function() {
        const username = this.value;
        if (username.length > 0 && username.length < 3) {
            this.setCustomValidity('Username must be at least 3 characters long');
        } else {
            this.setCustomValidity('');
        }
    });
</script>

</body>
</html>