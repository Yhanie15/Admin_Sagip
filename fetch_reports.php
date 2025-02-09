<?php
// fetch_reports.php

// Include the Firebase database connection
include('dbcon.php'); // Ensure this file sets up the $database variable

// Function to fetch reports and statistics
function fetchReports($database) {
    // Fetch image reports from Firebase
    $ref = 'reports_image';
    $reports = $database->getReference($ref)->getValue();
    
    // Sorting reports by timestamp in descending order
    if ($reports) {
        usort($reports, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
    }

    // Count statistics for the reports
    $totalReports = $reports ? count($reports) : 0;
    $resolvedReports = 0;
    $inProgressReports = 0;
    $pendingReports = 0;

    if ($reports) {
        foreach ($reports as $report) {
            $status = strtolower($report['status'] ?? 'pending');

            // Updated Counting Logic:
            // Count 'dispatching' and 'dispatched' as 'In Progress'
            if ($status === 'resolved') {
                $resolvedReports++;
            } elseif (in_array($status, ['in progress', 'dispatching', 'dispatched'])) {
                $inProgressReports++;
            } elseif ($status === 'pending') {
                $pendingReports++;
            }
        }
    }

    return [
        'totalReports' => $totalReports,
        'resolvedReports' => $resolvedReports,
        'inProgressReports' => $inProgressReports,
        'pendingReports' => $pendingReports,
        'reports' => $reports
    ];
}

// Fetch the latest reports and statistics
$data = fetchReports($database);
$totalReports = $data['totalReports'];
$resolvedReports = $data['resolvedReports'];
$inProgressReports = $data['inProgressReports'];
$pendingReports = $data['pendingReports'];
$reports = $data['reports'];

// Build stats HTML
ob_start();
?>
<div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-3">
    <div class="stat-box bg-danger">
        <strong>Pending Reports</strong>
        <div id="pendingReports"><?= htmlspecialchars($pendingReports) ?></div>
    </div>
    <div class="stat-box bg-warning">
        <strong>In Progress</strong>
        <div id="inProgressReports"><?= htmlspecialchars($inProgressReports) ?></div>
    </div>
    <div class="stat-box bg-success">
        <strong>Resolved Reports</strong>
        <div id="resolvedReports"><?= htmlspecialchars($resolvedReports) ?></div>
    </div>
    <div class="stat-box bg-primary">
        <strong>Total Reports Today</strong>
        <div id="totalReports"><?= htmlspecialchars($totalReports) ?></div>
    </div>
</div>

<?php
$statsHtml = ob_get_clean();

// Build table body HTML
ob_start();
?>
<?php if (!empty($reports)): ?>
    <?php foreach ($reports as $reportKey => $report): ?>
        <?php 
            // Fetch the reportId (if it exists) but DO NOT display it
            $reportId = $report['reportId'] ?? null; 
            
            // Prepare data for display
            $senderName = htmlspecialchars($report['senderName'] ?? 'Unknown');
            $imageUrl   = htmlspecialchars($report['imageUrl'] ?? 'https://via.placeholder.com/60');
            $location   = htmlspecialchars($report['location'] ?? 'No location provided');
            $timestamp  = htmlspecialchars(date('M. d, Y h:i A', strtotime($report['timestamp'] ?? 'now')));
            $statusRaw  = strtolower($report['status'] ?? 'pending');
            
            // Determine the display status
            $statusDisplay = ucfirst($statusRaw);
            
            // Assign badge class based on actual status
            $badgeClass = 'bg-secondary'; // Default
            if ($statusRaw === 'resolved') {
                $badgeClass = 'bg-success';
            } elseif (in_array($statusRaw, ['in progress', 'dispatching', 'dispatched'])) {
                $badgeClass = 'bg-warning';
            } elseif ($statusRaw === 'pending') {
                $badgeClass = 'bg-danger';
            }
        ?>
        <tr class="report-row" 
            data-report-key="<?= htmlspecialchars($reportKey) ?>" 
            data-report-id="<?= htmlspecialchars($reportId) ?>" 
            data-sender-name="<?= htmlspecialchars($senderName) ?>" 
            data-image-url="<?= htmlspecialchars($imageUrl) ?>" 
            data-location="<?= htmlspecialchars($location) ?>" 
            data-timestamp="<?= htmlspecialchars($timestamp) ?>" 
            data-status="<?= htmlspecialchars($statusDisplay) ?>">
            <td><?= $senderName ?></td>
            <td><img src="<?= $imageUrl ?>" alt="Report Image"></td>
            <td><?= $location ?></td>
            <td><?= $timestamp ?></td>
            <td><span class="badge <?= $badgeClass ?>"><?= $statusDisplay ?></span></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center">No image reports available.</td>
    </tr>
<?php endif; ?>
<?php
$tableBodyHtml = ob_get_clean();

// Return as JSON
header('Content-Type: application/json');
echo json_encode([
    'stats' => $statsHtml,
    'tableBody' => $tableBodyHtml
]);
?>
