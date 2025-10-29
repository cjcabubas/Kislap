<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$result = $result ?? null;
$errorMessage = $errorMessage ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Application Status - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/check.css" type="text/css">
</head>
<body>
<div class="status-container">
    <!-- Logo Header -->
    <div class="logo-header">
        <i class="fas fa-bolt"></i>
        <h1>KISLAP</h1>
        <p>Photographer Application Portal</p>
    </div>

    <?php if (!$result): ?>
        <div class="form-card">
            <div class="form-header">
                <h2>Check Application Status</h2>
                <p>Enter your details to view your application status</p>
            </div>

            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($errorMessage); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?controller=Application&action=submitCheckStatus">
                <div class="form-group">
                    <label for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               placeholder="your.email@example.com"
                               required>
                    </div>
                    <div class="helper-text">Enter the email you used when applying</div>
                </div>

                <div class="form-group">
                    <label for="identifier">
                        Phone Number
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="text"
                               id="identifier"
                               name="identifier"
                               placeholder="e.g., 09123456789">
                    </div>
                    <div class="helper-text">Optional: Helps verify your identity</div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-search"></i>
                    Check Status
                </button>
            </form>

            <div class="back-link">
                <a href="/Kislap/index.php">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- ==========================
             RESULT: Status Display
        =========================== -->
        <div class="status-result">
            <?php if ($result['status'] === 'PENDING'): ?>
                <div class="status-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <h2 class="status-title pending">Under Review</h2>
                <p class="status-message">
                    Your application is currently being reviewed by our team.
                    We'll notify you via email once a decision has been made.
                    This process typically takes 3–5 business days.
                </p>

            <?php elseif ($result['status'] === 'ACCEPTED'): ?>
                <div class="status-icon approved">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="status-title approved">Approved!</h2>
                <p class="status-message">
                    Congratulations! Your application has been approved.
                    You can now log in using your registered credentials and start
                    offering your photography services on Kislap.
                </p>

            <?php else: ?>
                <div class="status-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="status-title rejected">Application Declined</h2>
                <p class="status-message">
                    Thank you for your interest in joining Kislap.
                    Unfortunately, we are unable to approve your application at this time.
                </p>
                
                <?php if (!empty($result['rejection_reason'])): ?>
                <div class="rejection-reason-box">
                    <h3><i class="fas fa-info-circle"></i> Reason for Rejection</h3>
                    <p class="rejection-text"><?php echo nl2br(htmlspecialchars($result['rejection_reason'])); ?></p>
                </div>
                <?php endif; ?>
                
                <p class="status-message">
                    You may address the issues mentioned above and reapply after 30 days with updated information.
                </p>
            <?php endif; ?>

            <!-- Applicant Information -->
            <div class="applicant-info">
                <div class="info-item">
                    <span class="info-label">Name</span>
                    <span class="info-value">
                    <?php echo htmlspecialchars($result['lastName'] . ', ' . $result['firstName']); ?>
                </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($result['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Application ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($result['application_id']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Applied Date</span>
                    <span class="info-value">
                    <?php echo date('F d, Y', strtotime($result['created_at'])); ?>
                </span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-action btn-secondary" onclick="window.location.reload()">
                    <i class="fas fa-redo"></i> Check Again
                </button>

                <?php if ($result['status'] === 'ACCEPTED'): ?>
                    <a href="/Kislap/index.php?controller=Auth&action=login" class="btn-action btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login Now
                    </a>
                <?php else: ?>
                    <a href="/Kislap/index.php" class="btn-action btn-primary">
                        <i class="fas fa-home"></i> Go Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
