<?php
// images.php
session_start();
// Include the Firebase database connection
include('dbcon.php'); // Ensure this file sets up the $database variable

// Function to fetch reports and statistics (used for initial load)
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

// Fetch initial data
$data = fetchReports($database);
$totalReports = $data['totalReports'];
$resolvedReports = $data['resolvedReports'];
$inProgressReports = $data['inProgressReports'];
$pendingReports = $data['pendingReports'];
$reports = $data['reports'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Images Report</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <link
    href="https://fonts.googleapis.com/icon?family=Material+Icons"
    rel="stylesheet"
  />

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

    #topbar {
      position: fixed;
      top: 0;
      left: 250px; /* if your sidebar is 250px wide */
      width: calc(100% - 250px);
      z-index: 1030; /* keep it above everything else */
      background-color: #fff; /* so it's not transparent */
    }

    #content {
      margin-left: 250px; /* same as sidebar width */
      width: calc(100% - 250px);
      /* pick a margin-top that matches (or slightly exceeds) the topbar’s height */
      margin-top: 69px; /* or 120px, etc. */
      min-height: 100vh;
      overflow-y: auto;
    }
    #reportSection{
        padding-left: 20px;
        padding-right: 20px;
    }

    /* Responsive Sidebar */
    @media (max-width: 768px) {
      #sidebar {
        width: 200px;
      }
      #topbar {
        left: 200px;
        width: calc(100% - 200px);
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
      #topbar {
        left: 150px;
        width: calc(100% - 150px);
      }
      #content {
        margin-left: 150px;
        width: calc(100% - 150px);
      }
    }

    @media (max-width: 400px) {
      #sidebar {
        display: none; 
      }
      #topbar {
        left: 0;
        width: 100%;
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
    .bg-danger  { background-color: #dc3545; color: white; }
    .bg-secondary { background-color: #6c757d; color: white; }

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
      max-width: 50%;
    }
    .modal-content {
      overflow-y: auto;
    }
    .modal-body img {
      max-width: 100%;
      max-height: 300px;
      object-fit: contain;
      border-radius: 8px;
      margin-bottom: 15px;
    }

    /* Scrollable Table Container */
    .scrollable-table {
      max-height: 500px; 
      overflow-y: auto;
    }
    /* Hide scrollbar for Webkit browsers */
    .scrollable-table::-webkit-scrollbar {
      display: none;
    }
    /* Hide scrollbar for Firefox */
    .scrollable-table {
      scrollbar-width: none;
    }

    /* Optional: Sticky table headers */
    thead.sticky-top th {
      position: sticky;
      top: 0;
      background-color: #f8f9fa;
      z-index: 1;
    }

    /* Cursor pointer for report rows */
    .report-row {
      cursor: pointer;
    }

    .header {
      display: flex;
      justify-content: space-between; 
      align-items: center;
      padding: 10px;
    }
    .title {
      margin: 0;
      flex-grow: 1;
    }
    .status-dropdown {
      width: 150px;
      padding: 5px;
      margin: 0 10px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div id="sidebar">
    <?php include 'sidebar.php'; ?>
  </div>

  <!-- Fixed Topbar (with original styling from topbar.php) -->
  <div id="topbar">
    <?php include 'topbar.php'; ?>
  </div>

  <!-- Main Content -->
  <div id="content" class="d-flex flex-column">
      
    <!-- Report Section (Statistics and Table) -->
    <div id="reportSection">
      <!-- Stat Boxes -->
      <div
        class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-3"
        id="statsSection"
      >
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

      <!-- Page Title + Filter Dropdown -->
      <div class="header">
        <h4 class="title">Image Reports</h4>
        <select id="statusFilter" class="status-dropdown">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="dispatching">Dispatching</option>
          <option value="dispatched">Dispatched</option>
          <option value="resolved">Resolved</option>
        </select>
      </div>

      <!-- Scrollable Table Container with Hidden Scrollbar -->
      <div class="container-fluid mt-4 scrollable-table">
        <table class="table table-bordered table-striped">
          <thead class="table-light sticky-top">
            <tr>
              <th>Name of Reporter</th>
              <th>Image</th>
              <th>Location</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="reportsTableBody">
            <?php if (!empty($reports)): ?>
              <?php foreach ($reports as $reportKey => $report): ?>
                <?php
                  $senderName = htmlspecialchars($report['senderName'] ?? 'Unknown');
                  $imageUrl   = htmlspecialchars($report['imageUrl'] ?? 'https://via.placeholder.com/60');
                  $location   = htmlspecialchars($report['location'] ?? 'No location provided');
                  $timestamp  = htmlspecialchars(date('M. d, Y h:i A', strtotime($report['timestamp'] ?? 'now')));
                  $statusRaw  = strtolower($report['status'] ?? 'pending');

                  if ($statusRaw === 'dispatching') {
                      $statusDisplay = 'Dispatching';
                      $badgeClass = 'bg-warning';
                  } elseif ($statusRaw === 'dispatched') {
                      $statusDisplay = 'Dispatched';
                      $badgeClass = 'bg-warning';
                  } elseif ($statusRaw === 'in progress') {
                      $statusDisplay = 'In Progress';
                      $badgeClass = 'bg-warning';
                  } elseif ($statusRaw === 'resolved') {
                      $statusDisplay = 'Resolved';
                      $badgeClass = 'bg-success';
                  } elseif ($statusRaw === 'pending') {
                      $statusDisplay = 'Pending';
                      $badgeClass = 'bg-danger';
                  } else {
                      $statusDisplay = ucfirst($statusRaw);
                      $badgeClass = 'bg-secondary';
                  }
                ?>

                <tr class="report-row"
                  data-report-key="<?= htmlspecialchars($reportKey) ?>"
                  data-report-id="<?= htmlspecialchars($report['reportId'] ?? '') ?>"
                  data-sender-name="<?= $senderName ?>"
                  data-image-url="<?= $imageUrl ?>"
                  data-location="<?= $location ?>"
                  data-timestamp="<?= $timestamp ?>"
                  data-status="<?= htmlspecialchars($statusDisplay) ?>"
                >
                  <td><?= $senderName ?></td>
                  <td><img src="<?= $imageUrl ?>" alt="Report Image"></td>
                  <td><?= $location ?></td>
                  <td><?= $timestamp ?></td>
                  <td>
                    <span class="badge <?= $badgeClass ?>">
                      <?= $statusDisplay ?>
                    </span>
                  </td>
                </tr>
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
  </div>

  <!-- Report Details Modal -->
  <div
    class="modal fade"
    id="reportDetailsModal"
    tabindex="-1"
    aria-labelledby="reportDetailsModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reportDetailsModalLabel">Report Details</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <img id="modalImage" src="" alt="Large Report Image" />
          <p>
            <strong>Location:</strong>
            <span id="modalLocation"></span>
          </p>
        </div>
        <div class="modal-footer">
          <a href="#" id="acceptReportButton" class="btn btn-success">
            Accept Report
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and Dependencies -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
  ></script>

  <!-- Custom Script for Filtering Table Rows Based on Status -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const statusFilter = document.getElementById('statusFilter');
      statusFilter.addEventListener('change', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#reportsTableBody tr');

        rows.forEach((row) => {
          const status = row.getAttribute('data-status').toLowerCase();
          if (filter === "" || status === filter) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    });
  </script>

  <script>
    // Apply filter for table
    function applyFilter(filterValue) {
      const filter = filterValue.toLowerCase();
      const rows = document.querySelectorAll('#reportsTableBody tr');
      rows.forEach(row => {
        const status = row.getAttribute('data-status').toLowerCase();
        if (filter === "" || status === filter) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Function to fetch and update the report section (stats + table)
    function fetchReportData() {
      // 1) Get current filter
      const currentFilterValue = document.getElementById('statusFilter').value;

      fetch('fetch_reports.php')
        .then(response => response.json())
        .then(data => {
          // Update stats
          document.getElementById('statsSection').innerHTML = data.stats;

          // Preserve scroll position
          const scrollableTable = document.querySelector('.scrollable-table');
          const scrollTop = scrollableTable.scrollTop;

          // Update table body
          document.getElementById('reportsTableBody').innerHTML = data.tableBody;

          // Restore scroll position
          scrollableTable.scrollTop = scrollTop;

          // Re-apply filter
          applyFilter(currentFilterValue);
        })
        .catch(error => console.error('Error fetching report data:', error));
    }

    document.addEventListener('DOMContentLoaded', function() {
      // 1. Filter init
      const statusFilter = document.getElementById('statusFilter');
      statusFilter.addEventListener('change', function() {
        applyFilter(this.value);
      });

      // 2. Auto refresh (e.g. every 5 seconds)
      setInterval(fetchReportData, 5000);

      // 3. Modal click logic
      document.addEventListener('click', function(event) {
        let target = event.target;
        while (target && target.nodeName !== 'TR') {
          target = target.parentElement;
        }
        if (target && target.classList.contains('report-row')) {
          const reportKey   = target.getAttribute('data-report-key');
          const reportId    = target.getAttribute('data-report-id');
          const senderName  = target.getAttribute('data-sender-name');
          const imageUrl    = target.getAttribute('data-image-url');
          const location    = target.getAttribute('data-location');
          const timestamp   = target.getAttribute('data-timestamp');
          const status      = target.getAttribute('data-status');

          // Modal content
          document.querySelector('#reportDetailsModal .modal-title').innerText =
            `Report Details - ${senderName}`;
          document.getElementById('modalImage').src = imageUrl;
          document.getElementById('modalLocation').innerText = location;

          // Accept button logic
          const acceptButton = document.getElementById('acceptReportButton');
          if (status.toLowerCase() === 'resolved') {
            acceptButton.style.display = 'none';
          } else {
            acceptButton.style.display = 'inline-block';
            acceptButton.href = 
              `dispatch_firestation.php?reportKey=${encodeURIComponent(reportKey)}&reportId=${encodeURIComponent(reportId)}`;
          }

          // Show modal
          const reportDetailsModal = new bootstrap.Modal(
            document.getElementById('reportDetailsModal')
          );
          reportDetailsModal.show();
        }
      });
    });
  </script>
</body>
</html>
