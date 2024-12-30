<?php
// Include Firebase connection
include('dbcon.php');

// Fetch all dispatches from Firebase
$dispatchesRef = $database->getReference('dispatches');
$dispatchesSnapshot = $dispatchesRef->getSnapshot();
$dispatches = $dispatchesSnapshot->getValue();

// Ensure that dispatches is not empty or null
if ($dispatches) {
    // Fetch the station names dynamically from the rescuer data
    foreach ($dispatches as $key => $dispatch) {
        if (isset($dispatch['rescuerID'])) {
            $rescuerID = $dispatch['rescuerID'];
            // Fetch the station name from the rescuer node using rescuerID
            $rescuerRef = $database->getReference('rescuer/' . $rescuerID);
            $rescuerData = $rescuerRef->getValue();

            // Ensure rescuer data is fetched successfully
            if ($rescuerData && isset($rescuerData['stationName'])) {
                $dispatches[$key]['stationName'] = $rescuerData['stationName']; // Store the station name
            } else {
                // If no station name found, you can set a default value or handle the error
                $dispatches[$key]['stationName'] = 'Unknown Station';
            }
        }
    }

    // Sort the dispatches by 'dispatchTime' in descending order (newer dispatches first)
    usort($dispatches, function($a, $b) {
        // Assuming 'dispatchTime' is in a valid format like "Y-m-d H:i:s"
        $timeA = strtotime($a['dispatchTime']);
        $timeB = strtotime($b['dispatchTime']);
        return $timeB - $timeA; // Sort in descending order
    });

} else {
    $dispatches = []; // If no dispatches found, set an empty array
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatched Notifications</title>
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

        /* Table Styling */
        .table td, .table th {
            vertical-align: middle;
            text-align: center;
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
            <!-- Table for Displaying Dispatches -->
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Fire Station</th>
                        <th>Fire Incident Location</th>
                        <th>Status</th>
                        <th>Time of Dispatch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dispatches): ?>
                        <?php foreach ($dispatches as $dispatch): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dispatch['stationName']); ?></td>
                                <td><?php echo htmlspecialchars($dispatch['location']); ?></td>
                                <td>
                                    <?php 
                                    // Display status based on dispatch state
                                    $status = $dispatch['status'];
                                    echo ucfirst($status);
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    // Format dispatch time to 12-hour format with AM/PM and retain the date
                                    $dispatchTime = strtotime($dispatch['dispatchTime']);
                                    echo date("M d, Y g:i A", $dispatchTime); // Full date and 12-hour format with AM/PM
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No dispatches found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
