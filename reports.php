<?php 
// Start the session
session_start();
include('dbcon.php'); // Include the database connection

// Fetch all reports from the 'report' table in Firebase
$reportRef = $database->getReference('report');
$reportSnapshot = $reportRef->getSnapshot();
$reportData = $reportSnapshot->getValue();

// Check if a specific report ID is provided (for viewing a single report)
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
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        #content {
            margin-left: 250px; /* Matches sidebar width */
            padding: 20px;
        }

        .table-container {
            margin-top: 20px;
        }

        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Include Topbar -->
    <div id="content">
        <?php include 'topbar.php'; ?>

        <!-- Main Content -->
        <?php if ($viewingReport && $reportDetails): ?>
            <!-- Display Report Details -->
            <h1 class="text-center my-4">Report Details</h1>
            <div class="card">
                <div class="card-header">
                    <strong>Fire Station:</strong> <?php echo isset($reportDetails['fireStation']) ? $reportDetails['fireStation'] : 'Unknown'; ?>
                </div>
                <div class="card-body">
                    <?php foreach ($reportDetails as $key => $value): ?>
                        <p><strong><?php echo ucfirst($key); ?>:</strong> <?php echo !empty($value) ? $value : 'N/A'; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="reports.php" class="btn btn-secondary mt-3">Back to Reports</a>
        <?php else: ?>
            <!-- Reports Table -->
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
                            <?php 
                            $counter = 1;
                            foreach ($reportData as $reportID => $report): 
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo isset($report['fireStation']) ? $report['fireStation'] : 'Unknown Fire Station'; ?></td>
                                    <td><?php echo isset($report['status']) ? ucfirst($report['status']) : 'Unknown'; ?></td>
                                    <td><?php echo isset($report['submittedBy']) ? $report['submittedBy'] : 'Unknown'; ?></td>
                                    <td>
                                        <a href="reports.php?id=<?php echo $reportID; ?>" class="btn btn-primary btn-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
