<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$worker = $_SESSION['worker'] ?? null;
if (!$worker) {
    header("Location: index.php?controller=Worker&action=login");
    exit;
}

$bookings = $bookings ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css">
    <link rel="stylesheet" href="/Kislap/public/css/workerBookings.css">
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="bookings-container">
    <div class="bookings-header">
        <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
    </div>
    
    <div class="filter-tabs">
        <?php $currentStatus = $_GET['status'] ?? 'all'; ?>
        
        <button class="filter-tab <?php echo $currentStatus === 'all' || !isset($_GET['status']) ? 'active' : ''; ?>" 
                onclick="filterBookings('')">
            All Bookings
        </button>
        
        <button class="filter-tab <?php echo $currentStatus === 'pending' ? 'active' : ''; ?>" 
                onclick="filterBookings('pending')">
            Pending
        </button>
        
        <button class="filter-tab <?php echo $currentStatus === 'negotiating' ? 'active' : ''; ?>" 
                onclick="filterBookings('negotiating')">
            Negotiating
        </button>
        
        <button class="filter-tab <?php echo $currentStatus === 'confirmed' ? 'active' : ''; ?>" 
                onclick="filterBookings('confirmed')">
            Confirmed
        </button>
        
        <button class="filter-tab <?php echo $currentStatus === 'completed' ? 'active' : ''; ?>" 
                onclick="filterBookings('completed')">
            Completed
        </button>
        
        <button class="filter-tab <?php echo $currentStatus === 'cancelled' ? 'active' : ''; ?>" 
                onclick="filterBookings('cancelled')">
            Cancelled
        </button>
    </div>
    
    <div class="bookings-grid">
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No bookings yet</h3>
                <p>Your booking requests will appear here</p>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="customer-info">
                            <div class="customer-avatar">
                                <?php if ($booking['customer_photo']): ?>
                                    <img src="<?php echo htmlspecialchars($booking['customer_photo']); ?>" alt="Customer">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($booking['customer_name'], 0, 2)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3><?php echo htmlspecialchars($booking['customer_name']); ?></h3>
                                <?php if (!empty($booking['customer_email'])): ?>
                                <p style="color: #666; font-size: 14px;">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['customer_email']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                        // Handle empty status for display
                        $displayStatus = $booking['booking_status'] ?? '';
                        if (empty($displayStatus) || $displayStatus === '' || $displayStatus === null) {
                            $displayStatus = 'pending_worker';
                        }
                        ?>
                        <span class="status-badge status-<?php echo $displayStatus; ?>">
                            <?php 
                            $statusLabels = [
                                'pending_worker' => 'Pending',
                                'pending_ai' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'cancelled' => 'Cancelled',
                                'negotiating' => 'Negotiating',
                                'pending_confirmation' => 'Awaiting Confirmation',
                                'awaiting_deposit' => 'Awaiting Deposit',
                                'deposit_paid' => 'Deposit Paid',
                                'completed' => 'Completed',
                                'requires_info' => 'Needs Info'
                            ];
                            echo $statusLabels[$displayStatus] ?? ucfirst(str_replace('_', ' ', $displayStatus));
                            ?>
                        </span>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-item">
                            <i class="fas fa-camera"></i>
                            <div>
                                <small style="color: #666;">Event Type</small>
                                <div><strong><?php echo htmlspecialchars($booking['event_type']); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <small style="color: #666;">Date</small>
                                <div><strong><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <small style="color: #666;">Location</small>
                                <div><strong><?php echo htmlspecialchars($booking['event_location']); ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>
                                <small style="color: #666;">Budget</small>
                                <div><strong>₱<?php echo number_format($booking['budget'], 2); ?></strong></div>
                            </div>
                        </div>
                        
                        <?php if ($booking['package_name']): ?>
                            <div class="detail-item">
                                <i class="fas fa-box"></i>
                                <div>
                                    <small style="color: #666;">Package</small>
                                    <div><strong><?php echo htmlspecialchars($booking['package_name']); ?></strong></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="booking-actions">
                        <?php 
                        // Handle empty/null status - treat as pending_worker for workers
                        $status = $booking['booking_status'] ?? '';
                        if (empty($status) || $status === '' || $status === null) {
                            $status = 'pending_worker';
                        }
                        ?>
                        <?php if ($status === 'pending_worker' || $status === 'negotiating' || $status === 'pending_ai' || $status === 'requires_info'): ?>
                            <button class="btn btn-accept" onclick="acceptBooking(<?php echo $booking['conversation_id']; ?>)">
                                <i class="fas fa-check"></i> Accept
                            </button>
                            <button class="btn btn-negotiate" onclick="showNegotiateModal(<?php echo $booking['conversation_id']; ?>, <?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                <i class="fas fa-handshake"></i> Negotiate
                            </button>
                            <button class="btn btn-reject" onclick="showRejectModal(<?php echo $booking['conversation_id']; ?>)">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($status === 'confirmed' || $status === 'awaiting_deposit' || $status === 'deposit_paid'): ?>
                            <button class="btn btn-edit" onclick="showEditModal(<?php echo $booking['conversation_id']; ?>, <?php echo htmlspecialchars(json_encode($booking)); ?>)">
                                <i class="fas fa-edit"></i> Edit Details
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-chat" onclick="openChat(<?php echo $booking['conversation_id']; ?>)">
                            <i class="fas fa-comments"></i> Chat
                        </button>
                        
                        <button class="btn btn-info" onclick="showBookingDetails(<?php echo htmlspecialchars(json_encode($booking)); ?>)">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function filterBookings(status) {
    const url = new URL(window.location.href);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}

