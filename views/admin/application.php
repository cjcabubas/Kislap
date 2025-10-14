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
    <title>Kislap - Manage Applications</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/application.css" type="text/css">
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> Pending Applications</h1>
        <p style="color:#999;">Review and manage worker applications</p>

        <div class="filter-section">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by name or email..."
                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>

    <div class="applications-grid" id="applicationsContainer">
        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No pending applications found</h3>
            </div>
        <?php else: ?>
            <?php foreach ($applications as $app): ?>
                <div class="application-card" data-id="<?php echo $app['application_id']; ?>">
                    <div class="card-header">
                        <div class="applicant-info">
                            <h3><?php echo htmlspecialchars($app['firstName'].' '.$app['middleName'].' '.$app['lastName']); ?></h3>
                            <div class="meta">
                                <i class="fas fa-calendar"></i>
                                Applied: <?php echo date('M d, Y - h:i A', strtotime($app['createdAt'])); ?>
                            </div>
                        </div>
                        <span class="status-badge"><?php echo strtoupper($app['status']); ?></span>
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
                        <div class="info-item" style="grid-column:1/-1;">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <div class="label">Address</div>
                                <div class="value"><?php echo htmlspecialchars($app['address']); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resume -->
                    <div class="resume-section"
                         onclick="window.open('<?php echo htmlspecialchars($app['resume_path']); ?>', '_blank')">
                        <i class="fas fa-file-pdf"></i>
                        <div class="resume-info">
                            <h5>Resume / CV</h5>
                            <p>Click to view or download</p>
                        </div>
                        <i class="fas fa-external-link-alt" style="margin-left:auto;"></i>
                    </div>

                    <!-- Images -->
                    <?php $images = array_filter([$app['image1_path'],$app['image2_path'],$app['image3_path'],$app['image4_path']]); ?>
                    <?php if (!empty($images)): ?>
                        <div class="images-section">
                            <h4><i class="fas fa-images"></i> Application Images (<?php echo count($images); ?>)</h4>
                            <div class="images-grid">
                                <?php foreach ($images as $img): ?>
                                    <div class="image-item" onclick="openModal('<?php echo htmlspecialchars($img); ?>')">
                                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Application Image">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="actions">
                        <button class="btn btn-reject" onclick="handleAction(<?php echo $app['application_id']; ?>,'reject')">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                        <button class="btn btn-approve" onclick="handleAction(<?php echo $app['application_id']; ?>,'approve')">
                            <i class="fas fa-check-circle"></i> Approve
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Load More -->
    <?php if (($totalPages ?? 0) > 1 && ($page ?? 1) < ($totalPages ?? 1)): ?>
        <div class="load-more">
            <button class="btn btn-load-more" onclick="loadMore()">
                <i class="fas fa-chevron-down"></i> Load More Applications
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal" id="imageModal" onclick="closeModal()">
    <span class="modal-close">&times;</span>
    <img id="modalImage" src="" alt="Preview">
</div>

<script>
    let currentPage = <?php echo $page ?? 1; ?>;
    const totalPages = <?php echo $totalPages ?? 1; ?>;
    const searchQuery = '<?php echo addslashes($search ?? ''); ?>';

    // Search
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(()=>{
            window.location.href = `?search=${encodeURIComponent(e.target.value)}`;
        },500);
    });

    // Modal
    function openModal(src){
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.add('active');
    }
    function closeModal(){
        document.getElementById('imageModal').classList.remove('active');
    }

    // Load more
    function loadMore(){
        currentPage++;
        fetch(`?controller=Admin&action=viewPending&page=${currentPage}&ajax=1&search=${encodeURIComponent(searchQuery)}`)
            .then(res=>res.text())
            .then(html=>{
                document.getElementById('applicationsContainer').insertAdjacentHTML('beforeend', html);
                if(currentPage>=totalPages) document.querySelector('.load-more').style.display='none';
            }).catch(err=>console.error(err));
    }

    // Approve / Reject
    function handleAction(id,action){
        if(!confirm(`Are you sure you want to ${action} this application?`)) return;
        fetch('?controller=Admin&action=handleAction',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({application_id:id,action:action})
        }).then(r=>r.json()).then(data=>{
            if(data.success){
                const card = document.querySelector(`[data-id="${id}"]`);
                card.style.opacity='0';
                card.style.transform='translateX('+(action==='approve'?'100px':'-100px')+')';
                setTimeout(()=>card.remove(),300);
            }else alert(data.message||'Error');
        }).catch(err=>alert('An error occurred'));
    }
</script>

</body>
</html>
