<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Kislap</title>
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <style>
        .otp-input {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 10px;
            padding: 15px;
        }
        .otp-info {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .resend-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .resend-link {
            color: #ff6b00;
            text-decoration: none;
            font-weight: 600;
        }
        .resend-link:hover {
            text-decoration: underline;
        }
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
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2>Verify OTP</h2>
        <p>Enter the 6-digit code sent to your email</p>
    </div>

    <div class="otp-info">
        <i class="fas fa-envelope"></i>
        <p><strong>OTP sent to:</strong> <?php echo htmlspecialchars($_SESSION['otp_email'] ?? ''); ?></p>
        <p><small>Valid for 10 minutes</small></p>
    </div>

    <form class="form" method="POST" action="index.php?controller=Auth&action=verifyOTPCode">
        <div class="form-group">
            <label for="otp_code">OTP Code</label>
            <input id="otp_code" type="text" name="otp_code" class="otp-input" 
                   maxlength="6" pattern="[0-9]{6}" required 
                   placeholder="000000" autocomplete="off">
        </div>

        <button type="submit">
            <i class="fas fa-check"></i> Verify OTP
        </button>
    </form>

    <div class="resend-section">
        <p>Didn't receive the code?</p>
        <a href="index.php?controller=Auth&action=sendOTP" class="resend-link" 
           onclick="return confirm('This will send a new OTP code. Continue?')">
            <i class="fas fa-redo"></i> Resend OTP
        </a>
    </div>

    <div class="login-switch">
        <a href="index.php?controller=Auth&action=forgotPassword" class="switch-link">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
<script>
// Auto-format OTP input
document.getElementById('otp_code').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});

// Auto-submit when 6 digits are entered
document.getElementById('otp_code').addEventListener('input', function() {
    if (this.value.length === 6) {
        // Small delay to show the complete code
        setTimeout(() => {
            this.form.submit();
        }, 500);
    }
});
</script>
</body>
</html>