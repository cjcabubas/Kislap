<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Kislap</title>
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <style>
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        .password-match {
            margin-top: 5px;
            font-size: 12px;
        }
        .match-success { color: #28a745; }
        .match-error { color: #dc3545; }
    </style>
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
            <i class="fas fa-lock"></i>
        </div>
        <h2>Reset Password</h2>
        <p>Create your new password</p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Auth&action=updatePassword" id="resetForm">
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input id="new_password" type="password" name="new_password" required 
                   placeholder="Enter new password" minlength="6">
            <div id="password-strength" class="password-strength"></div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" type="password" name="confirm_password" required 
                   placeholder="Confirm new password">
            <div id="password-match" class="password-match"></div>
        </div>

        <button type="submit" id="submit-btn">
            <i class="fas fa-save"></i> Update Password
        </button>
    </form>

    <div class="login-switch">
        <p>Password requirements:</p>
        <ul style="text-align: left; font-size: 14px; color: #666;">
            <li>At least 6 characters long</li>
            <li>Mix of letters and numbers recommended</li>
            <li>Avoid common passwords</li>
        </ul>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
<script>
// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = '';
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    if (strength < 3) {
        feedback = '<span class="strength-weak">Weak password</span>';
    } else if (strength < 5) {
        feedback = '<span class="strength-medium">Medium strength</span>';
    } else {
        feedback = '<span class="strength-strong">Strong password</span>';
    }
    
    return feedback;
}

// Real-time password validation
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('password-strength');
    
    if (password.length > 0) {
        strengthDiv.innerHTML = checkPasswordStrength(password);
    } else {
        strengthDiv.innerHTML = '';
    }
    
    // Check confirm password match if it has value
    const confirmPassword = document.getElementById('confirm_password').value;
    if (confirmPassword.length > 0) {
        checkPasswordMatch();
    }
});

// Password match checker
function checkPasswordMatch() {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('password-match');
    
    if (confirmPassword.length === 0) {
        matchDiv.innerHTML = '';
        return;
    }
    
    if (password === confirmPassword) {
        matchDiv.innerHTML = '<span class="match-success"><i class="fas fa-check"></i> Passwords match</span>';
        return true;
    } else {
        matchDiv.innerHTML = '<span class="match-error"><i class="fas fa-times"></i> Passwords do not match</span>';
        return false;
    }
}

document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

// Form validation
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const password = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        return;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match.');
        return;
    }
});
</script>
</body>
</html>