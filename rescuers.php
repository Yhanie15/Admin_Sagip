<?php
// Include the database connection file (dbcon.php)
require_once 'dbcon.php';  // Assuming dbcon.php handles the Firebase connection

// Define the six districts
$districts = [
    'District 1',
    'District 2',
    'District 3',
    'District 4',
    'District 5',
    'District 6'
];

// Get the selected district from GET parameters if available
$selectedDistrict = isset($_GET['district']) ? $_GET['district'] : '';

// Initialize variables for messages
$successMessage = '';
$errorMessage = '';

// Handling POST requests for updating rescuer status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rescuerId = $_POST['rescuerId'] ?? '';
    $newStatus = $_POST['rescuer_status'] ?? '';

    if ($rescuerId && in_array($newStatus, ['Approved', 'Rejected'])) {
        try {
            $database->getReference("rescuer/{$rescuerId}")
                ->update(['rescuer_status' => $newStatus]);

            // Redirect to the same page with a success message
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&status=" . urlencode($newStatus));
            exit();
        } catch (Exception $e) {
            // Redirect with an error message
            header("Location: " . $_SERVER['PHP_SELF'] . "?error=1&message=" . urlencode($e->getMessage()));
            exit();
        }
    } else {
        // Redirect with an error message for invalid input
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1&message=" . urlencode("Invalid request."));
        exit();
    }
}

// Check for success or error messages in GET parameters
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['status'])) {
    $status = htmlspecialchars($_GET['status']);
    $successMessage = "Rescuer status updated to <strong>{$status}</strong>.";
}

