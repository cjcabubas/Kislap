<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$worker = $_SESSION['worker'] ?? null;
if (!$worker) {
    header("Location: index.php?controller=Worker&action=login");
    exit;
}

$availability = $availability ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Availability - Kislap</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Kislap/public/css/style.css">
    <style>
        body {
            background-color: #0a0a0a;
            color: #e0e0e0;
        }
        
        .availability-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            color: #fff;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-header h1 i {
            color: #ff6b00;
            margin-right: 10px;
        }
        
        .page-header p {
            color: #999;
            font-size: 16px;
        }
        
        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .month-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .month-nav button {
            background: rgba(255, 107, 0, 0.1);
            border: 1px solid rgba(255, 107, 0, 0.3);
            color: #ff6b00;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .month-nav button:hover {
            background: rgba(255, 107, 0, 0.2);
            border-color: #ff6b00;
        }
        
        .month-nav h2 {
            color: #fff;
            font-size: 24px;
            min-width: 200px;
            text-align: center;
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
            color: white;
        }
        
        .btn-primary:hover {
            box-shadow: 0 4px 15px rgba(255, 107, 0, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 107, 0, 0.1);
            color: #ff6b00;
            border: 1px solid rgba(255, 107, 0, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 107, 0, 0.2);
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-bottom: 40px;
        }
        
        .calendar-header {
            background: rgba(255, 107, 0, 0.1);
            border: 1px solid rgba(255, 107, 0, 0.3);
            padding: 15px;
            text-align: center;
            font-weight: 700;
            color: #ff6b00;
            border-radius: 8px;
        }
        
        .calendar-day {
            background: rgba(20, 20, 20, 0.8);
            border: 1px solid rgba(255, 107, 0, 0.2);
            padding: 15px;
            border-radius: 8px;
            min-height: 100px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .calendar-day:hover {
            border-color: rgba(255, 107, 0, 0.5);
            transform: translateY(-2px);
        }
        
        .calendar-day.empty {
            background: transparent;
            border: none;
            cursor: default;
        }
        
        .calendar-day.empty:hover {
            transform: none;
        }
        
        .day-number {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .day-status {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .status-available {
            background: #28a745;
            color: white;
        }
        
        .status-blocked {
            background: #dc3545;
            color: white;
        }
        
        .status-booked {
            background: #ffc107;
            color: #000;
        }
        
        .calendar-day.past {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .calendar-day.today {
            border-color: #ff6b00;
            box-shadow: 0 0 15px rgba(255, 107, 0, 0.3);
        }
        
        .legend {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px;
            background: rgba(255, 107, 0, 0.05);
            border: 1px solid rgba(255, 107, 0, 0.1);
            border-radius: 8px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: rgba(20, 20, 20, 0.95);
            border: 1px solid rgba(255, 107, 0, 0.3);
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(255, 107, 0, 0.2);
        }
        
        .modal-content h2 {
            color: #fff;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ff6b00 0%, #ff8533 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .modal-content label {
            color: #e0e0e0;
            display: block;
            margin: 15px 0 5px;
        }
        
        .modal-content input,
        .modal-content select {
            background: rgba(255, 107, 0, 0.05);
            border: 1px solid rgba(255, 107, 0, 0.3);
            color: #fff;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .modal-content input:focus,
        .modal-content select:focus {
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
        
        .close:hover {
            color: #ff6b00;
            transform: rotate(90deg);
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<?php require __DIR__ . '/../shared/navbar.php'; ?>

<div class="availability-container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Manage Availability</h1>
        <p>Set your available dates and working hours</p>
    </div>
    
    <div class="calendar-controls">
        <div class="month-nav">
            <button onclick="previousMonth()"><i class="fas fa-chevron-left"></i> Previous</button>
            <h2 id="currentMonth">December 2025</h2>
            <button onclick="nextMonth()">Next <i class="fas fa-chevron-right"></i></button>
        </div>
        
        <div class="quick-actions">
            <button class="btn btn-primary" onclick="showBlockDatesModal()">
                <i class="fas fa-ban"></i> Block Dates
            </button>
            <button class="btn btn-secondary" onclick="window.location.href='?controller=Worker&action=bookings'">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </button>
        </div>
    </div>
    
    <div class="calendar-grid" id="calendarGrid">
        <!-- Calendar headers -->
        <div class="calendar-header">Sun</div>
        <div class="calendar-header">Mon</div>
        <div class="calendar-header">Tue</div>
        <div class="calendar-header">Wed</div>
        <div class="calendar-header">Thu</div>
        <div class="calendar-header">Fri</div>
        <div class="calendar-header">Sat</div>
        
        <!-- Calendar days will be generated by JavaScript -->
    </div>
    
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: #28a745;"></div>
            <span>Available</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #dc3545;"></div>
            <span>Blocked</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #ffc107;"></div>
            <span>Booked</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: rgba(255, 107, 0, 0.3); border: 2px solid #ff6b00;"></div>
            <span>Today</span>
        </div>
    </div>
</div>

<!-- Set Availability Modal -->
<div id="availabilityModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Set Availability</h2>
        
        <input type="hidden" id="selectedDate">
        <p id="selectedDateDisplay" style="color: #ff6b00; font-size: 18px; margin-bottom: 20px;"></p>
        
        <label>Status:</label>
        <select id="availabilityStatus">
            <option value="1">Available</option>
            <option value="0">Blocked</option>
        </select>
        
        <label>Working Hours (optional):</label>
        <div style="display: flex; gap: 10px;">
            <input type="time" id="startTime" placeholder="Start time">
            <input type="time" id="endTime" placeholder="End time">
        </div>
        
        <label>Max Bookings:</label>
        <input type="number" id="maxBookings" value="1" min="0" max="10">
        
        <div class="modal-actions">
            <button class="btn btn-primary" onclick="saveAvailability()">
                <i class="fas fa-save"></i> Save
            </button>
            <button class="btn btn-secondary" onclick="closeModal()">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Block Multiple Dates Modal -->
<div id="blockDatesModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeBlockModal()">&times;</span>
        <h2>Block Multiple Dates</h2>
        
        <label>From Date:</label>
        <input type="date" id="blockFromDate">
        
        <label>To Date:</label>
        <input type="date" id="blockToDate">
        
        <label>Reason (optional):</label>
        <input type="text" id="blockReason" placeholder="e.g., Vacation, Personal leave">
        
        <div class="modal-actions">
            <button class="btn btn-primary" onclick="blockDateRange()">
                <i class="fas fa-ban"></i> Block Dates
            </button>
            <button class="btn btn-secondary" onclick="closeBlockModal()">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
let currentDate = new Date();
let availabilityData = {};

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
});

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month display
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    
    // Clear existing days
    const grid = document.getElementById('calendarGrid');
    const headers = grid.querySelectorAll('.calendar-header');
    grid.innerHTML = '';
    headers.forEach(header => grid.appendChild(header));
    
    // Add empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day empty';
        grid.appendChild(emptyDay);
    }
    
    // Add days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dayDate = new Date(year, month, day);
        
        dayElement.className = 'calendar-day';
        
        // Check if past date
        if (dayDate < today && dayDate.toDateString() !== today.toDateString()) {
            dayElement.classList.add('past');
        }
        
        // Check if today
        if (dayDate.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        dayElement.innerHTML = `
            <div class="day-number">${day}</div>
            <div class="day-status status-available">Available</div>
        `;
        
        dayElement.onclick = function() {
            if (!dayElement.classList.contains('past')) {
                showAvailabilityModal(dateStr, day);
            }
        };
        
        grid.appendChild(dayElement);
    }
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function showAvailabilityModal(date, day) {
    document.getElementById('selectedDate').value = date;
    document.getElementById('selectedDateDisplay').textContent = `Setting availability for ${date}`;
    document.getElementById('availabilityModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('availabilityModal').style.display = 'none';
}

function showBlockDatesModal() {
    document.getElementById('blockDatesModal').style.display = 'block';
}

function closeBlockModal() {
    document.getElementById('blockDatesModal').style.display = 'none';
}

async function saveAvailability() {
    const date = document.getElementById('selectedDate').value;
    const isAvailable = document.getElementById('availabilityStatus').value === '1';
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    const maxBookings = document.getElementById('maxBookings').value;
    
    try {
        const response = await fetch('?controller=Worker&action=setAvailability', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `date=${date}&is_available=${isAvailable}&start_time=${startTime}&end_time=${endTime}&max_bookings=${maxBookings}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Availability updated!');
            closeModal();
            renderCalendar();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to update availability');
        console.error(error);
    }
}

async function blockDateRange() {
    const fromDate = document.getElementById('blockFromDate').value;
    const toDate = document.getElementById('blockToDate').value;
    const reason = document.getElementById('blockReason').value;
    
    if (!fromDate || !toDate) {
        alert('Please select both from and to dates');
        return;
    }
    
    // Generate array of dates
    const dates = [];
    const current = new Date(fromDate);
    const end = new Date(toDate);
    
    while (current <= end) {
        dates.push(current.toISOString().split('T')[0]);
        current.setDate(current.getDate() + 1);
    }
    
    try {
        const response = await fetch('?controller=Worker&action=blockDates', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `dates=${JSON.stringify(dates)}&reason=${encodeURIComponent(reason)}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            closeBlockModal();
            renderCalendar();
        } else {
            alert('Error: ' + result.error);
        }
    } catch (error) {
        alert('Failed to block dates');
        console.error(error);
    }
}
</script>

</body>
</html>
