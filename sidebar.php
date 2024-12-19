<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch admin details from session
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<div class="sidebar bg-dark text-white">
    <!-- Sidebar Header -->
    <div class="sidebar-header text-center py-4">
        <h4>SAGIP-SIKLAB</h4>
    </div>

    <!-- Sidebar Menu -->
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white">
                <span class="material-icons">dashboard</span> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="calls.php" class="nav-link text-white">
                <span class="material-icons">call</span> Calls Report
            </a>
        </li>
        <li class="nav-item">
            <a href="images.php" class="nav-link text-white">
                <span class="material-icons">image</span> Images Report
            </a>
        </li>
        <li class="nav-item">
            <a href="dispatches.php" class="nav-link text-white">
                <span class="material-icons">local_shipping</span> Dispatches
            </a>
        </li>

        <!--<li class="nav-item">
            <a href="incidents.php" class="nav-link text-white">
                <span class="material-icons">report_problem</span> Incidents
            </a>
        </li>
        <li class="nav-item">
            <a href="fire_stations.php" class="nav-link text-white">
                <span class="material-icons">fire_extinguisher</span> Fire Stations
            </a>
        </li>-->
        
        <li class="nav-item">
            <a href="reports.php" class="nav-link text-white">
                <span class="material-icons">article</span> Reports
            </a>
        </li>
        <!-- Users Dropdown -->
        <li class="nav-item">
            <a href="#usersMenu" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="usersMenu">
                <span class="material-icons">group</span> Users
                <span class="material-icons ms-auto">expand_more</span>
            </a>
            <div class="collapse" id="usersMenu">
                <ul class="nav flex-column ps-4">
                    <li class="nav-item">
                        <a href="admin_users.php" class="nav-link text-white">
                            <span class="material-icons">admin_panel_settings</span> Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="residents.php" class="nav-link text-white">
                            <span class="material-icons">people</span> Residents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="rescuers.php" class="nav-link text-white">
                            <span class="material-icons">person_pin</span> Rescuers
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="text-center mt-auto py-3">
    <a href="login.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center">
        <span class="material-icons me-2">logout</span> Logout
    </a>
    </div>
</div>

<style>
/* Sidebar Styles */
.sidebar {
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    background-color: #2b3e50;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.sidebar .nav-link {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: white;
    transition: background 0.3s ease;
}

.sidebar .nav-link:hover {
    background-color: #1a252f;
}

.sidebar .nav-link .material-icons {
    margin-right: 10px;
}

.nav-item .collapse {
    background-color: #34495e; /* Slightly different shade for dropdown */
    border-left: 3px solid #1a252f; /* Visual separation for dropdown */
}

/* Responsive Sidebar */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
    }
}

@media (max-width: 576px) {
    .sidebar {
        width: 150px;
    }
}

@media (max-width: 400px) {
    .sidebar {
        display: none; /* Hide sidebar for very small screens */
    }
}
</style>
