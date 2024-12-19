<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin details are available in the session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<!-- Topbar -->
<div class="d-flex align-items-center bg-light shadow-sm p-2 mb-3">
    <button id="toggleSidebar" class="btn btn-light d-flex align-items-center me-auto">
        <span class="material-icons">menu</span>
    </button>
    <div class="d-flex align-items-center">
        <span class="material-icons me-2 notifications" id="notificationIcon" style="cursor: pointer;" onclick="toggleNotifications()">notifications</span>
        <span class="material-icons me-2">account_circle</span>
        <span><?= htmlspecialchars($admin_name) ?></span> <!-- Display the admin name -->
    </div>
</div>
