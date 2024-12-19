<?php
// Include Firebase connection
include('dbcon.php');

// Get the report key from the URL
$reportKey = isset($_GET['reportKey']) ? $_GET['reportKey'] : null;

// Fetch the report details from Firebase using the report key
$reportRef = $database->getReference('reports_image/' . $reportKey);
$report = $reportRef->getValue();

// Fetch the nearest rescuers from Firebase
$rescuersRef = $database->getReference('rescuer');
$rescuersSnapshot = $rescuersRef->getSnapshot();
$rescuers = $rescuersSnapshot->getValue();

// Logic to calculate nearest rescuers
$nearestRescuers = []; // This will hold the nearest rescuers
foreach ($rescuers as $rescuer) {
    $distance = rand(1, 50); // Random distance for now (replace with actual calculation)
    $rescuer['distance'] = $distance;
    $nearestRescuers[] = $rescuer;
}

// Sort by distance (closest first)
usort($nearestRescuers, function ($a, $b) {
    return $a['distance'] - $b['distance'];
});
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
                <p><strong>Fire Incident Location:</strong> <?php echo htmlspecialchars($report['location']); ?></p>
                <p><strong>Reported By:</strong> <?php echo htmlspecialchars($report['senderName']); ?></p>
            </div>

            <!-- Nearest Firestations Table -->
            <h5>Available Nearby Firestations</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Station Name</th>
                        <th>Location</th>
                        <th>Distance (km)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nearestRescuers as $rescuer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rescuer['stationName']); ?></td>
                            <td><?php echo htmlspecialchars($rescuer['exactLocation']); ?></td>
                            <td><?php echo $rescuer['distance']; ?> km</td>
                            <td>
                                <button 
                                    class="btn btn-success" 
                                    onclick="dispatchFirestation('<?php echo htmlspecialchars($rescuer['rescuerID']); ?>', '<?php echo htmlspecialchars($reportKey); ?>', '<?php echo htmlspecialchars($report['location']); ?>')">
                                    Dispatch
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Function to dispatch the nearest firestation
        function dispatchFirestation(rescuerID, reportKey, location) {
            fetch('dispatch_rescuer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'rescuerID=' + encodeURIComponent(rescuerID) + '&reportKey=' + encodeURIComponent(reportKey) + '&location=' + encodeURIComponent(location),
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert(data.message);
                } else {
                    alert('Failed to dispatch firestation: ' + data.message);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('An error occurred while dispatching the firestation.');
            });
        }   
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