async function acceptBooking(conversationId) {
    if (!confirm('Accept this booking request?')) return;
    
    try {
        const response = await fetch('?controller=Worker&action=acceptBooking', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Booking accepted!');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to accept booking');
        console.error(error);
    }
}

async function rejectBooking(conversationId) {
    if (!confirm('Reject this booking request?')) return;
    
    try {
        const response = await fetch('?controller=Worker&action=rejectBooking', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Booking rejected');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to reject booking');
        console.error(error);
    }
}

function openChat(conversationId) {
    window.location.href = `?controller=Chat&action=view&conversation_id=${conversationId}`;
}

// Show negotiate modal
function showNegotiateModal(conversationId, booking) {
    const modal = document.getElementById('negotiateModal');
    document.getElementById('negotiateConversationId').value = conversationId;
    document.getElementById('currentPrice').textContent = booking.budget ? '₱' + parseFloat(booking.budget).toLocaleString() : 'Not set';
    modal.style.display = 'block';
}

// Show reject modal
function showRejectModal(conversationId) {
    const modal = document.getElementById('rejectModal');
    document.getElementById('rejectConversationId').value = conversationId;
    modal.style.display = 'block';
}

// Show edit modal
function showEditModal(conversationId, booking) {
    const modal = document.getElementById('editModal');
    document.getElementById('editConversationId').value = conversationId;
    document.getElementById('editEventDate').value = booking.event_date || '';
    document.getElementById('editEventTime').value = booking.event_time || '';
    document.getElementById('editEventLocation').value = booking.event_location || '';
    document.getElementById('editFinalPrice').value = booking.final_price || booking.budget || '';
    modal.style.display = 'block';
}

// Show booking details modal
function showBookingDetails(booking) {
    const modal = document.getElementById('detailsModal');
    let html = `
        <h3>Booking Details</h3>
        <div class="detail-grid">
            <div><strong>Customer:</strong> ${booking.customer_name}</div>
            <div><strong>Email:</strong> ${booking.customer_email}</div>
            <div><strong>Phone:</strong> ${booking.customer_phone || 'N/A'}</div>
            <div><strong>Event Type:</strong> ${booking.event_type}</div>
            <div><strong>Event Date:</strong> ${booking.event_date}</div>
            <div><strong>Event Time:</strong> ${booking.event_time || 'Not specified'}</div>
            <div><strong>Location:</strong> ${booking.event_location}</div>
            <div><strong>Budget:</strong> ₱${parseFloat(booking.budget || 0).toLocaleString()}</div>
            ${booking.final_price ? `<div><strong>Final Price:</strong> ₱${parseFloat(booking.final_price).toLocaleString()}</div>` : ''}
            ${booking.deposit_amount ? `<div><strong>Deposit:</strong> ₱${parseFloat(booking.deposit_amount).toLocaleString()}</div>` : ''}
            ${booking.special_requests ? `<div><strong>Special Requests:</strong> ${booking.special_requests}</div>` : ''}
            ${booking.worker_notes ? `<div><strong>Your Notes:</strong> ${booking.worker_notes}</div>` : ''}
        </div>
    `;
    document.getElementById('detailsContent').innerHTML = html;
    modal.style.display = 'block';
}

// Close modals
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="block"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
});

