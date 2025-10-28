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
        <a href="/Kislap/index.php?controller=Auth&action=login" class="switch-link">
            <i class="fas fa-user"></i> Client Login
        </a>
    </div>

    <div class="support-section">
        <p>Need help?</p>
        <button type="button" class="support-link" onclick="openChangePasswordModal()">
            <i class="fas fa-key"></i> Change Password
        </button>
    </div>
</div>

<!-- ========================================
     CHANGE PASSWORD MODAL
     ======================================== -->
<div id="changePasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-key"></i> Change Password</h3>
            <button type="button" class="modal-close" onclick="closeChangePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="changePasswordForm" method="POST" action="index.php?controller=Auth&action=changePassword">
            <div class="modal-body">
                <p class="modal-info">Please fill in all your details exactly as registered to change your password.</p>
                
                <div class="form-group">
                    <label for="changeFirstName">First Name</label>
                    <input type="text" id="changeFirstName" name="firstName" placeholder="Enter your first name" required>
                </div>
                
                <div class="form-group">
                    <label for="changeLastName">Last Name</label>
                    <input type="text" id="changeLastName" name="lastName" placeholder="Enter your last name" required>
                </div>
                
                <div class="form-group">
                    <label for="changeMiddleName">Middle Name</label>
                    <input type="text" id="changeMiddleName" name="middleName" placeholder="Enter your middle name (optional)">
                </div>
                
                <div class="form-group">
                    <label for="changeEmail">Email</label>
                    <input type="email" id="changeEmail" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="changePhone">Phone Number</label>
                    <input type="text" id="changePhone" name="phoneNumber" placeholder="09XXXXXXXXX" required>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                </div>
                
                <input type="hidden" name="userType" value="worker">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeChangePasswordModal()">Cancel</button>
                <button type="submit" class="btn-change-password">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>

<script>
// Change Password Modal Functions
function openChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('changePasswordForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const changePasswordModal = document.getElementById('changePasswordModal');
    
    if (event.target === changePasswordModal) {
        closeChangePasswordModal();
    }
}

// Change password form validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const firstName = document.getElementById('changeFirstName').value;
    const lastName = document.getElementById('changeLastName').value;
    const email = document.getElementById('changeEmail').value;
    const phone = document.getElementById('changePhone').value;
    
    if (!firstName || !lastName || !email || !phone || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Please fill in all required fields!');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirmation do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
});

// Real-time password confirmation validation
document.getElementById('confirmPassword').addEventListener('input', function() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        this.style.borderColor = '#dc3545';
        this.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.2)';
    } else if (confirmPassword && newPassword === confirmPassword) {
        this.style.borderColor = '#28a745';
        this.style.boxShadow = '0 0 0 2px rgba(40, 167, 69, 0.2)';
    } else {
        this.style.borderColor = '';
        this.style.boxShadow = '';
    }
});

// Phone number formatting
document.getElementById('changePhone').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    this.value = value;
});
</script>
</body>
</html>
