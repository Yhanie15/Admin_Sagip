<?php
// graph.php

// ------------------------------
// 1. LOAD / PREPARE YOUR DATA
// ------------------------------
if (!isset($reportData)) {
  // Replace this with your actual data retrieval logic.
  // Sample data for demonstration:
  $reportData = [
    [
      'dateTime'          => '2023-01-15 10:00:00',
      'civilianInjured'   => 2,
      'civilianDeath'     => 0,
      'firefighterInjured'=> 1,
      'firefighterDeath'  => 0,
      'fireClassification'=> 'Class A',
      'motive'            => 'Accidental'
    ],
    [
      'dateTime'          => '2023-01-20 15:00:00',
      'civilianInjured'   => 0,
      'civilianDeath'     => 1,
      'firefighterInjured'=> 0,
      'firefighterDeath'  => 0,
      'fireClassification'=> 'Class B',
      'motive'            => 'Arson'
    ]
    // Add more entries as needed.
  ];
}

// ------------------------------
// 2. GET DATE RANGE FROM USER
// ------------------------------
$startDate = new DateTime($_GET['start_date'] ?? '2023-01-01');
$endDate   = new DateTime($_GET['end_date'] ?? date('Y-m-d'));

// ------------------------------
// 3. FILTER DATA BY DATE RANGE
// ------------------------------
$filteredData = array_filter($reportData, function($r) use ($startDate, $endDate) {
    if (empty($r['dateTime'])) return false;
    $d = new DateTime($r['dateTime']);
    return $d >= $startDate && $d <= $endDate;
});
$totalIncidents = count($filteredData);

// ------------------------------
// 4. DECIDE AGGREGATION TYPE
// ------------------------------
$rangeDays = $startDate->diff($endDate)->days + 1;
if ($rangeDays < 31) {
    $aggregationType = 'weekly';
    $startWeek = clone $startDate;
    if ($startWeek->format('N') != 1) {
        $startWeek->modify('last monday');
    }
    $endWeek = clone $endDate;
    if ($endWeek->format('N') != 1) {
        $endWeek->modify('next monday');
    }
    $period = new DatePeriod($startWeek, new DateInterval('P1W'), $endWeek);
    $labels = [];
    $incidentData = [];
    $casualtyData = [];
    foreach ($period as $dt) {
        $weekKey = $dt->format('o-\WW');
        $labels[] = $weekKey;
        $incidentData[$weekKey] = 0;
        $casualtyData[$weekKey] = [
            'Resident Injured'    => 0,
            'Resident Deaths'     => 0,
            'Firefighter Injured' => 0,
            'Firefighter Deaths'  => 0,
        ];
    }
    foreach ($filteredData as $r) {
        if (empty($r['dateTime'])) continue;
        $d = new DateTime($r['dateTime']);
        $weekKey = $d->format('o-\WW');
        if (isset($incidentData[$weekKey])) {
            $incidentData[$weekKey]++;
            $casualtyData[$weekKey]['Resident Injured']    += (int)($r['civilianInjured']   ?? 0);
            $casualtyData[$weekKey]['Resident Deaths']     += (int)($r['civilianDeath']     ?? 0);
            $casualtyData[$weekKey]['Firefighter Injured'] += (int)($r['firefighterInjured']?? 0);
            $casualtyData[$weekKey]['Firefighter Deaths']  += (int)($r['firefighterDeath']  ?? 0);
        }
    }
} else {
    $aggregationType = 'monthly';
    $period = new DatePeriod($startDate, new DateInterval('P1M'), (clone $endDate)->modify('+1 month'));
    $labels = [];
    $incidentData = [];
    $casualtyData = [];
    foreach ($period as $dt) {
        $monthKey = $dt->format('Y-m');
        $labels[] = $monthKey;
        $incidentData[$monthKey] = 0;
        $casualtyData[$monthKey] = [
            'Resident Injured'    => 0,
            'Resident Deaths'     => 0,
            'Firefighter Injured' => 0,
            'Firefighter Deaths'  => 0,
        ];
    }
    foreach ($filteredData as $r) {
        if (empty($r['dateTime'])) continue;
        $d = new DateTime($r['dateTime']);
        $monthKey = $d->format('Y-m');
        if (isset($incidentData[$monthKey])) {
            $incidentData[$monthKey]++;
            $casualtyData[$monthKey]['Resident Injured']    += (int)($r['civilianInjured']   ?? 0);
            $casualtyData[$monthKey]['Resident Deaths']     += (int)($r['civilianDeath']     ?? 0);
            $casualtyData[$monthKey]['Firefighter Injured'] += (int)($r['firefighterInjured']?? 0);
            $casualtyData[$monthKey]['Firefighter Deaths']  += (int)($r['firefighterDeath']  ?? 0);
        }
    }
}