// Propose price
async function proposePrice() {
    const conversationId = document.getElementById('negotiateConversationId').value;
    const proposedPrice = document.getElementById('proposedPrice').value;
    const notes = document.getElementById('priceNotes').value;
    
    if (!proposedPrice) {
        alert('Please enter a proposed price');
        return;
    }
    
    try {
        const response = await fetch('?controller=Worker&action=proposePrice', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}&proposed_price=${proposedPrice}&notes=${encodeURIComponent(notes)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Price proposal sent!');
            closeModal('negotiateModal');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to send proposal');
        console.error(error);
    }
}

// Propose date/time
async function proposeDateTime() {
    const conversationId = document.getElementById('negotiateConversationId').value;
    const proposedDate = document.getElementById('proposedDate').value;
    const proposedTime = document.getElementById('proposedTime').value;
    const reason = document.getElementById('dateReason').value;
    
    if (!proposedDate) {
        alert('Please select a date');
        return;
    }
    
    try {
        const response = await fetch('?controller=Worker&action=proposeDateTime', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}&proposed_date=${proposedDate}&proposed_time=${proposedTime}&reason=${encodeURIComponent(reason)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Date/time proposal sent!');
            closeModal('negotiateModal');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to send proposal');
        console.error(error);
    }
}

// Reject with reason
async function rejectWithReason() {
    const conversationId = document.getElementById('rejectConversationId').value;
    const reason = document.getElementById('rejectReason').value;
    
    if (!confirm('Are you sure you want to reject this booking?')) return;
    
    try {
        const response = await fetch('?controller=Worker&action=rejectBooking', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}&reason=${encodeURIComponent(reason)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Booking rejected');
            closeModal('rejectModal');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to reject booking');
        console.error(error);
    }
}

// Update booking details
async function updateBooking() {
    const conversationId = document.getElementById('editConversationId').value;
    const eventDate = document.getElementById('editEventDate').value;
    const eventTime = document.getElementById('editEventTime').value;
    const eventLocation = document.getElementById('editEventLocation').value;
    const finalPrice = document.getElementById('editFinalPrice').value;
    
    try {
        const response = await fetch('?controller=Worker&action=updateBookingDetails', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `conversation_id=${conversationId}&event_date=${eventDate}&event_time=${eventTime}&event_location=${encodeURIComponent(eventLocation)}&final_price=${finalPrice}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Booking updated successfully!');
            closeModal('editModal');
            location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to update booking');
        console.error(error);
    }
}
</script>

<!-- Negotiate Modal -->
<div id="negotiateModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('negotiateModal')">&times;</span>
        <h2>Negotiate Booking</h2>
        <input type="hidden" id="negotiateConversationId">
        
        <div class="modal-section">
            <h3>Propose Alternative Price</h3>
            <p>Current budget: <strong id="currentPrice"></strong></p>
            <input type="number" id="proposedPrice" placeholder="Your proposed price" step="0.01" style="width: 100%; padding: 10px; margin: 10px 0;">
            <textarea id="priceNotes" placeholder="Explanation (optional)" style="width: 100%; padding: 10px; margin: 10px 0;" rows="3"></textarea>
            <button onclick="proposePrice()" class="btn btn-accept">Send Price Proposal</button>
        </div>
        
        <hr style="margin: 20px 0;">
        
        <div class="modal-section">
            <h3>Propose Alternative Date/Time</h3>
            <input type="date" id="proposedDate" style="width: 100%; padding: 10px; margin: 10px 0;">
            <input type="time" id="proposedTime" style="width: 100%; padding: 10px; margin: 10px 0;">
            <textarea id="dateReason" placeholder="Reason for date change (optional)" style="width: 100%; padding: 10px; margin: 10px 0;" rows="3"></textarea>
            <button onclick="proposeDateTime()" class="btn btn-accept">Send Date Proposal</button>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('rejectModal')">&times;</span>
        <h2>Reject Booking</h2>
        <input type="hidden" id="rejectConversationId">
        <p>Please provide a reason for rejecting this booking (optional but recommended):</p>
        <textarea id="rejectReason" placeholder="Reason for rejection..." style="width: 100%; padding: 10px; margin: 10px 0;" rows="4"></textarea>
        <button onclick="rejectWithReason()" class="btn btn-reject">Confirm Rejection</button>
        <button onclick="closeModal('rejectModal')" class="btn" style="background: rgba(255, 107, 0, 0.1); color: #ff6b00; border: 1px solid rgba(255, 107, 0, 0.3);">Cancel</button>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h2>Edit Booking Details</h2>
        <input type="hidden" id="editConversationId">
        
        <label>Event Date:</label>
        <input type="date" id="editEventDate" style="width: 100%; padding: 10px; margin: 10px 0;">
        
        <label>Event Time:</label>
        <input type="time" id="editEventTime" style="width: 100%; padding: 10px; margin: 10px 0;">
        
        <label>Event Location:</label>
        <input type="text" id="editEventLocation" style="width: 100%; padding: 10px; margin: 10px 0;">
        
        <label>Final Price:</label>
        <input type="number" id="editFinalPrice" step="0.01" style="width: 100%; padding: 10px; margin: 10px 0;">
        
        <button onclick="updateBooking()" class="btn btn-accept">Save Changes</button>
        <button onclick="closeModal('editModal')" class="btn" style="background: rgba(255, 107, 0, 0.1); color: #ff6b00; border: 1px solid rgba(255, 107, 0, 0.3);">Cancel</button>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('detailsModal')">&times;</span>
        <div id="detailsContent"></div>
        <button onclick="closeModal('detailsModal')" class="btn btn-chat" style="margin-top: 20px; width: 100%;">Close</button>
    </div>
</div>



</body>
</html>