if (isset($_GET['error']) && $_GET['error'] == '1' && isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    $errorMessage = "Error: {$message}";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- (Head content remains the same) -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rescuer Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* (CSS styles remain the same) */
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

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
            margin-top: 20px:;
            margin-left: 250px;
            width: calc(100% - 250px);
            padding-top: 70px;
            padding-bottom: 15px;
            padding-left: 30px;
            padding-right: 30px;
            min-height: 100vh;
            overflow-y: auto;
        }
        
        #topbar {
            position: fixed;
            top: 0;
            left: 250px; /* Adjust this if your sidebar width changes */
            width: calc(100% - 250px);
            z-index: 1030; /* Ensure it is above other content */
            background-color: #fff; /* Background color to match images.php */
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 200px;
            }
            #content {
                padding-top: 100px;
                margin-left: 200px;
                width: calc(100% - 200px);
            }
            #topbar {
                left: 200px;
                width: calc(100% - 200px);
            }
        }

        @media (max-width: 576px) {
            #sidebar {
                width: 150px;
            }
            #content {
                padding-top: 100px;
                margin-left: 150px;
                width: calc(100% - 150px);
            }
            #topbar {
                left: 150px;
                width: calc(100% - 150px);
            }
        }

        @media (max-width: 400px) {
            #sidebar {
                display: none;
            }
            #content {
                margin-left: 0;
                width: 100%;
            }
            #topbar {
                left: 0;
                width: 100%;
            }
        }

        /* Additional Styles for Buttons */
        .action-btn {
            width: 80px; /* Fixed width for consistency */
            margin-right: 5px; /* Spacing between buttons */
        }

        /* Adjust the select dropdown to align properly */
        .filter-form {
            max-width: 200px;
        }

        /* Alert positioning */
        .alert-position {
            position: fixed;
            top: 80px; /* Adjust based on your topbar height */
            right: 30px;
            z-index: 2000;
            min-width: 300px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <!-- Topbar -->
    <div id="topbar">
        <?php include 'topbar.php'; ?> <!-- Ensure this includes the correct file -->
    </div>

    <!-- Main Content -->
    <div id="content" class="d-flex flex-column">

        <!-- Display Success or Error Messages -->
        <?php if ($successMessage || $errorMessage): ?>
            <div class="alert-position">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $successMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $errorMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Header with Title and Filter Dropdown -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <!-- Removed 'ms-4' to eliminate left margin -->
            <h4 class="mb-0">Rescuer Accounts</h4>
            <form method="GET" class="d-flex align-items-center filter-form">
                <!-- <label for="district" class="me-2 mb-0">Filter by District:</label> -->
                <select name="district" id="district" class="form-select" onchange="this.form.submit()">
                    <option value="">All Districts</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?php echo htmlspecialchars($district); ?>" <?php if ($selectedDistrict === $district) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($district); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Station Name</th>
                        <th>District</th>
                        <th>Barangay</th>
                        <th>Plate Number</th>
                        <th>Account Created</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetching rescuer data from Firebase
                    $ref = 'rescuer';
                    $rescuerData = $database->getReference($ref)->getValue();

                    if ($rescuerData) {
                        foreach ($rescuerData as $rescuerId => $rescuer) {
                            $assignedBarangay = $rescuer['assignedBarangay'] ?? 'N/A';
                            $assignedDistrict = $rescuer['assignedDistrict'] ?? 'N/A';
                            $plateNumber = $rescuer['plateNumber'] ?? 'N/A';
                            $stationName = $rescuer['stationName'] ?? 'N/A';
                            // $truckNumber = $rescuer['truckNumber'] ?? 'N/A'; // Removed Truck Number
                            $createdAtRaw = $rescuer['createdAt'] ?? 'N/A';
                            $rescuerStatus = $rescuer['rescuer_status'] ?? 'Pending';

                            // Apply district filter if selected
                            if ($selectedDistrict && $assignedDistrict !== $selectedDistrict) {
                                continue; // Skip this rescuer as it doesn't match the selected district
                            }

                            // Optionally, skip already approved or rejected rescuers
                            // Uncomment the following lines if you want to hide approved/rejected rescuers
                            /*
                            if (in_array($rescuerStatus, ['Approved', 'Rejected'])) {
                                continue;
                            }
                            */

                            // Format the createdAt date
                            if ($createdAtRaw !== 'N/A') {
                                try {
                                    $date = new DateTime($createdAtRaw);
                                    $createdAt = $date->format('M. j, Y g:i A'); // e.g., Jan. 5, 2025 7:35 PM
                                } catch (Exception $e) {
                                    $createdAt = $createdAtRaw; // Fallback to original if parsing fails
                                }
                            } else {
                                $createdAt = 'N/A';
                            }

                            // Styling for different statuses
                            $rowClass = '';
                            if ($rescuerStatus === 'Approved') {
                                $rowClass = 'table-success';
                            } elseif ($rescuerStatus === 'Rejected') {
                                $rowClass = 'table-danger';
                            } elseif ($rescuerStatus === 'Pending') {
                                $rowClass = 'table-warning';
                            }

                            echo "
                                <tr class='{$rowClass}'>
                                    <td>{$stationName}</td>
                                    <td>{$assignedDistrict}</td>
                                    <td>{$assignedBarangay}</td>
                                    <td>{$plateNumber}</td>
                                    <td>{$createdAt}</td>
                                    <td>{$rescuerStatus}</td>
                                    <td>
                                        <div class='d-flex'>
                                            <form method='POST' class='d-inline me-1'>
                                                <input type='hidden' name='rescuerId' value='{$rescuerId}'>
                                                <button type='submit' name='rescuer_status' value='Approved' class='btn btn-success btn-sm action-btn'>Approve</button>
                                            </form>
                                            <form method='POST' class='d-inline'>
                                                <input type='hidden' name='rescuerId' value='{$rescuerId}'>
                                                <button type='submit' name='rescuer_status' value='Rejected' class='btn btn-danger btn-sm action-btn'>Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            ";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No Rescuers Found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS and optional Popper.js for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Optional: Automatically hide alerts after a certain time -->
    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Select all alerts with the 'alert' class
            var alerts = document.querySelectorAll('.alert');

            alerts.forEach(function(alert) {
                // Set a timeout to remove the alert after 5 seconds (5000 milliseconds)
                setTimeout(function () {
                    // Use Bootstrap's alert dispose method
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
