<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Login - Kislap</title>

    <link rel="stylesheet" href="public/css/form.css" type="text/css">
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
    
    <div class="login-header">
        <div class="login-icon client">
            <i class="fas fa-user"></i>
        </div>
        <h2>Client Login</h2>
        <p>Sign in to book photography services</p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Auth&action=loginDB">

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
        <p>Are you a photographer?</p>
        <a href="/Kislap/index.php?controller=Worker&action=login" class="switch-link">
            <i class="fas fa-camera"></i> Photographer Login
        </a>
    </div>

    <div class="support-section">
        <p>Need help?</p>
        <button type="button" class="support-link" onclick="openLoginSupportModal()">
            <i class="fas fa-headset"></i> Customer Support
        </button>
    </div>
</div>

<!-- ========================================
     CUSTOMER SUPPORT MODAL
     ======================================== -->
<div id="loginSupportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-headset"></i> Customer Support</h3>
            <button type="button" class="modal-close" onclick="closeLoginSupportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="loginSupportForm" method="POST" action="index.php?controller=Auth&action=contactSupport">
            <div class="modal-body">
                <div class="form-group">
                    <label for="supportEmail">Your Email</label>
                    <input type="email" id="supportEmail" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label for="loginSupportSubject">Subject</label>
                    <select id="loginSupportSubject" name="subject" required>
                        <option value="">Select a topic</option>
                        <option value="login_issue">Login Issue</option>
                        <option value="forgot_password">Forgot Password</option>
                        <option value="account_locked">Account Locked</option>
                        <option value="registration_problem">Registration Problem</option>
                        <option value="technical_issue">Technical Issue</option>
                        <option value="general_inquiry">General Inquiry</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="loginSupportMessage">Message</label>
                    <textarea id="loginSupportMessage" name="message" rows="5" placeholder="Please describe your issue..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeLoginSupportModal()">Cancel</button>
                <button type="submit" class="btn-send-support">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>

<script>
// Login Support Modal Functions
function openLoginSupportModal() {
    document.getElementById('loginSupportModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLoginSupportModal() {
    document.getElementById('loginSupportModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('loginSupportForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loginSupportModal');
    if (event.target === modal) {
        closeLoginSupportModal();
    }
}

// Support form validation
document.getElementById('loginSupportForm').addEventListener('submit', function(e) {
    const email = document.getElementById('supportEmail').value;
    const subject = document.getElementById('loginSupportSubject').value;
    const message = document.getElementById('loginSupportMessage').value;
    
    if (!email || !subject || !message.trim()) {
        e.preventDefault();
        alert('Please fill in all required fields!');
        return false;
    }
    
    if (message.trim().length < 10) {
        e.preventDefault();
        alert('Please provide a more detailed message (at least 10 characters)!');
        return false;
    }
});
</script></script>
</body>
</html>