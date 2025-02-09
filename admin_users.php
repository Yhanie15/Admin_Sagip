<?php
// Include Firebase connection (Ensure that dbcon.php handles the Firebase connection)
include('dbcon.php');

// Fetch all admin data from Firebase
$adminRef = $database->getReference('admin');
$adminSnapshot = $adminRef->getSnapshot();
$admins = $adminSnapshot->getValue();  // Get all admin data

// Check for success or error messages
$success = isset($_GET['success']) ? $_GET['success'] : null;
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Sidebar styles */
        #sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #2b3e50;
            overflow-y: auto;
            transition: width 0.3s;
        }

        #content {
            margin-left: 250px;
            padding: 15px;
            min-height: 100vh;
            overflow-y: auto;
            transition: margin-left 0.3s, width 0.3s;
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
                display: none; /* Hide the sidebar */
            }
            #content {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Toast Container Positioning */
        .alert-position {
            position: fixed;
            top: 80px; /* Adjust based on your topbar height */
            right: 30px;
            z-index: 2000; /* Ensure it's above other elements */
            min-width: 300px;
        }

        /* Additional Styles for Buttons */
        .action-btn {
            width: 80px; /* Fixed width for consistency */
            margin-right: 5px; /* Spacing between buttons */
        }

        /* Adjust the form elements */
        .form-inline {
            display: flex;
            align-items: center;
        }

        .form-inline label {
            margin-right: 10px;
        }

        /* Ensure the modal inputs take full width */
        .modal .form-control {
            width: 100%;
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

        <!-- Header with Title and Add Button -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h4>Admin Users</h4>
            <!-- Button to Open the Modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                <span class="material-icons align-middle">person_add</span> Add Admin
            </button>
        </div>

        <!-- Display Success or Error Messages -->
        <?php if ($success || $error): ?>
            <div class="alert-position">
                <?php if ($success === 'true'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Admin account successfully created.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if ($success === 'false'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Failed to create admin account. <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Table for Admin Users -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Account Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($admins): ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['name']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($admin['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No admin accounts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Creating Admin Account -->
    <div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="process_signup.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAdminModalLabel">Create Admin Account</h5>
                       <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
                    </div>
                    <div class="modal-body">
                        <!-- Name Field -->
                        <div class="mb-3">
                            <label for="adminName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="adminName" name="name" required>
                        </div>
                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="adminEmail" name="email" required>
                        </div>
                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="password" required>
                        </div>
                        <!-- Hidden User Type Field -->
                        <input type="hidden" name="user_type" value="admin">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (including Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript to Auto-Dismiss Alerts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select all alerts with the 'alert' class inside the alert-position container
            var alerts = document.querySelectorAll('.alert-position .alert');

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
