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
    <style>
        body {
            background-color: #0a0a0a;
            color: #e0e0e0;
        }
        
        .bookings-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }
        
        .bookings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .bookings-header h1 {
            color: #fff;
            font-size: 32px;
            font-weight: 700;
        }
        
        .bookings-header h1 i {
            color: #ff6b00;
            margin-right: 10px;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 12px 24px;
            border: 1px solid rgba(255, 107, 0, 0.3);
            background: rgba(255, 107, 0, 0.05);
            color: #e0e0e0;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .filter-tab:hover {
            background: rgba(255, 107, 0, 0.1);
            border-color: rgba(255, 107, 0, 0.5);
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
            color: #fff;
            border-color: #ff6b00;
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.3);
        }
        
        .bookings-grid {
            display: grid;
            gap: 20px;
        }
        
        .booking-card {
            background: rgba(20, 20, 20, 0.8);
            border: 1px solid rgba(255, 107, 0, 0.2);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 107, 0, 0.2);
            border-color: rgba(255, 107, 0, 0.4);
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }
        
        .customer-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .customer-info h3 {
            color: #fff;
            margin-bottom: 5px;
        }
        
        .customer-info p {
            color: #999;
            font-size: 14px;
        }
        
        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .customer-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending_worker { background: #ffc107; color: #000; }
        .status-confirmed { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
        .status-pending_confirmation { background: #17a2b8; color: white; }
        .status-negotiating { background: #ff9800; color: white; }
        .status-requires_info { background: #9c27b0; color: white; }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
            padding: 20px;
            background: rgba(255, 107, 0, 0.05);
            border: 1px solid rgba(255, 107, 0, 0.1);
            border-radius: 8px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-item i {
            color: #ff6b00;
            width: 20px;
        }
        
        .detail-item small {
            color: #999;
        }
        
        .detail-item strong {
            color: #fff;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn i {
            margin-right: 6px;
        }
        
        .btn-accept {
            background: #28a745;
            color: white;
        }
        
        .btn-accept:hover {
            background: #218838;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-negotiate {
            background: #ff9800;
            color: white;
        }
        
        .btn-negotiate:hover {
            background: #e68900;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .btn-chat {
            background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
            color: white;
        }
        
        .btn-chat:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: rgba(255, 107, 0, 0.1);
            color: #ff6b00;
            border: 1px solid rgba(255, 107, 0, 0.3);
        }
        
        .btn-info:hover {
            background: rgba(255, 107, 0, 0.2);
            border-color: #ff6b00;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 64px;
            color: rgba(255, 107, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #fff;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
        }
    </style>
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="bookings-container">
    <div class="bookings-header">
        <h1><i class="fas fa-calendar-check"></i> My Bookings</h1>
    </div>
    
    <div class="filter-tabs">
        <button class="filter-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" 
                onclick="filterBookings('')">
            All Bookings
        </button>
        <button class="filter-tab <?php echo ($_GET['status'] ?? '') === 'pending_worker' ? 'active' : ''; ?>" 
                onclick="filterBookings('pending_worker')">
            Pending
        </button>
        <button class="filter-tab <?php echo ($_GET['status'] ?? '') === 'confirmed' ? 'active' : ''; ?>" 
                onclick="filterBookings('confirmed')">
            Confirmed
        </button>
        <button class="filter-tab <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'active' : ''; ?>" 
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
                                <p style="color: #666; font-size: 14px;">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($booking['customer_email']); ?>
                                </p>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                            <?php 
                            $statusLabels = [
                                'pending_worker' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'cancelled' => 'Cancelled',
                                'pending_confirmation' => 'Awaiting Confirmation'
                            ];
                            echo $statusLabels[$booking['booking_status']] ?? $booking['booking_status'];
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
                        <?php if ($booking['booking_status'] === 'pending_worker' || $booking['booking_status'] === 'negotiating'): ?>
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
                        
                        <?php if ($booking['booking_status'] === 'confirmed'): ?>
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

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(5px);
}

.modal-content {
    background: rgba(20, 20, 20, 0.95);
    border: 1px solid rgba(255, 107, 0, 0.3);
    margin: 5% auto;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(255, 107, 0, 0.2);
}

.modal-content h2, .modal-content h3 {
    color: #fff;
    margin-bottom: 20px;
}

.modal-content h2 {
    background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.modal-content p, .modal-content label {
    color: #e0e0e0;
    margin-bottom: 10px;
}

.modal-content input[type="number"],
.modal-content input[type="date"],
.modal-content input[type="time"],
.modal-content input[type="text"],
.modal-content textarea {
    background: rgba(255, 107, 0, 0.05);
    border: 1px solid rgba(255, 107, 0, 0.3);
    color: #fff;
    border-radius: 8px;
    padding: 12px;
    width: 100%;
    margin: 10px 0;
    font-family: 'Segoe UI', sans-serif;
}

.modal-content input:focus,
.modal-content textarea:focus {
    outline: none;
    border-color: #ff6b00;
    box-shadow: 0 0 10px rgba(255, 107, 0, 0.3);
}

.close {
    color: #999;
    float: right;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
    transition: all 0.3s;
}

.close:hover,
.close:focus {
    color: #ff6b00;
    transform: rotate(90deg);
}

.modal-section {
    margin: 25px 0;
    padding: 20px;
    background: rgba(255, 107, 0, 0.03);
    border: 1px solid rgba(255, 107, 0, 0.1);
    border-radius: 8px;
}

.modal-section h3 {
    font-size: 18px;
    margin-bottom: 15px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-top: 15px;
}

.detail-grid > div {
    padding: 12px;
    background: rgba(255, 107, 0, 0.05);
    border: 1px solid rgba(255, 107, 0, 0.1);
    border-radius: 8px;
    color: #e0e0e0;
}

.detail-grid strong {
    color: #ff6b00;
    margin-right: 8px;
}

.modal-content .btn {
    margin-top: 10px;
}

.modal-content hr {
    border: none;
    border-top: 1px solid rgba(255, 107, 0, 0.2);
    margin: 25px 0;
}
</style>

</body>
</html>
