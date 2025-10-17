<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header("Location: /Kislap/index.php?controller=Auth&action=login");
    exit;
}

$bookings = $bookings ?? [];
$totalBookings = count($bookings);
$totalAmount = 0;

foreach ($bookings as $booking) {
    $totalAmount += $booking['price'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css" type="text/css">
    <link rel="stylesheet" href="/Kislap/public/css/bookings.css" type="text/css">
</head>
<body>
<?php  require __DIR__ . '/../shared/navbar.php'; ?>

<div class="bookings-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
            <p>Manage your photography bookings and sessions</p>
        </div>
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalBookings; ?></span>
                <span class="stat-label">Total Bookings</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">₱<?php echo number_format($totalAmount, 2); ?></span>
                <span class="stat-label">Total Amount</span>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="tab-btn active" data-status="all">
            <i class="fas fa-list"></i> All Bookings
        </button>
        <button class="tab-btn" data-status="pending">
            <i class="fas fa-clock"></i> Pending
        </button>
        <button class="tab-btn" data-status="confirmed">
            <i class="fas fa-check-circle"></i> Confirmed
        </button>
        <button class="tab-btn" data-status="completed">
            <i class="fas fa-star"></i> Completed
        </button>
        <button class="tab-btn" data-status="cancelled">
            <i class="fas fa-times-circle"></i> Cancelled
        </button>
    </div>

    <!-- Bookings Grid -->
    <div class="bookings-wrapper">
        <!-- Bookings List -->
        <div class="bookings-list">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No bookings yet</h3>
                    <p>Start booking talented photographers for your special moments!</p>
                    <a href="/Kislap/index.php?controller=Customer&action=browse" class="btn-browse">
                        <i class="fas fa-search"></i> Browse Photographers
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $bookingId = $booking['booking_id'] ?? 0;
                    $photographerName = $booking['photographer_name'] ?? 'Unknown Photographer';
                    $photographerAvatar = $booking['photographer_avatar'] ?? '';
                    $serviceType = $booking['service_type'] ?? 'Photography Session';
                    $bookingDate = $booking['booking_date'] ?? '';
                    $bookingTime = $booking['booking_time'] ?? '';
                    $duration = $booking['duration'] ?? 0;
                    $location = $booking['location'] ?? 'Not specified';
                    $price = $booking['price'] ?? 0;
                    $status = $booking['status'] ?? 'pending';
                    $notes = $booking['notes'] ?? '';
                    $createdAt = $booking['created_at'] ?? '';

                    // Status styling
                    $statusClass = '';
                    $statusIcon = '';
                    switch($status) {
                        case 'pending':
                            $statusClass = 'status-pending';
                            $statusIcon = 'fa-clock';
                            break;
                        case 'confirmed':
                            $statusClass = 'status-confirmed';
                            $statusIcon = 'fa-check-circle';
                            break;
                        case 'completed':
                            $statusClass = 'status-completed';
                            $statusIcon = 'fa-star';
                            break;
                        case 'cancelled':
                            $statusClass = 'status-cancelled';
                            $statusIcon = 'fa-times-circle';
                            break;
                    }
                    ?>
                    <div class="booking-card" data-status="<?php echo $status; ?>">
                        <div class="booking-header">
                            <div class="photographer-info">
                                <div class="photographer-avatar">
                                    <?php if ($photographerAvatar): ?>
                                        <img src="<?php echo htmlspecialchars($photographerAvatar); ?>" alt="<?php echo htmlspecialchars($photographerName); ?>">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($photographerName, 0, 2)); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="photographer-details">
                                    <h3><?php echo htmlspecialchars($photographerName); ?></h3>
                                    <p class="service-type"><?php echo htmlspecialchars($serviceType); ?></p>
                                </div>
                            </div>
                            <div class="booking-status <?php echo $statusClass; ?>">
                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                <span><?php echo ucfirst($status); ?></span>
                            </div>
                        </div>

                        <div class="booking-details">
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Date</span>
                                    <span class="detail-value"><?php echo $bookingDate ? date('F d, Y', strtotime($bookingDate)) : 'TBD'; ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Time</span>
                                    <span class="detail-value"><?php echo $bookingTime ? date('h:i A', strtotime($bookingTime)) : 'TBD'; ?></span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-hourglass-half"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Duration</span>
                                    <span class="detail-value"><?php echo $duration; ?> hours</span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="detail-content">
                                    <span class="detail-label">Location</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($location); ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if ($notes): ?>
                            <div class="booking-notes">
                                <i class="fas fa-sticky-note"></i>
                                <p><?php echo nl2br(htmlspecialchars($notes)); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="booking-footer">
                            <div class="booking-price">
                                <span class="price-label">Total Price</span>
                                <span class="price-amount">₱<?php echo number_format($price, 2); ?></span>
                            </div>
                            <div class="booking-actions">
                                <?php if ($status === 'pending'): ?>
                                    <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $bookingId; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button class="btn-action btn-message" onclick="messagePhotographer(<?php echo $booking['photographer_id'] ?? 0; ?>)">
                                        <i class="fas fa-comment"></i> Message
                                    </button>
                                <?php elseif ($status === 'confirmed'): ?>
                                    <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $bookingId; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button class="btn-action btn-message" onclick="messagePhotographer(<?php echo $booking['photographer_id'] ?? 0; ?>)">
                                        <i class="fas fa-comment"></i> Message
                                    </button>
                                    <button class="btn-action btn-details">
                                        <i class="fas fa-info-circle"></i> Details
                                    </button>
                                <?php elseif ($status === 'completed'): ?>
                                    <button class="btn-action btn-review">
                                        <i class="fas fa-star"></i> Leave Review
                                    </button>
                                    <button class="btn-action btn-rebook">
                                        <i class="fas fa-redo"></i> Book Again
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="booking-meta">
                            <span class="booking-id">Booking #<?php echo str_pad($bookingId, 6, '0', STR_PAD_LEFT); ?></span>
                            <span class="booking-created">Created: <?php echo $createdAt ? date('M d, Y', strtotime($createdAt)) : 'N/A'; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Summary Sidebar -->
        <?php if (!empty($bookings)): ?>
            <div class="summary-sidebar">
                <div class="summary-card">
                    <h3><i class="fas fa-chart-bar"></i> Booking Summary</h3>

                    <div class="summary-stats">
                        <?php
                        $statusCounts = [
                            'pending' => 0,
                            'confirmed' => 0,
                            'completed' => 0,
                            'cancelled' => 0
                        ];

                        foreach ($bookings as $booking) {
                            $status = $booking['status'] ?? 'pending';
                            if (isset($statusCounts[$status])) {
                                $statusCounts[$status]++;
                            }
                        }
                        ?>

                        <div class="summary-stat">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo $statusCounts['pending']; ?></span>
                                <span class="stat-name">Pending</span>
                            </div>
                        </div>

                        <div class="summary-stat">
                            <div class="stat-icon confirmed">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo $statusCounts['confirmed']; ?></span>
                                <span class="stat-name">Confirmed</span>
                            </div>
                        </div>

                        <div class="summary-stat">
                            <div class="stat-icon completed">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo $statusCounts['completed']; ?></span>
                                <span class="stat-name">Completed</span>
                            </div>
                        </div>

                        <div class="summary-stat">
                            <div class="stat-icon cancelled">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-count"><?php echo $statusCounts['cancelled']; ?></span>
                                <span class="stat-name">Cancelled</span>
                            </div>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span class="total-label">Total Investment</span>
                        <span class="total-amount">₱<?php echo number_format($totalAmount, 2); ?></span>
                    </div>

                    <a href="/Kislap/index.php?controller=Customer&action=browse" class="btn-new-booking">
                        <i class="fas fa-plus"></i> New Booking
                    </a>
                </div>

                <div class="tips-card">
                    <h4><i class="fas fa-lightbulb"></i> Tips</h4>
                    <ul>
                        <li>Communicate with your photographer before the shoot</li>
                        <li>Confirm location and time 24 hours in advance</li>
                        <li>Prepare a shot list for your session</li>
                        <li>Leave a review after completed bookings</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Filter tabs functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const bookingCards = document.querySelectorAll('.booking-card');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.getAttribute('data-status');

            // Update active tab
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            // Filter bookings
            bookingCards.forEach(card => {
                if (status === 'all' || card.getAttribute('data-status') === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

</script>
</body>
</html>