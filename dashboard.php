<?php 
include('dbcon.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.js'></script>
    <style>
        /* Make the body take full height and enable scrolling if content overflows */
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        #sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #2b3e50;
            overflow-y: auto;
        }

        #content {
            margin-left: 250px; /* Same width as the sidebar */
            width: calc(100% - 250px);
            padding: 15px;
            min-height: 100vh;
            overflow-y: auto; /* Enable scrolling for the main content */
        }

        /* Flexbox styles for statistics boxes */
        .stat-box {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 200px; /* Adjust width */
            height: 100px; /* Adjust height */
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin: 10px; /* Optional for additional spacing */
        }

        .gap-3 {
            gap: 1rem; /* Adds spacing between the boxes */
        }

        /* Background color styles for each type */
        .bg-primary {
            background-color: #007bff;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: black;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        #map_dashboard {
            height: 440px;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            #sidebar {
                width: 200px;
            }
            #content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 576px) {
            #sidebar {
                width: 150px;
            }
            #content {
                margin-left: 150px;
                width: calc(100% - 150px);
            }
        }

        /* Full width content for very small screens */
        @media (max-width: 400px) {
            #sidebar {
                display: none; /* Hide the sidebar */
            }
            #content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div id="content" class="d-flex flex-column">
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>

        <!-- Header -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-3">
            
            <div class="stat-box bg-warning">
                <strong>Active Incidents</strong>
                <div>2</div>
            </div>
            <div class="stat-box bg-success">
                <strong>Dispatched Fire Trucks</strong>
                <div>6</div>
            </div>
            <div class="stat-box bg-primary">
                <strong>Resolved Incidents</strong>
                <div>10</div>
            </div>
            <div class="stat-box bg-danger">
                <strong>Total Incidents<br>Report Today</strong>
                <div>10</div>
            </div>
        </div>

        <!-- Map -->
        <div id="map_dashboard" class="flex-grow-1 rounded border mt-3"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>
