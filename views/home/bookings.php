<?php
$user   = $_SESSION['user']   ?? null;
$worker = $_SESSION['worker'] ?? null;

$isLoggedIn = $user || $worker;
$userType   = $user ? 'user' : ($worker ? 'worker' : null);



$bookings = $bookings ?? [];
$totalBookings = count($bookings);
$totalAmount = 0;

foreach ($bookings as $booking) {
    // Use final_price if available, otherwise use budget or package_price
    $price = $booking['final_price'] ?? $booking['budget'] ?? $booking['package_price'] ?? 0;
    $totalAmount += $price;
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
        <?php $currentStatus = $_GET['status'] ?? 'all'; ?>
        <button class="tab-btn <?php echo $currentStatus === 'all' || !$currentStatus ? 'active' : ''; ?>" 
                onclick="filterBookings('')">
            <i class="fas fa-list"></i> All Bookings
        </button>
        <button class="tab-btn <?php echo in_array($currentStatus, ['pending_details', 'pending_worker', 'collecting_info']) ? 'active' : ''; ?>" 
                onclick="filterBookings('pending')">
            <i class="fas fa-clock"></i> Pending
        </button>
        <button class="tab-btn <?php echo $currentStatus === 'negotiating' ? 'active' : ''; ?>" 
                onclick="filterBookings('negotiating')">
            <i class="fas fa-handshake"></i> Negotiating
        </button>
        <button class="tab-btn <?php echo $currentStatus === 'confirmed' ? 'active' : ''; ?>" 
                onclick="filterBookings('confirmed')">
            <i class="fas fa-check-circle"></i> Confirmed
        </button>
        <button class="tab-btn <?php echo in_array($currentStatus, ['completed', 'rated']) ? 'active' : ''; ?>" 
                onclick="filterBookings('completed')">
            <i class="fas fa-star"></i> Completed
        </button>
        <button class="tab-btn <?php echo $currentStatus === 'cancelled' ? 'active' : ''; ?>" 
                onclick="filterBookings('cancelled')">
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
                    $conversationId = $booking['conversation_id'] ?? 0;
                    $photographerName = $booking['photographer_name'] ?? 'Unknown Photographer';
                    $photographerAvatar = $booking['photographer_photo'] ?? '';
                    $serviceType = $booking['event_type'] ?? 'Photography Session';
                    $bookingDate = $booking['event_date'] ?? '';
                    $bookingTime = $booking['event_time'] ?? '';
                    $location = $booking['event_location'] ?? 'Not specified';
                    $price = $booking['final_price'] ?? $booking['budget'] ?? $booking['package_price'] ?? 0;
                    $status = $booking['booking_status'] ?? 'pending';

                    $createdAt = $booking['created_at'] ?? '';
                    $packageName = $booking['package_name'] ?? null;
                    $photographerSpecialty = $booking['photographer_specialty'] ?? '';

                    // Map database status to display status
                    $displayStatus = $status;
                    switch($status) {
                        case 'collecting_info':
                        case 'pending_details':
                            $displayStatus = 'pending';
                            break;
                        case 'pending_worker':
                        case 'pending_confirmation':
                            $displayStatus = 'pending';
                            break;
                        case 'negotiating':
                            $displayStatus = 'negotiating';
                            break;
                        case 'confirmed':
                            $displayStatus = 'confirmed';
                            break;
                        case 'completed':
                        case 'rated':
                            $displayStatus = 'completed';
                            break;
                        case 'cancelled':
                            $displayStatus = 'cancelled';
                            break;
                    }

                    // Status styling
                    $statusClass = '';
                    $statusIcon = '';
                    switch($displayStatus) {
                        case 'pending':
                            $statusClass = 'status-pending';
                            $statusIcon = 'fa-clock';
                            break;
                        case 'negotiating':
                            $statusClass = 'status-negotiating';
                            $statusIcon = 'fa-handshake';
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
                    <div class="booking-card" data-status="<?php echo $displayStatus; ?>">
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
                                    <p class="service-type"><?php echo htmlspecialchars($photographerSpecialty ? ucwords(str_replace('_', ' ', $photographerSpecialty)) : $serviceType); ?></p>
                                </div>
                            </div>
                            <div class="booking-status <?php echo $statusClass; ?>">
                                <i class="fas <?php echo $statusIcon; ?>"></i>
                                <span><?php echo ucfirst($displayStatus); ?></span>
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

                        <!-- Show photographer's proposal if exists -->
                        <?php if ($status === 'negotiating' && (!empty($booking['worker_proposed_price']) || !empty($booking['worker_proposed_date']))): ?>
                            <div class="proposal-section">
                                <div class="proposal-header">
                                    <i class="fas fa-handshake"></i>
                                    <h4>Photographer's Proposal</h4>
                                </div>
                                <div class="proposal-content">
                                    <?php if (!empty($booking['worker_proposed_price'])): ?>
                                        <div class="proposal-item">
                                            <span class="proposal-label">Proposed Price:</span>
                                            <span class="proposal-value price">₱<?php echo number_format($booking['worker_proposed_price'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($booking['worker_proposed_date'])): ?>
                                        <div class="proposal-item">
                                            <span class="proposal-label">Proposed Date:</span>
                                            <span class="proposal-value"><?php echo date('M d, Y', strtotime($booking['worker_proposed_date'])); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($booking['worker_notes'])): ?>
                                        <div class="proposal-item full-width">
                                            <span class="proposal-label">Notes:</span>
                                            <span class="proposal-value"><?php echo nl2br(htmlspecialchars($booking['worker_notes'])); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="booking-footer">
                            <div class="booking-price">
                                <span class="price-label">Total Price</span>
                                <span class="price-amount">₱<?php echo number_format($price, 2); ?></span>
                            </div>
                            <div class="booking-actions">
                                <?php if ($packageName): ?>
                                    <div class="package-info">
                                        <i class="fas fa-box"></i>
                                        <span>Package: <?php echo htmlspecialchars($packageName); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="action-buttons">
                                    <!-- Actions based on booking status -->
                                    <?php if ($status === 'collecting_info' || $status === 'pending_details'): ?>
                                        <button class="btn-action btn-edit" onclick="editBookingDetails(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-edit"></i> Complete Details
                                        </button>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    
                                    <?php elseif ($status === 'pending_worker'): ?>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editBookingDetails(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-edit"></i> Edit Details
                                        </button>
                                        <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    
                                    <?php elseif ($status === 'negotiating'): ?>
                                        <?php 
                                        // Check if photographer proposed something
                                        $hasProposal = !empty($booking['worker_proposed_price']) || !empty($booking['worker_proposed_date']);
                                        ?>
                                        <?php if ($hasProposal): ?>
                                            <button class="btn-action btn-accept" onclick="acceptProposal(<?php echo $conversationId; ?>)">
                                                <i class="fas fa-check"></i> Accept Proposal
                                            </button>
                                            <button class="btn-action btn-reject" onclick="rejectProposal(<?php echo $conversationId; ?>)">
                                                <i class="fas fa-times"></i> Reject Proposal
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-cancel" onclick="cancelBooking(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    
                                    <?php elseif ($status === 'confirmed'): ?>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-details" onclick="showBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editBookingDetails(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-edit"></i> Edit Details
                                        </button>
                                    
                                    <?php elseif ($status === 'completed' || $status === 'rated'): ?>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-details" onclick="showBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </button>
                                        <button class="btn-action btn-rebook" onclick="bookAgain(<?php echo $booking['worker_id']; ?>)">
                                            <i class="fas fa-redo"></i> Book Again
                                        </button>
                                    
                                    <?php else: ?>
                                        <button class="btn-action btn-message" onclick="openConversation(<?php echo $conversationId; ?>)">
                                            <i class="fas fa-comment"></i> Message
                                        </button>
                                        <button class="btn-action btn-details" onclick="showBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                            <i class="fas fa-info-circle"></i> View Details
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="booking-meta">
                            <span class="booking-id">Booking #<?php echo str_pad($conversationId, 6, '0', STR_PAD_LEFT); ?></span>
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
                            $status = $booking['booking_status'] ?? 'pending';
                            
                            // Map database status to display status (same as main loop)
                            $displayStatus = $status;
                            switch($status) {
                                case 'collecting_info':
                                case 'pending_details':
                                    $displayStatus = 'pending';
                                    break;
                                case 'pending_worker':
                                case 'negotiating':
                                    $displayStatus = 'pending';
                                    break;
                                case 'confirmed':
                                case 'deposit_paid':
                                    $displayStatus = 'confirmed';
                                    break;
                                case 'completed':
                                case 'paid':
                                    $displayStatus = 'completed';
                                    break;
                                case 'cancelled':
                                case 'rejected':
                                    $displayStatus = 'cancelled';
                                    break;
                                default:
                                    $displayStatus = 'pending';
                            }
                            
                            if (isset($statusCounts[$displayStatus])) {
                                $statusCounts[$displayStatus]++;
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
    // Filter bookings by status
    function filterBookings(status) {
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    }

    // Booking action functions
    function openConversation(conversationId) {
        window.location.href = `?controller=Chat&action=view&conversation_id=${conversationId}`;
    }

    function bookAgain(workerId) {
        window.location.href = `?controller=Chat&action=startBooking&worker_id=${workerId}`;
    }

    function editBookingDetails(conversationId) {
        // Redirect to chat to edit details
        window.location.href = `?controller=Chat&action=view&conversation_id=${conversationId}&edit=true`;
    }

    async function acceptProposal(conversationId) {
        if (!confirm('Accept the photographer\'s proposal?')) return;
        
        try {
            const response = await fetch('?controller=Chat&action=acceptProposal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('success', 'Proposal accepted! Booking confirmed.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', result.error || 'Failed to accept proposal');
            }
        } catch (error) {
            console.error('Error accepting proposal:', error);
            showNotification('error', 'Failed to accept proposal');
        }
    }

    async function rejectProposal(conversationId) {
        const reason = prompt('Please provide a reason for rejecting (optional):');
        if (reason === null) return; // User cancelled
        
        try {
            const response = await fetch('?controller=Chat&action=rejectProposal', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}&reason=${encodeURIComponent(reason)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('success', 'Proposal rejected. You can continue negotiating.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', result.error || 'Failed to reject proposal');
            }
        } catch (error) {
            console.error('Error rejecting proposal:', error);
            showNotification('error', 'Failed to reject proposal');
        }
    }

    async function cancelBooking(conversationId) {
        const reason = prompt('Please provide a reason for cancellation:');
        if (!reason) return;
        
        if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) return;
        
        try {
            const response = await fetch('?controller=Chat&action=cancelBooking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `conversation_id=${conversationId}&reason=${encodeURIComponent(reason)}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('success', 'Booking cancelled successfully.');
                setTimeout(() => location.reload(), 1500);
            } else {
                showNotification('error', result.error || 'Failed to cancel booking');
            }
        } catch (error) {
            console.error('Error cancelling booking:', error);
            showNotification('error', 'Failed to cancel booking');
        }
    }

    function showBookingDetails(booking) {
        // Create and show booking details modal
        const modal = document.createElement('div');
        modal.className = 'booking-details-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Booking Details</h3>
                    <button class="close-btn" onclick="this.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong>Photographer:</strong>
                            <span>${booking.photographer_name}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Event Type:</strong>
                            <span>${booking.event_type || 'Not specified'}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Date:</strong>
                            <span>${booking.event_date ? new Date(booking.event_date).toLocaleDateString() : 'TBD'}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Time:</strong>
                            <span>${booking.event_time || 'TBD'}</span>
                        </div>
                        <div class="detail-item">
                            <strong>Location:</strong>
                            <span>${booking.event_location || 'Not specified'}</span>
                        </div>

                        <div class="detail-item">
                            <strong>Budget:</strong>
                            <span>₱${parseFloat(booking.budget || 0).toLocaleString()}</span>
                        </div>
                        ${booking.final_price ? `
                        <div class="detail-item">
                            <strong>Final Price:</strong>
                            <span>₱${parseFloat(booking.final_price).toLocaleString()}</span>
                        </div>
                        ` : ''}

                        ${booking.worker_notes ? `
                        <div class="detail-item full-width">
                            <strong>Photographer Notes:</strong>
                            <span>${booking.worker_notes}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-action btn-message" onclick="openConversation(${booking.conversation_id})">
                        <i class="fas fa-comment"></i> Open Chat
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    // Notification system
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

</script>
</body>
</html>