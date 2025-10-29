<?php
// ========================================
// SESSION MANAGEMENT
// ========================================

if (!isset($_SESSION['user'])) {
    header("Location: /Kislap/views/user/login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/userProfile.css" type="text/css">
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<!-- ========================================
     PROFILE CONTAINER
     ======================================== -->

<div class="container">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form id="profileForm" method="POST" action="?controller=User&action=updateProfile" enctype="multipart/form-data">
        <!-- ========================================
             PROFILE HEADER
             ======================================== -->
        <div class="profile-header">
            <div class="profile-photo-container">
                <div class="profile-photo" id="profilePhoto">
                    <?php if(!empty($user['profilePhotoUrl'])): ?>
                        <img id="profilePhotoImg" src="<?= htmlspecialchars($user['profilePhotoUrl']) ?>" alt="Profile Photo">
                    <?php else: ?>
                        <img id="profilePhotoImg" src="" alt="Profile Photo" class="hidden">
                        <span class="photo-placeholder">👤</span>
                    <?php endif; ?>
                </div>
                <button type="button" class="photo-upload-btn hidden" id="photoBtn">Edit</button>
                <input type="file" id="photoUpload" name="profilePhotoUrl" accept="image/*" class="hidden">
            </div>
            <div class="profile-name"><?= htmlspecialchars($user['firstName'].' '.$user['lastName']) ?></div>
            <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
        </div>

        <!-- ========================================
             PROFILE BODY
             ======================================== -->
        <div class="profile-body">




            <h2 class="section-title">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($user['firstName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="middleName">Middle Name</label>
                    <input type="text" id="middleName" name="middleName" value="<?= htmlspecialchars($user['middleName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($user['lastName']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" 
                           value="<?= htmlspecialchars($user['phoneNumber']) ?>" 
                           disabled
                           pattern="^09[0-9]{9}$"
                           title="Enter a valid Philippine phone number (e.g., 09123456789)"
                           placeholder="09XXXXXXXXX">
                </div>
                <div class="form-group full-width">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <span class="info-text">Email cannot be changed</span>
                </div>
                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" disabled><?= htmlspecialchars($user['address']) ?></textarea>
                </div>
            </div>



            <!-- ========================================
                 CUSTOMER SUPPORT SECTION
                 ======================================== -->
            <div class="support-section">
                <h2 class="section-title">Customer Support</h2>
                <div class="support-content">
                    <div class="support-info">
                        <p>Need help? Our customer support team is here to assist you with any questions or issues.</p>
                    </div>
                    <div class="support-options">
                        <div class="support-option">
                            <div class="support-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="support-details">
                                <h3>Email Support</h3>
                                <p>Get help via email</p>
                                <button type="button" class="btn-support" onclick="openSupportModal()">
                                    <i class="fas fa-paper-plane"></i> Contact Support
                                </button>
                            </div>
                        </div>
                        <div class="support-option">
                            <div class="support-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="support-details">
                                <h3>Change Password</h3>
                                <p>Update your account password</p>
                                <button type="button" class="btn-support" onclick="openPasswordModal()">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </div>
                        <div class="support-option">
                            <div class="support-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="support-details">
                                <h3>Quick Help</h3>
                                <p>Common issues and solutions</p>
                                <div class="quick-help-links">
                                    <p><strong>Booking Issues:</strong> Check your bookings page for status updates</p>
                                    <p><strong>Payment Problems:</strong> Verify your payment method in settings</p>
                                    <p><strong>Account Issues:</strong> Use the contact form for account-related problems</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn-edit" id="editBtn">Edit Profile</button>
                <button type="button" class="btn-cancel hidden" id="cancelBtn">Cancel</button>
                <button type="submit" class="btn-save hidden" id="saveBtn">Save Changes</button>
            </div>
        </div>
    </form>
</div>

<!-- ========================================
     CUSTOMER SUPPORT MODAL
     ======================================== -->
<div id="supportModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-headset"></i> Contact Customer Support</h3>
            <button type="button" class="modal-close" onclick="closeSupportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="supportForm" method="POST" action="?controller=User&action=contactSupport">
            <div class="modal-body">
                <div class="form-group">
                    <label for="supportSubject">Subject</label>
                    <select id="supportSubject" name="subject" required>
                        <option value="">Select a topic</option>
                        <option value="booking_issue">Booking Issue</option>
                        <option value="payment_problem">Payment Problem</option>
                        <option value="photographer_complaint">Photographer Complaint</option>
                        <option value="technical_issue">Technical Issue</option>
                        <option value="account_problem">Account Problem</option>
                        <option value="password_reset">Password Reset Request</option>
                        <option value="general_inquiry">General Inquiry</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="supportMessage">Message</label>
                    <textarea id="supportMessage" name="message" rows="6" placeholder="Please describe your issue or question in detail..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="supportPriority">Priority</label>
                    <select id="supportPriority" name="priority">
                        <option value="low">Low - General question</option>
                        <option value="medium" selected>Medium - Need assistance</option>
                        <option value="high">High - Urgent issue</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeSupportModal()">Cancel</button>
                <button type="submit" class="btn-send-support">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================
     CHANGE PASSWORD MODAL
     ======================================== -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-lock"></i> Change Password</h3>
            <button type="button" class="modal-close" onclick="closePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="passwordForm" method="POST" action="?controller=User&action=changePassword">
            <div class="modal-body">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                </div>
                <div class="password-requirements">
                    <p><i class="fas fa-info-circle"></i> Password must be at least 6 characters long</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closePasswordModal()">Cancel</button>
                <button type="submit" class="btn-change-password" id="changePasswordBtn">
                    <i class="fas fa-lock"></i> Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/Kislap/public/js/profile.js"></script>
<script src="public/js/navbaronclick.js"></script>

<script>
// Support Modal Functions
function openSupportModal() {
    document.getElementById('supportModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeSupportModal() {
    document.getElementById('supportModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('supportForm').reset();
}

// Password Modal Functions
function openPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Enable password inputs and focus
    setTimeout(() => {
        const currentPwd = document.getElementById('currentPassword');
        const newPwd = document.getElementById('newPassword');
        const confirmPwd = document.getElementById('confirmPassword');
        
        // Force enable them
        currentPwd.disabled = false;
        newPwd.disabled = false;
        confirmPwd.disabled = false;
        
        currentPwd.removeAttribute('disabled');
        newPwd.removeAttribute('disabled');
        confirmPwd.removeAttribute('disabled');
        
        currentPwd.readOnly = false;
        newPwd.readOnly = false;
        confirmPwd.readOnly = false;
        
        currentPwd.focus();
    }, 100);
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('passwordForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const supportModal = document.getElementById('supportModal');
    const passwordModal = document.getElementById('passwordModal');
    
    if (event.target === supportModal) {
        closeSupportModal();
    } else if (event.target === passwordModal) {
        closePasswordModal();
    }
}

// Password form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Check if all fields are filled
    if (!currentPassword || !newPassword || !confirmPassword) {
        e.preventDefault();
        alert('Please fill in all password fields!');
        return false;
    }
    
    // Check if new passwords match
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New passwords do not match!');
        return false;
    }
    
    // Simple password validation - just minimum length
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    // Check if new password is same as current (basic check)
    if (newPassword === currentPassword) {
        e.preventDefault();
        alert('New password must be different from your current password!');
        return false;
    }
    

    
    // Add loading state to button
    const submitBtn = document.getElementById('changePasswordBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Changing Password...';
    
    // If we reach here, validation passed and form will submit normally
});

// Support form validation
document.getElementById('supportForm').addEventListener('submit', function(e) {
    const subject = document.getElementById('supportSubject').value;
    const message = document.getElementById('supportMessage').value;
    
    if (!subject || !message.trim()) {
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

// Phone number validation for profile form
const phoneInput = document.getElementById('phoneNumber');
if (phoneInput) {
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, ''); // Remove non-digits
        
        // Allow empty field
        if (value === '') {
            this.value = '';
            this.style.borderColor = '';
            this.style.backgroundColor = '';
            // Remove any error message
            const errorMsg = this.parentNode.querySelector('.phone-error');
            if (errorMsg) {
                errorMsg.remove();
            }
            return;
        }
        
        // Limit to 11 digits for 09XXXXXXXXX format
        if (value.length > 11) {
            value = value.slice(0, 11);
        }
        
        // Format as 09XXXXXXXXX only if user starts typing
        if (value.length > 0 && !value.startsWith('09')) {
            // If starts with 63, convert to 09
            if (value.startsWith('63')) {
                value = '0' + value.slice(2);
            }
            // If starts with 9, add 0
            else if (value.startsWith('9')) {
                value = '0' + value;
            }
            // If doesn't start with 0, add 09
            else if (!value.startsWith('0')) {
                value = '09' + value;
            }
        }
        
        this.value = value;
        
        // Visual validation feedback
        const phonePattern = /^09[0-9]{9}$/;
        if (value.length > 0) {
            if (phonePattern.test(value)) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '';
                // Remove any error message
                const errorMsg = this.parentNode.querySelector('.phone-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            } else {
                this.style.borderColor = '#ffc107';
                this.style.backgroundColor = '';
            }
        }
    });
    
    phoneInput.addEventListener('blur', function() {
        // Only show validation message if field has content and is invalid
        // But don't use alert - just visual feedback
        const phonePattern = /^09[0-9]{9}$/;
        if (this.value && !phonePattern.test(this.value)) {
            this.style.borderColor = '#dc3545';
            this.style.backgroundColor = '';
            
            // Add or update error message below the field
            let errorMsg = this.parentNode.querySelector('.phone-error');
            if (!errorMsg) {
                errorMsg = document.createElement('small');
                errorMsg.className = 'phone-error';
                errorMsg.style.color = '#dc3545';
                errorMsg.style.fontSize = '12px';
                errorMsg.style.marginTop = '4px';
                errorMsg.style.display = 'block';
                this.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = 'Please enter a valid Philippine phone number (e.g., 09123456789)';
        } else {
            // Remove error message if valid
            const errorMsg = this.parentNode.querySelector('.phone-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        }
    });
}

// Profile form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    
    // Only validate if phone number is provided and form is in edit mode
    if (phoneNumber && !document.getElementById('phoneNumber').disabled) {
        const phonePattern = /^09[0-9]{9}$/;
        if (!phonePattern.test(phoneNumber)) {
            e.preventDefault();
            alert('Please complete the phone number. It should be 11 digits starting with 09 (e.g., 09123456789)');
            document.getElementById('phoneNumber').focus();
            return false;
        }
    }
});
</script>
</body>
</html>
