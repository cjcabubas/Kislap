<!-- applications.php -->
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
    <title>Kislap - Pending Applications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/pending_applications.css" type="text/css">
</head>
<body>

<div class="container">
    <div class="page-header-wrapper">
        <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>

        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-clock"></i> Pending Applications</h1>
                <p style="color:#999;">Review and manage photographer applications (<?php echo $totalApplications ?? count($applications ?? []); ?> total)</p>
            </div>

            <div class="filter-section">
                <input type="text" id="searchInput" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="applications-container">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No pending applications found</h3>
                <p>New applications will appear here for review.</p>
            </div>
        <?php else: ?>
            <div class="applications-grid" id="applicationsContainer">
                <?php foreach ($applications as $app): ?>
                    <div class="application-card pending" data-id="<?php echo $app['application_id']; ?>">
                        <div class="card-header">
                            <div class="applicant-info">
                                <h3><?php echo htmlspecialchars($app['firstName'] . ' ' . $app['lastName']); ?></h3>
                                <div class="meta">
                                    <span><i class="fas fa-calendar"></i> Applied: <?php echo date('M d, Y', strtotime($app['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="status-badge pending">
                                <i class="fas fa-clock"></i>
                                Pending
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <div class="label">Email</div>
                                    <div class="value"><?php echo htmlspecialchars($app['email']); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <div>
                                    <div class="label">Phone</div>
                                    <div class="value"><?php echo htmlspecialchars($app['phoneNumber']); ?></div>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <div class="label">Address</div>
                                    <div class="value"><?php echo htmlspecialchars($app['address']); ?></div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($app['resumeFilePath'])): ?>
                            <div class="resume-section" onclick="window.open('<?php echo htmlspecialchars($app['resumeFilePath']); ?>', '_blank')">
                                <i class="fas fa-file-pdf"></i>
                                <div class="resume-info">
                                    <h5>Resume/CV</h5>
                                    <p>Click to view document</p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($app['worksFilePath'])): ?>
                            <div class="images-section">
                                <h4><i class="fas fa-images"></i> Portfolio Work</h4>
                                <div class="images-grid">
                                    <?php foreach ($app['worksFilePath'] as $imagePath): ?>
                                        <div class="image-item" onclick="openModal('<?php echo htmlspecialchars($imagePath); ?>')">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Portfolio work" loading="lazy">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="actions">
                            <button class="btn btn-reject" onclick="openRejectModal(<?php echo $app['application_id']; ?>)">
                                <i class="fas fa-times"></i>
                                Reject
                            </button>
                            <button class="btn btn-approve" onclick="handleAction(<?php echo $app['application_id']; ?>,'approve')">
                                <i class="fas fa-check"></i>
                                Approve
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (($totalPages ?? 0) > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ($totalPages ?? 1); $i++): ?>
                        <a href="index.php?controller=Admin&action=viewPendingApplications&page=<?php echo $i; ?>&search=<?php echo urlencode($search ?? ''); ?>"
                           class="page-link <?php echo ($i === ($page ?? 1)) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>


</div>

<!-- Image Modal -->
<div class="modal" id="imageModal">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img id="modalImage" src="" alt="Portfolio work">
</div>

<!-- Rejection Modal -->
<div class="modal" id="rejectModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-times-circle"></i> Reject Application</h3>
            <span class="modal-close" onclick="closeRejectModal()">&times;</span>
        </div>
        <div class="modal-body">
            <p>Please provide a reason for rejecting this application:</p>
            <div class="form-group">
                <label for="rejectionReason">Rejection Reason</label>
                <select id="rejectionReason" onchange="toggleCustomReason()">
                    <option value="">Select a reason...</option>
                    <option value="Incomplete portfolio - needs more samples">Incomplete portfolio - needs more samples</option>
                    <option value="Poor image quality - resolution too low">Poor image quality - resolution too low</option>
                    <option value="Insufficient experience for platform standards">Insufficient experience for platform standards</option>
                    <option value="Missing required documents">Missing required documents</option>
                    <option value="Portfolio doesn't match claimed specialty">Portfolio doesn't match claimed specialty</option>
                    <option value="Unprofessional presentation">Unprofessional presentation</option>
                    <option value="custom">Other (specify below)</option>
                </select>
            </div>
            <div class="form-group" id="customReasonGroup" style="display: none;">
                <label for="customReason">Custom Reason</label>
                <textarea id="customReason" rows="3" placeholder="Please specify the reason..."></textarea>
            </div>
            <div class="form-group">
                <label for="additionalNotes">Additional Notes (Optional)</label>
                <textarea id="additionalNotes" rows="3" placeholder="Any additional feedback or suggestions for improvement..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Cancel</button>
            <button type="button" class="btn btn-reject" onclick="submitRejection()">
                <i class="fas fa-times"></i> Reject Application
            </button>
        </div>
    </div>
</div>

<script>
    let currentApplicationId = null;

    function handleAction(id, action) {
        if (!confirm(`Are you sure you want to ${action} this application?`)) return;

        fetch(`/Kislap/index.php?controller=Admin&action=updateStatus`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ application_id: id, status: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Request failed. Check console for details.');
        });
    }

    function openRejectModal(applicationId) {
        currentApplicationId = applicationId;
        document.getElementById('rejectModal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.body.style.overflow = '';
        // Reset form
        document.getElementById('rejectionReason').value = '';
        document.getElementById('customReason').value = '';
        document.getElementById('additionalNotes').value = '';
        document.getElementById('customReasonGroup').style.display = 'none';
        currentApplicationId = null;
    }

    function toggleCustomReason() {
        const select = document.getElementById('rejectionReason');
        const customGroup = document.getElementById('customReasonGroup');
        
        if (select.value === 'custom') {
            customGroup.style.display = 'block';
        } else {
            customGroup.style.display = 'none';
        }
    }

    function submitRejection() {
        const reasonSelect = document.getElementById('rejectionReason');
        const customReason = document.getElementById('customReason');
        const additionalNotes = document.getElementById('additionalNotes');
        
        let finalReason = '';
        
        if (reasonSelect.value === 'custom') {
            if (!customReason.value.trim()) {
                alert('Please specify a custom reason.');
                return;
            }
            finalReason = customReason.value.trim();
        } else if (reasonSelect.value) {
            finalReason = reasonSelect.value;
        } else {
            alert('Please select a rejection reason.');
            return;
        }
        
        // Add additional notes if provided
        if (additionalNotes.value.trim()) {
            finalReason += '\n\nAdditional Notes: ' + additionalNotes.value.trim();
        }

        fetch(`/Kislap/index.php?controller=Admin&action=updateStatus`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ 
                application_id: currentApplicationId, 
                status: 'reject',
                rejection_reason: finalReason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Request failed. Check console for details.');
        });
    }

    // Image modal functions
    function openModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('imageModal').classList.remove('active');
    }

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    let searchTimer;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const value = this.value.trim();
            const url = new URL(window.location.href);
            url.searchParams.set('search', value);
            url.searchParams.set('page', 1); // Reset to page 1 on search
            window.location.href = url.toString();
        }, 600); // delay 600ms after typing
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
</body>
</html>