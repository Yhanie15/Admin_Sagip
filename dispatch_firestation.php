
<?php
// Include Firebase connection
include('dbcon.php');

// Get the report ID or call ID from the URL
$reportId = isset($_GET['reportId']) ? $_GET['reportId'] : null;
$callId = isset($_GET['callId']) ? $_GET['callId'] : null; // For Calls table

// Initialize variables
$location = '';
$senderName = '';
$incidentLat = null;
$incidentLon = null;
$currentReportKey = '';
$currentReportVia = '';

// Fetch the report details from Firebase using the report ID (Reports_Image) or call ID (Calls)
if ($reportId) {
    // Fetch report from Reports_Image
    $reportRef = $database->getReference('reports_image/' . $reportId);
    $report = $reportRef->getValue();

    if (!$report) {
        die("Report not found.");
    }

    $location = isset($report['location']) ? $report['location'] : 'Unknown Location';
    $senderName = isset($report['senderName']) ? $report['senderName'] : 'Anonymous';
    $incidentLat = isset($report['latitude']) ? (float)$report['latitude'] : null;
    $incidentLon = isset($report['longitude']) ? (float)$report['longitude'] : null;

    $currentReportKey = $reportId;
    $currentReportVia = "Image";
} elseif ($callId) {
    // Fetch report from Calls
    $callRef = $database->getReference('Calls/' . $callId);
    $call = $callRef->getValue();

    if (!$call) {
        die("Call report not found.");
    }

    $location = isset($call['address']) ? $call['address'] : 'Unknown Address';
    $senderName = isset($call['callerName']) ? $call['callerName'] : 'Anonymous';
    $incidentLat = isset($call['latitude']) ? (float)$call['latitude'] : null;
    $incidentLon = isset($call['longitude']) ? (float)$call['longitude'] : null;

    $currentReportKey = $callId;
    $currentReportVia = "Call";
} else {
    die("No valid report or call ID provided.");
}

// Check if latitude and longitude are available
if ($incidentLat === null || $incidentLon === null) {
    die("Incident latitude and longitude not provided in the report.");
}

// Fetch the nearest rescuers from Firebase
$rescuersRef = $database->getReference('rescuer');
$rescuersSnapshot = $rescuersRef->getSnapshot();
$rescuers = $rescuersSnapshot->getValue();

if (!$rescuers) {
    die("No rescuers found.");
}

// Logic to calculate nearest rescuers
$nearestRescuers = []; // This will hold the nearest rescuers

foreach ($rescuers as $rescuer) {
    // Ensure rescuer has necessary data
    if (!isset($rescuer['latitude']) || !isset($rescuer['longitude']) || !isset($rescuer['stationName']) || !isset($rescuer['exactLocation'])) {
        continue; // Skip incomplete rescuer entries
    }

    // Get latitude and longitude for the fire station (rescuer)
    $stationLat = (float)$rescuer['latitude'];
    $stationLon = (float)$rescuer['longitude'];
    $stationName = $rescuer['stationName'];
    $exactLocation = $rescuer['exactLocation'];

    // Calculate the distance between the fire incident and the fire station
    $distance = haversine($incidentLat, $incidentLon, $stationLat, $stationLon);

    // Round the distance to one decimal place
    $distance = round($distance, 1);

    // Add the rescuer to the list with calculated distance
    $rescuer['distance'] = $distance;
    $nearestRescuers[] = $rescuer;
}

// Sort by distance (closest first)
usort($nearestRescuers, function ($a, $b) {
    return $a['distance'] <=> $b['distance'];
});

// Haversine formula to calculate distance between two lat/lon points
function haversine($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in km

    // Convert degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Haversine formula
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat / 2) * sin($dlat / 2) +
         cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Distance in km
    return $earthRadius * $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Nearest Firestation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

        /* Styling for the location and reported by section */
        .incident-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .incident-info p {
            margin: 0;
            font-size: 16px;
        }

        .gap-3 {
            gap: 1rem; /* Adds spacing between the boxes */
        }

        /* Table Styling */
        .table td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            cursor: pointer;
        }

        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }

        /* Button Styling */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
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

        <div class="container mt-4">
            <!-- Fire Incident Location and Reported By Section (Combined in one container) -->
            <div class="incident-info">
                <p><strong>Fire Incident Location:</strong> <?php echo htmlspecialchars($location); ?></p>
                <p><strong>Reported By:</strong> <?php echo htmlspecialchars($senderName); ?></p>
            </div>

            <!-- Nearest Firestations Table -->
            <h5>Available Nearby Firestations</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Station Name</th>
                        <th>Location</th>
                        <!---<th>Distance (km)</th>-->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nearestRescuers as $rescuer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rescuer['stationName']); ?></td>
                            <td><?php echo htmlspecialchars($rescuer['exactLocation']); ?></td>
                            <!--<td><?php echo $rescuer['distance']; ?> km</td>-->
                            <td>
                                <button class="btn btn-success" onclick="dispatchFirestation(
                                    '<?php echo htmlspecialchars($rescuer['rescuerID']); ?>',
                                    '<?php echo htmlspecialchars($currentReportKey); ?>',
                                    '<?php echo htmlspecialchars($location); ?>',
                                    '<?php echo htmlspecialchars($rescuer['stationName']); ?>',
                                    '<?php echo htmlspecialchars($currentReportVia); ?>',
                                    '<?php echo $incidentLat; ?>', // Include the latitude
                                    '<?php echo $incidentLon; ?>'  // Include the longitude
                                )">Dispatch</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function dispatchFirestation(rescuerID, reportKey, location, fireStationName, reportVia, incidentLat, incidentLon) {
    fetch('dispatch_rescuer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rescuerID=' + encodeURIComponent(rescuerID) + 
              '&reportKey=' + encodeURIComponent(reportKey) + 
              '&location=' + encodeURIComponent(location) + 
              '&fireStationName=' + encodeURIComponent(fireStationName) +
              '&reportVia=' + encodeURIComponent(reportVia) +
              '&latitude=' + encodeURIComponent(incidentLat) +
              '&longitude=' + encodeURIComponent(incidentLon)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Failed to dispatch firestation: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while dispatching the firestation.');
    });
    }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
