<?php
include('dbcon.php'); // Use the existing Firebase database connection

// Fetch image reports from Firebase
$ref = 'reports_image';
$reports = $database->getReference($ref)->getValue();

// Count statistics for the reports
$totalReports = count($reports);
$resolvedReports = 0;
$inProgressReports = 0;
$pendingReports = 0;

foreach ($reports as $report) {
    $status = strtolower($report['status'] ?? 'pending');
    if ($status === 'resolved') {
        $resolvedReports++;
    } elseif ($status === 'in progress') {
        $inProgressReports++;
    } elseif ($status === 'pending') {
        $pendingReports++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Images Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Main Styling */
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

        /* Stat boxes layout */
        .stat-box {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 200px;
            height: 100px;
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin: 10px;
        }

        .gap-3 {
            gap: 1rem;
        }

        /* Color boxes */
        .bg-primary { background-color: #007bff; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-success { background-color: #28a745; color: white; }
        .bg-danger { background-color: #dc3545; color: white; }
        .bg-info { background-color: #17a2b8; color: white; }

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

        /* Modal Styling */
        .modal-dialog {
            max-width: 50%; /* Adjust modal width */
        }

        .modal-content {
            overflow-y: auto; /* Enable vertical scrolling inside the modal */
        }

        .modal-body img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 8px;
            margin-bottom: 15px;
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

        <!-- Stat Boxes -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-3">
            <div class="stat-box bg-danger">
                <strong>Pending Reports</strong>
                <div><?= $pendingReports ?></div>
            </div>
            <div class="stat-box bg-warning">
                <strong>In Progress</strong>
                <div><?= $inProgressReports ?></div>
            </div>
            <div class="stat-box bg-success">
                <strong>Resolved Reports</strong>
                <div><?= $resolvedReports ?></div>
            </div>
            <div class="stat-box bg-primary">
                <strong>Total Reports Today</strong>
                <div><?= $totalReports ?></div>
            </div>
        
            
        </div>

        <!-- Page Title -->
        <h4 class="ms-4 mt-3">Image Reports</h4>

        <!-- Table for Image Reports -->
        <div class="container-fluid mt-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name of Reporter</th>
                        <th>Image</th>
                        <th>Location</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports): ?>
                        <?php foreach ($reports as $reportKey => $report): ?>
                            <tr data-bs-toggle="modal" data-bs-target="#reportModal<?= $reportKey ?>">
                                <td><?= htmlspecialchars($report['senderName'] ?? 'Unknown') ?></td>
                                <td>
                                    <img src="<?= htmlspecialchars($report['imageUrl'] ?? 'https://via.placeholder.com/60') ?>" alt="Report Image">
                                </td>
                                <td><?= htmlspecialchars($report['location'] ?? 'No location provided') ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($report['timestamp'] ?? 'now'))) ?></td>
                                <td>
                                    <?php
                                    $status = $report['status'] ?? 'Pending';
                                    $badgeClass = match (strtolower($status)) {
                                        'resolved' => 'bg-success',
                                        'in progress' => 'bg-warning',
                                        'pending' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                </td>
                            </tr>

                            <!-- Modal for Viewing Report -->
                            <div class="modal fade" id="reportModal<?= $reportKey ?>" tabindex="-1" aria-labelledby="reportModalLabel<?= $reportKey ?>" aria-hidden="true">
                                <div class="modal-dialog modal-md">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="reportModalLabel<?= $reportKey ?>">Report Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="<?= htmlspecialchars($report['imageUrl'] ?? 'https://via.placeholder.com/600') ?>" alt="Large Report Image">
                                            <p><strong>Location:</strong> <?= htmlspecialchars($report['location'] ?? 'No location provided') ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <a href="dispatch_firestation.php?reportKey=<?= $reportKey ?>" class="btn btn-success">Accept Report</a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No image reports available.</td>
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
