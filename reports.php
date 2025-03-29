<?php
// report.php

// Always start session and fetch reports data
include('fetchReports.php');

// Check if an individual report is being viewed
$viewingReport = isset($_GET['id']) && !empty($_GET['id']);
$reportDetails = null;
if ($viewingReport) {
    $reportID = $_GET['id'];
    $reportRef = $database->getReference('report/' . $reportID);
    $reportSnapshot = $reportRef->getSnapshot();
    $reportDetails = $reportSnapshot->getValue();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Icons CSS: REQUIRED for icons to appear -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        #content {
            margin-left: 250px; 
            padding: 20px;
        }
        .chart-container {
            max-width: 900px;
            height: 500px;
            margin: 40px auto;
        }
        .graph-description {
            text-align: center;
            margin-top: 10px;
            font-style: italic;
            color: #555;
        }
        /* Example styling for icons, if needed */
        .material-icons {
            vertical-align: middle;
            font-size: 24px; /* Adjust as desired */
        }
    </style>
</head>
<body>
    <!-- Sidebar (includes your Material Icons) -->
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <!-- Topbar (includes your Material Icons) -->
        <?php include 'topbar.php'; ?>
       
        <?php if (!$viewingReport): ?>
            <!-- Include the graph block only when not viewing a single report -->
            <?php include('graph.php'); ?>
           
        <?php endif; ?>

        <!-- Your reports table or single report view code goes here -->
        <?php if ($viewingReport && $reportDetails): ?>
            <h1 class="text-center my-4">Report Details</h1>
            <div class="card">
                <div class="card-header">
                    <strong>Fire Station:</strong> <?php echo $reportDetails['fireStation'] ?? 'Unknown'; ?>
                </div>
                <div class="card-body">
                    <?php foreach ($reportDetails as $key => $value): ?>
                        <p><strong><?php echo ucfirst($key); ?>:</strong> <?php echo !empty($value) ? $value : 'N/A'; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="reports.php" class="btn btn-secondary mt-3">Back to Reports</a>
        <?php else: ?>
            <h1 class="text-center my-4">Reports</h1>
            <div class="table-container">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Fire Station</th>
                            <th>Status</th>
                            <th>Submitted By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reportData): ?>
                            <?php $counter = 1; foreach ($reportData as $reportID => $report): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo $report['fireStation'] ?? 'Unknown Fire Station'; ?></td>
                                    <td><?php echo ucfirst($report['status'] ?? 'Unknown'); ?></td>
                                    <td><?php echo $report['submittedBy'] ?? 'Unknown'; ?></td>
                                    <td>
                                        <a href="reports.php?id=<?php echo $reportID; ?>" class="btn btn-primary btn-sm">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No reports found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
