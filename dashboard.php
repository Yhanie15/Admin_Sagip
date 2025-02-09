<?php
// dashboard.php

// Start the session and include the database connection
session_start();
include('dbcon.php'); // sets up $database

if (!$database) {
    die("Database connection failed.");
}

// The initial data fetching is not required here as we'll use AJAX to fetch data
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [HTML Head Content] -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Mapbox GL JS CSS -->
    <link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
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

        /* Custom marker styles */
        .marker {
            width: 30px;
            height: 30px;
            background-size: cover;
            border-radius: 50%;
            cursor: pointer;
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
                <div id="activeIncidents">0</div>
            </div>
            <div class="stat-box bg-success">
                <strong>Dispatched Fire Trucks</strong>
                <div id="dispatchedFireTrucks">0</div>
            </div>
            <div class="stat-box bg-primary">
                <strong>Resolved Incidents</strong>
                <div id="resolvedIncidents">0</div>
            </div>
            <div class="stat-box bg-danger">
                <strong>Total Incidents<br>Report Today</strong>
                <div id="totalReportsToday">0</div>
            </div>
        </div>

        <!-- Map -->
        <div id="map_dashboard" class="flex-grow-1 rounded border mt-3"></div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Mapbox GL JS -->
    <script src='https://api.tiles.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.js'></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        mapboxgl.accessToken = 'pk.eyJ1IjoieWhhbmllMTUiLCJhIjoiY2x5bHBrenB1MGxmczJpczYxbjRxbGxsYSJ9.DPO8TGv3Z4Q9zg08WhfoCQ';
        var map = new mapboxgl.Map({
            container: 'map_dashboard',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [121.0437, 14.6760], // Default center: Quezon City
            zoom: 12
        });

        // Add navigation controls to the map (optional)
        map.addControl(new mapboxgl.NavigationControl());

        // URLs for custom icons
        var firetruckIconURL = 'https://img.icons8.com/color/48/000000/fire-truck.png'; // Firetruck icon from Icons8
        var fireIconURL = 'https://img.icons8.com/color/48/000000/fire-element.png'; // Fire icon from Icons8

        // Initialize empty arrays to keep track of current markers
        var residentMarkers = [];
        var rescuerMarkers = [];

        // Function to create a custom marker element
        function createCustomMarker(iconURL) {
            var el = document.createElement('div');
            el.className = 'marker';
            el.style.backgroundImage = 'url(' + iconURL + ')';
            return el;
        }

        // Function to clear existing markers from the map
        function clearMarkers() {
            residentMarkers.forEach(function(marker) {
                marker.remove();
            });
            rescuerMarkers.forEach(function(marker) {
                marker.remove();
            });
            residentMarkers = [];
            rescuerMarkers = [];
        }

        // Function to update the stats and map with fetched data
        function updateDashboard(data) {
            // Update statistics
            document.getElementById('activeIncidents').innerText = data.activeIncidents;
            document.getElementById('dispatchedFireTrucks').innerText = data.dispatchedFireTrucks;
            document.getElementById('resolvedIncidents').innerText = data.resolvedIncidents;
            document.getElementById('totalReportsToday').innerText = data.totalReportsToday;

            // Clear existing markers
            clearMarkers();

            // Add new markers to the map
            data.dispatches.forEach(function(dispatch) {
                // Add resident/fire scene marker
                var residentLat = dispatch.resident.latitude;
                var residentLng = dispatch.resident.longitude;

                var residentMarker = new mapboxgl.Marker(createCustomMarker(fireIconURL))
                    .setLngLat([residentLng, residentLat])
                    .addTo(map);

                // Optionally, add a popup for the resident marker
                residentMarker.setPopup(new mapboxgl.Popup({ offset: 25 })
                    .setHTML('<h5>Resident Location</h5>'));

                residentMarkers.push(residentMarker);

                // Add rescuer/firetruck marker
                var rescuerLat = dispatch.rescuer.latitude;
                var rescuerLng = dispatch.rescuer.longitude;

                var rescuerMarker = new mapboxgl.Marker(createCustomMarker(firetruckIconURL))
                    .setLngLat([rescuerLng, rescuerLat])
                    .addTo(map);

                // Optionally, add a popup for the rescuer marker
                rescuerMarker.setPopup(new mapboxgl.Popup({ offset: 25 })
                    .setHTML('<h5>Rescuer Location</h5>'));

                rescuerMarkers.push(rescuerMarker);
            });

            // Optionally, adjust the map's bounds to fit all markers
            adjustMapBounds(data.dispatches);
        }

        // Function to adjust map bounds to fit all markers
        function adjustMapBounds(dispatches) {
            if (dispatches.length === 0) return;

            var bounds = new mapboxgl.LngLatBounds();

            dispatches.forEach(function(dispatch) {
                bounds.extend([dispatch.resident.longitude, dispatch.resident.latitude]);
                bounds.extend([dispatch.rescuer.longitude, dispatch.rescuer.latitude]);
            });

            map.fitBounds(bounds, { padding: 50, maxZoom: 15 });
        }

        // Function to fetch data from the server
        function fetchData() {
            fetch('get_dispatch_data.php')
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.error) {
                        console.error('Error from server:', data.error);
                        return;
                    }
                    updateDashboard(data);
                })
                .catch(function(error) {
                    console.error('Fetch error:', error);
                });
        }

        // Initial data fetch
        fetchData();

        // Set interval to fetch data every 2 seconds (2000 milliseconds)
        setInterval(fetchData, 2000);
    });
    </script>
</body>
</html>
