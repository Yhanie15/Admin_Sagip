<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin details are available in the session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<!-- Topbar -->
<div class="d-flex justify-content-between align-items-center bg-light shadow-sm p-2 mb-3">
    <!-- Philippine Standard Time display (updated styling) -->
    <div style="color: #000; padding: 5px 10px; border-radius: 5px; margin-left: 15px; 
                background-color: #f8d7da; font-size: 1.1rem;">
        <span id="philippineTime"></span> <!-- Only show time and date -->
    </div>
    
    <div class="d-flex align-items-center me-3">
        <span class="material-icons me-2 notifications" id="notificationIcon" style="cursor: pointer;" onclick="toggleNotifications()">notifications</span>
        <span class="material-icons me-2">account_circle</span>
        <span><?= htmlspecialchars($admin_name) ?></span> <!-- Display the admin name -->
    </div>
</div>

<script>
// Update Philippine time every second
function updatePhilippineTime() {
    const now = new Date();

    // Use 'Asia/Manila' for Philippine Standard Time
    const philippineTimeString = now.toLocaleString('en-US', {
        timeZone: 'Asia/Manila',
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: true
    });

    document.getElementById('philippineTime').textContent = philippineTimeString;
}

// Call once immediately, then update every second
updatePhilippineTime();
setInterval(updatePhilippineTime, 1000);
</script>
