<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    header("Location: /Kislap/views/admin/login.php");
    exit;
}
$applications = $applications ?? [];
$totalApplications = $totalApplications ?? 0;
$totalPages = $totalPages ?? 1;
$page = $page ?? 1;
$limit = $limit ?? 10;
$search = $search ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kislap - Rejected Applications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/rejected_applications.css" type="text/css">
</head>
<body>

<div class="container">
    <div class="page-header-wrapper">
        <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>

        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-times-circle"></i> Rejected Applications</h1>
                <p style="color:#999;">Review rejected photographer applications (<?php echo $totalApplications; ?> total)</p>
            </div>

            <div class="filter-section">
                <input type="text" id="searchInput" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
        </div>
    </div>

    <div class="applications-container">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No rejected applications found</h3>
                <p>All applications are either pending or approved.</p>
            </div>
        <?php else: ?>
            <div class="applications-grid">
                <?php foreach ($applications as $app): ?>
                    <div class="application-card rejected">
                        <div class="card-header">
                            <div class="applicant-info">
                                <h3><?php echo htmlspecialchars($app['firstName'] . ' ' . $app['lastName']); ?></h3>
                                <div class="meta">
                                    <span><i class="fas fa-calendar"></i> Rejected: <?php echo date('M d, Y', strtotime($app['updated_at'])); ?></span>
                                    <span><i class="fas fa-clock"></i> Applied: <?php echo date('M d, Y', strtotime($app['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="status-badge rejected">
                                <i class="fas fa-times-circle"></i>
                                Rejected
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
                            <button class="btn btn-reconsider" onclick="reconsiderApplication(<?php echo $app['application_id']; ?>)">
                                <i class="fas fa-undo"></i>
                                Reconsider
                            </button>
                            <button class="btn btn-delete" onclick="deleteApplication(<?php echo $app['application_id']; ?>)">
                                <i class="fas fa-trash"></i>
                                Delete Permanently
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?controller=Admin&action=viewRejectedApplications&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                       class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal -->
<div class="modal" id="imageModal">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <img id="modalImage" src="" alt="Portfolio work">
</div>

<script>
    function reconsiderApplication(id) {
        if (!confirm('Are you sure you want to move this application back to pending for reconsideration?')) return;

        fetch(`/Kislap/index.php?controller=Admin&action=updateStatus`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ application_id: id, status: 'pending' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application moved to pending for reconsideration');
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

    function deleteApplication(id) {
        if (!confirm('Are you sure you want to PERMANENTLY DELETE this application? This action cannot be undone.')) return;

        fetch(`/Kislap/index.php?controller=Admin&action=deleteApplication`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ application_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Application deleted permanently');
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