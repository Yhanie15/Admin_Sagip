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
        /* Make the body take full height and enable scrolling if content overflows */
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Sidebar styling */
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

        #content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 15px;
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Topbar styles */
        .topbar {
            display: flex;
            justify-content: space-between;
            background-color: #f8f9fa;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

    <!-- Main Content (Right of Sidebar) -->
    <div id="content" class="d-flex flex-column">
        <!-- Topbar -->
        <?php include 'topbar.php'; ?>

        <!-- Page Title -->
        <h4 class="ms-4 mt-3">Rescuer Accounts</h4>

        <!-- Table for Rescuers -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
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