// ------------------------------
// 5. CLASSIFICATION & MOTIVE
// ------------------------------
$classificationData = [];
$motiveData = [];
foreach ($filteredData as $r) {
    if (empty($r['dateTime'])) continue;
    if (!empty($r['fireClassification'])) {
        $class = $r['fireClassification'];
        $classificationData[$class] = ($classificationData[$class] ?? 0) + 1;
    }
    if (!empty($r['motive'])) {
        $mot = $r['motive'];
        $motiveData[$mot] = ($motiveData[$mot] ?? 0) + 1;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fire Incidents Dashboard</title>
  <link rel="stylesheet" href="graph.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <!-- Include Chart.js, Chartjs-plugin-datalabels, html2canvas, and jsPDF -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <script>
    Chart.register(ChartDataLabels);
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.3.2/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
  <!-- Top Navbar -->
  <nav class="navbar navbar-light bg-light">
    <div class="container-fluid">
      <span class="navbar-brand h1">Fire Incidents Dashboard</span>
    </div>
  </nav>
  <div class="container-fluid mt-2">
    <div class="row">
      <!-- Side Panel -->
      <div class="col-md-2 bg-panel p-3">
        <h5>Date Range Selector</h5>
        <form id="dateRangeForm" method="GET">
          <div class="mb-3">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" id="startDate" name="start_date" class="form-control" value="<?php echo $startDate->format('Y-m-d'); ?>">
          </div>
          <div class="mb-3">
            <label for="endDate" class="form-label">End Date</label>
            <input type="date" id="endDate" name="end_date" class="form-control" value="<?php echo $endDate->format('Y-m-d'); ?>">
          </div>
          <button type="submit" class="btn btn-primary">Apply</button>
          <!-- Button to view full report -->
          <button type="button" id="viewReportBtn" class="btn btn-secondary mt-2">View Full Report</button>
        </form>
        <!-- Incident Counter -->
        <div class="text-center mt-4">
          <h5>Incident Counter</h5>
          <h1 class="display-4" id="incidentCounter"><?php echo $totalIncidents; ?></h1>
        </div>
      </div>
      <!-- Main Content: Tabbed Charts -->
      <div class="col-md-10">
        <div class="card mt-2">
          <div class="card-header">Charts</div>
          <div class="card-body">
            <!-- NAV TABS -->
            <ul class="nav nav-tabs" id="graphTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="incident-tab" data-bs-toggle="tab" data-bs-target="#incident-pane" type="button" role="tab" aria-controls="incident-pane" aria-selected="true">
                  Fire Incident Graph
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="casualty-tab" data-bs-toggle="tab" data-bs-target="#casualty-pane" type="button" role="tab" aria-controls="casualty-pane" aria-selected="false">
                  Casualty Graph
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="classification-tab" data-bs-toggle="tab" data-bs-target="#classification-pane" type="button" role="tab" aria-controls="classification-pane" aria-selected="false">
                  Classification
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="motive-tab" data-bs-toggle="tab" data-bs-target="#motive-pane" type="button" role="tab" aria-controls="motive-pane" aria-selected="false">
                  Motive
                </button>
              </li>
            </ul>
            <!-- TAB CONTENT -->
            <div class="tab-content" id="graphTabsContent">
              <!-- Fire Incident Pane -->
              <div class="tab-pane fade show active p-3" id="incident-pane" role="tabpanel" aria-labelledby="incident-tab">
                <div class="chart-container">
                  <canvas id="fireIncidentChart"></canvas>
                </div>
                <div class="text-center" id="fireIncidentDescription">
                  Fire Incidents from <?php echo $startDate->format('Y-m-d'); ?> to <?php echo $endDate->format('Y-m-d'); ?>
                  (<?php echo ucfirst($aggregationType); ?>)
                </div>
              </div>
              <!-- Casualty Pane -->
              <div class="tab-pane fade p-3" id="casualty-pane" role="tabpanel" aria-labelledby="casualty-tab">
                <div class="chart-container">
                  <canvas id="casualtyChart"></canvas>
                </div>
                <div class="text-center" id="casualtyDescription">
                  Casualties from <?php echo $startDate->format('Y-m-d'); ?> to <?php echo $endDate->format('Y-m-d'); ?>
                  (<?php echo ucfirst($aggregationType); ?>)
                </div>
              </div>
              <!-- Classification Pane -->
              <div class="tab-pane fade p-3" id="classification-pane" role="tabpanel" aria-labelledby="classification-tab">
                <div class="chart-container">
                  <canvas id="classificationChart"></canvas>
                </div>
                <div class="text-center">Classification Chart</div>
              </div>
              <!-- Motive Pane -->
              <div class="tab-pane fade p-3" id="motive-pane" role="tabpanel" aria-labelledby="motive-tab">
                <div class="chart-container">
                  <canvas id="motiveChart"></canvas>
                </div>
                <div class="text-center">Motive Chart</div>
              </div>
            </div> <!-- end tab-content -->
          </div> <!-- card-body -->
        </div> <!-- card -->
      </div> <!-- col-md-10 -->
    </div> <!-- row -->
  </div> <!-- container -->

  <!-- Modal for Full Report -->
  <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="reportModalLabel">Full Dashboard Report</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="reportContent">
          <!-- The full report HTML is injected here by graph.js -->
        </div>
        <div class="modal-footer">
          <button type="button" id="downloadPDFBtn" class="btn btn-primary">Download as PDF</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Report</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pass PHP Data to JavaScript -->
  <script>
    const aggregationType      = '<?php echo $aggregationType; ?>';
    const labels               = <?php echo json_encode($labels); ?>;
    const incidentData         = <?php echo json_encode($incidentData); ?>;
    const casualtyData         = <?php echo json_encode($casualtyData); ?>;
    const classificationData   = <?php echo json_encode($classificationData); ?>;
    const motiveData           = <?php echo json_encode($motiveData); ?>;
    const startDate            = '<?php echo $startDate->format("Y-m-d"); ?>';
    const endDate              = '<?php echo $endDate->format("Y-m-d"); ?>';
  </script>
  <!-- Main JS Logic -->
  <script src="graph.js"></script>

  <!-- Bootstrap JS (for tabs and modal) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
