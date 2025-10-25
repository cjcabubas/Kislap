<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$admin = $_SESSION['admin'] ?? null;
if (!$admin) {
    header("Location: /Kislap/views/admin/login.php");
    exit;
}
$workers = $workers ?? [];
$totalWorkers = $totalWorkers ?? 0;
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
    <title>Kislap - Approved Workers</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/approved_workers.css" type="text/css">
</head>
<body>

<div class="container">
    <div class="page-header-wrapper">
        <a href="/Kislap/index.php?controller=Admin&action=showDashboard" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>

        <div class="page-header">
            <div class="header-content">
                <h1><i class="fas fa-check-circle"></i> Approved Workers</h1>
                <p style="color:#999;">Manage active and approved workers (<?php echo $totalWorkers; ?> total)</p>
            </div>

            <div class="filter-section">
                <input type="text" id="searchInput" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                
                <div class="status-filters">
                    <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers&status=all&search=<?php echo urlencode($search); ?>" 
                       class="filter-btn <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'active' : ''; ?>">
                        All Workers
                    </a>
                    <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers&status=active&search=<?php echo urlencode($search); ?>" 
                       class="filter-btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'active' : ''; ?>">
                        Active
                    </a>
                    <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers&status=suspended&search=<?php echo urlencode($search); ?>" 
                       class="filter-btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'suspended') ? 'active' : ''; ?>">
                        Suspended
                    </a>
                    <a href="/Kislap/index.php?controller=Admin&action=viewApprovedWorkers&status=banned&search=<?php echo urlencode($search); ?>" 
                       class="filter-btn <?php echo (isset($_GET['status']) && $_GET['status'] === 'banned') ? 'active' : ''; ?>">
                        Banned
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($workers)): ?>
            <p class="no-results">No approved workers found.</p>
        <?php else: ?>
            <table class="worker-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($workers as $worker): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($worker['worker_id']); ?></td>
                        <td><?php echo htmlspecialchars($worker['firstName'] . ' ' . $worker['lastName']); ?></td>
                        <td><?php echo htmlspecialchars($worker['email']); ?></td>
                        <td><?php echo htmlspecialchars($worker['phoneNumber']); ?></td>
                        <td><span class="status-badge status-<?php echo strtolower($worker['account_status']); ?>"><?php echo ucfirst($worker['account_status']); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($worker['created_at'])); ?></td>
                        <td class="action-btns">
                            <?php if ($worker['account_status'] === 'active'): ?>
                                <button class="btn btn-suspend" onclick="handleWorkerAction(<?php echo $worker['worker_id']; ?>, 'suspend')">Suspend</button>
                                <button class="btn btn-ban" onclick="handleWorkerAction(<?php echo $worker['worker_id']; ?>, 'ban')">Ban</button>
                            <?php else: ?>
                                <button class="btn btn-activate" onclick="handleWorkerAction(<?php echo $worker['worker_id']; ?>, 'activate')">Activate</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
                <?php 
                $currentStatus = $_GET['status'] ?? 'all';
                for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="index.php?controller=Admin&action=viewApprovedWorkers&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($currentStatus); ?>"
                       class="page-link <?php echo ($i === $page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function handleWorkerAction(id, action) {
        let message;
        if (action === 'suspend') {
            message = 'Are you sure you want to SUSPEND this worker? They will not be able to take bookings.';
        } else if (action === 'ban') {
            message = 'Are you sure you want to BAN this worker? This action is usually permanent.';
        } else if (action === 'activate') {
            message = 'Are you sure you want to ACTIVATE this worker?';
        }

        if (!confirm(message)) return;

        fetch(`/Kislap/index.php?controller=Admin&action=handleWorkerAction`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ worker_id: id, action: action })
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
            // Preserve current status filter
            const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
            url.searchParams.set('status', currentStatus);
            window.location.href = url.toString();
        }, 600); // delay 600ms after typing
    });
</script>

</body>
</html>