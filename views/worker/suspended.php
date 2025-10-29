<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - Kislap</title>
    <link rel="stylesheet" href="public/css/form.css" type="text/css">
    <style>
        .suspension-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 0 20px;
        }
        
        /* Notification Styles */
        .notification {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
        }
        
        .notification.success {
            background: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.3);
            color: #28a745;
        }
        
        .notification.error {
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        
        .notification i {
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .notification span {
            flex: 1;
            font-weight: 500;
            font-size: 14px;
        }
        
        .notification.success i {
            color: #28a745;
        }
        
        .notification.error i {
            color: #dc3545;
        }
        
        .suspension-card {
            background: rgba(20, 20, 20, 0.95);
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
        }
        
        .suspension-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #fff;
        }
        
        .suspension-title {
            color: #dc3545;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .suspension-message {
            color: #fff;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .suspension-details {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #dc3545;
            min-width: 120px;
        }
        
        .detail-value {
            color: #fff;
            flex: 1;
            text-align: right;
        }
        
        .reason-text {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
            margin-top: 10px;
            font-style: italic;
        }
        
        .time-remaining {
            font-size: 18px;
            font-weight: 700;
            color: #ffc107;
        }
        
        .permanent-suspension {
            color: #dc3545;
            font-weight: 700;
        }
        
        .contact-support {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .support-text {
            color: #aaa;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .appeal-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            margin-right: 15px;
            margin-bottom: 15px;
        }
        
        .appeal-button:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(15, 15, 15, 0.98);
            margin: 2% auto;
            padding: 0;
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 95vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(40, 167, 69, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(40, 167, 69, 0.05);
        }
        
        .modal-header h3 {
            color: #fff;
            margin: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-header h3 i {
            color: #28a745;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: #999;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid rgba(40, 167, 69, 0.2);
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            background: rgba(40, 167, 69, 0.02);
        }
        
        .appeal-info {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            color: #28a745;
        }
        
        .appeal-info ul {
            margin: 10px 0 0 20px;
            color: #fff;
        }
        
        .appeal-info li {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #fff;
            font-size: 14px;
        }
        
        .required {
            color: #dc3545;
            font-weight: bold;
        }
        
        .form-group textarea,
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-radius: 6px;
            background: rgba(30, 30, 30, 0.8);
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group textarea:focus,
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #28a745;
            background: rgba(35, 35, 35, 0.9);
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .form-group textarea::placeholder,
        .form-group input::placeholder {
            color: #888;
            font-style: italic;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ccc;
        }
        
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: #fff;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        @media (max-width: 768px) {
            .suspension-container {
                margin: 50px auto;
                padding: 0 15px;
            }
            
            .suspension-card {
                padding: 30px 20px;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="suspension-container">
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="notification <?php echo htmlspecialchars($_SESSION['notification']['type']); ?>">
            <i class="fas fa-<?php echo $_SESSION['notification']['type'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <span><?php echo htmlspecialchars($_SESSION['notification']['message']); ?></span>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

    <div class="suspension-card">
        <div class="suspension-icon">
            <i class="fas fa-ban"></i>
        </div>
        
        <h1 class="suspension-title">Account Suspended</h1>
        
        <p class="suspension-message">
            Your photographer account has been temporarily suspended and you cannot access your dashboard or accept new bookings at this time.
        </p>
        
        <div class="suspension-details">
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <?php if ($suspensionInfo['is_permanent']): ?>
                        <span class="permanent-suspension">Permanently Suspended</span>
                    <?php else: ?>
                        <span class="time-remaining">Suspended for <?php echo htmlspecialchars($suspensionInfo['time_remaining']); ?></span>
                    <?php endif; ?>
                </span>
            </div>
            
            <?php if (!$suspensionInfo['is_permanent']): ?>
            <div class="detail-row">
                <span class="detail-label">Suspended Until:</span>
                <span class="detail-value">
                    <?php 
                    $suspendedUntil = new DateTime($suspensionInfo['suspended_until']);
                    echo $suspendedUntil->format('F j, Y \a\t g:i A'); 
                    ?>
                </span>
            </div>
            <?php endif; ?>
            
            <?php if ($suspensionInfo['suspended_at']): ?>
            <div class="detail-row">
                <span class="detail-label">Suspended On:</span>
                <span class="detail-value">
                    <?php 
                    $suspendedAt = new DateTime($suspensionInfo['suspended_at']);
                    echo $suspendedAt->format('F j, Y \a\t g:i A'); 
                    ?>
                </span>
            </div>
            <?php endif; ?>
            
            <div class="detail-row">
                <span class="detail-label">Reason:</span>
                <span class="detail-value">
                    <div class="reason-text">
                        <?php echo nl2br(htmlspecialchars($suspensionInfo['reason'])); ?>
                    </div>
                </span>
            </div>
        </div>
        
        <?php if (!$suspensionInfo['is_permanent']): ?>
        <div class="suspension-message">
            <strong>Your account will be automatically reactivated when the suspension period ends.</strong>
            You can try logging in again after the suspension expires.
        </div>
        <?php endif; ?>
        
        <div class="contact-support">
            <p class="support-text">
                If you believe this suspension was made in error or have questions about your account status, you can submit an appeal below.
            </p>
            
            <button type="button" class="appeal-button" onclick="openAppealModal()">
                <i class="fas fa-gavel"></i> Submit Appeal
            </button>
            
            <a href="index.php?controller=Worker&action=login" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Login
            </a>
        </div>
    </div>
</div>

<!-- Appeals Modal -->
<div id="appealModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-gavel"></i> Submit Suspension Appeal</h3>
            <button type="button" class="modal-close" onclick="closeAppealModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="appealForm" method="POST" action="index.php?controller=Worker&action=submitAppeal">
            <div class="modal-body">
                <div class="appeal-info">
                    <p><strong>Appeal Guidelines:</strong></p>
                    <ul>
                        <li>Provide clear and detailed explanation</li>
                        <li>Include any relevant evidence or context</li>
                        <li>Be respectful and professional</li>
                        <li>Appeals are reviewed within 24-48 hours</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <label for="appealSubject">Subject <span class="required">*</span></label>
                    <select id="appealSubject" name="subject" required>
                        <option value="">Select appeal type</option>
                        <option value="wrongful_suspension">Wrongful Suspension</option>
                        <option value="excessive_penalty">Excessive Penalty</option>
                        <option value="misunderstanding">Misunderstanding</option>
                        <option value="technical_issue">Technical Issue</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="appealMessage">Your Appeal <span class="required">*</span></label>
                    <textarea id="appealMessage" name="message" rows="6" 
                              placeholder="Please explain why you believe this suspension should be reviewed or overturned..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="contactEmail">Contact Email</label>
                    <input type="email" id="contactEmail" name="contact_email" 
                           placeholder="Your email for follow-up (optional)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeAppealModal()">Cancel</button>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Appeal
                </button>
            </div>
        </form>
    </div>
</div>

<script src="public/js/navbaronclick.js"></script>
<script>
// Appeals Modal Functions
function openAppealModal() {
    document.getElementById('appealModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAppealModal() {
    document.getElementById('appealModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('appealForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('appealModal');
    if (event.target === modal) {
        closeAppealModal();
    }
}

// Form validation
document.getElementById('appealForm').addEventListener('submit', function(e) {
    const subject = document.getElementById('appealSubject').value;
    const message = document.getElementById('appealMessage').value.trim();
    
    if (!subject) {
        e.preventDefault();
        alert('Please select an appeal type');
        return;
    }
    
    if (!message || message.length < 20) {
        e.preventDefault();
        alert('Please provide a detailed explanation (at least 20 characters)');
        return;
    }
});

// Auto-refresh page every 5 minutes to check if suspension has expired
<?php if (!$suspensionInfo['is_permanent']): ?>
setTimeout(function() {
    window.location.reload();
}, 300000); // 5 minutes
<?php endif; ?>
</script>
</body>
</html>