<?php
// Include the database connection file (dbcon.php)
require_once 'dbcon.php';  // Assuming dbcon.php handles the Firebase connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescuer Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Sidebar styles */
        #sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2b3e50;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: width 0.3s;
        }

        /* Content section (table) styling */
        #content {
            margin-left: 250px; /* Set this to the sidebar width */
            padding: 15px;
        }

        /* Table responsiveness */
        .table-responsive {
            overflow-x: auto;
        }

        /* Ensures proper display of table */
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        /* No need for hamburger button */
        #toggleSidebar {
            display: none;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Main Content (Right of Sidebar) -->
    <div id="content">
        <!-- Topbar -->
        <div class="d-flex justify-content-between bg-light p-3 shadow-sm">
            <div>
                <span class="material-icons">notifications</span>
                <span class="material-icons">account_circle</span>
                <span>Juan Masipag</span>
            </div>
        </div>

        <h4 class="ms-4 mt-3">Rescuer Accounts</h4>

        <!-- Table for Rescuers -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Station Name</th>
                        <th>Truck Number</th>
                        <th>Barangay</th>
                        <th>District</th>
                        <th>Account Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetching rescuer data from Firebase using dbcon.php connection
                    $ref = 'rescuer';  // Your Firebase collection path for rescuer
                    $rescuerData = $database->getReference($ref)->getValue();

                    if ($rescuerData) {
                        foreach ($rescuerData as $rescuerId => $rescuer) {
                            $assignedBarangay = isset($rescuer['assignedBarangay']) ? $rescuer['assignedBarangay'] : 'N/A';
                            $assignedDistrict = isset($rescuer['assignedDistrict']) ? $rescuer['assignedDistrict'] : 'N/A';
                            $plateNumber = isset($rescuer['plateNumber']) ? $rescuer['plateNumber'] : 'N/A';
                            $stationName = isset($rescuer['stationName']) ? $rescuer['stationName'] : 'N/A';
                            $truckNumber = isset($rescuer['truckNumber']) ? $rescuer['truckNumber'] : 'N/A';
                            $createdAt = isset($rescuer['createdAt']) ? $rescuer['createdAt'] : 'N/A';

                            // Displaying the information in the table
                            echo "
                                <tr>
                                    <td>{$plateNumber}</td>
                                    <td>{$stationName}</td>
                                    <td>{$truckNumber}</td>
                                    <td>{$assignedBarangay}</td>
                                    <td>{$assignedDistrict}</td>
                                    <td>{$createdAt}</td>
                                </tr>
                            ";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
