<?php
// Include the Firebase connection file (dbcon.php)
require 'dbcon.php'; // Ensure this path is correct!

// Fetch resident data from Firebase
$residentReference = $database->getReference('resident'); // Fetch data from the 'resident' node
$residents = $residentReference->getValue(); // Get all the residents' data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Sidebar and Topbar styling (same as in the dashboard) */
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
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 15px;
            min-height: 100vh;
            overflow-y: auto;
        }

        .table thead {
            background-color: #f1f1f1;
        }

        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* Optional: Style for table headers */
        .table th {
            text-align: center;
        }

        /* Optional: Style for the action button in the table */
        .table td button {
            padding: 5px 10px;
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

        @media (max-width: 400px) {
            #sidebar {
                display: none; /* Hide sidebar for very small screens */
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

        <!-- Page Title -->
        <h4 class="ms-4 mt-3">Residents Account</h4>

        <!-- Table for Resident Accounts -->
        <div class="container-fluid mt-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Account Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Check if the residents are found
                        if ($residents) {
                            foreach ($residents as $resident_id => $resident_data) {
                                // Fetch resident details
                                $name = isset($resident_data['name']) ? $resident_data['name'] : 'No name provided';
                                $email = isset($resident_data['email']) ? $resident_data['email'] : 'No email';
                                $mobile = isset($resident_data['mobile']) ? $resident_data['mobile'] : 'No mobile';
                                $createdAt = isset($resident_data['createdAt']) ? $resident_data['createdAt'] : 'No date';

                                // Convert Firebase timestamp to a readable date format
                                if ($createdAt !== 'No date') {
                                    $createdAt = date('F j, Y, g:i a', strtotime($createdAt));
                                }

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($name) . "</td>";
                                echo "<td>" . htmlspecialchars($email) . "</td>";
                                echo "<td>" . htmlspecialchars($mobile) . "</td>";
                                echo "<td>" . htmlspecialchars($createdAt) . "</td>";
                                echo "<td>
                                        <button class='btn btn-info' data-bs-toggle='modal' data-bs-target='#viewModal" . $resident_id . "'>View</button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No residents found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal for View Resident Details -->
        <?php
        if ($residents) {
            foreach ($residents as $resident_id => $resident_data) {
                $name = isset($resident_data['name']) ? $resident_data['name'] : 'No name';
                $barangay = isset($resident_data['barangay']) ? $resident_data['barangay'] : 'No barangay';
                $district = isset($resident_data['district']) ? $resident_data['district'] : 'No district';
                $email = isset($resident_data['email']) ? $resident_data['email'] : 'No email';
                $mobile = isset($resident_data['mobile']) ? $resident_data['mobile'] : 'No mobile';
                $createdAt = isset($resident_data['createdAt']) ? $resident_data['createdAt'] : 'No date';
                if ($createdAt !== 'No date') {
                    $createdAt = date('F j, Y, g:i a', strtotime($createdAt));
                }

                echo "
                    <div class='modal fade' id='viewModal" . $resident_id . "' tabindex='-1' aria-labelledby='viewModalLabel" . $resident_id . "' aria-hidden='true'>
                        <div class='modal-dialog'>
                            <div class='modal-content'>
                                <div class='modal-header'>
                                    <h5 class='modal-title' id='viewModalLabel" . $resident_id . "'>Resident Details</h5>
                                    <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                </div>
                                <div class='modal-body'>
                                    <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                                    <p><strong>Barangay:</strong> " . htmlspecialchars($barangay) . "</p>
                                    <p><strong>District:</strong> " . htmlspecialchars($district) . "</p>
                                    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                                    <p><strong>Mobile:</strong> " . htmlspecialchars($mobile) . "</p>
                                    <p><strong>Account Created:</strong> " . htmlspecialchars($createdAt) . "</p>
                                </div>
                                <div class='modal-footer'>
                                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                ";
            }
        }
        ?>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
