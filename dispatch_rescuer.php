<?php
include('dbcon.php'); // sets up $database

// POST fields
$rescuerID       = isset($_POST['rescuerID']) ? trim($_POST['rescuerID']) : '';
$reportKey       = isset($_POST['reportKey']) ? trim($_POST['reportKey']) : '';
$location        = isset($_POST['location']) ? trim($_POST['location']) : '';
$fireStationName = isset($_POST['fireStationName']) ? trim($_POST['fireStationName']) : '';
$reportVia       = isset($_POST['reportVia']) ? trim($_POST['reportVia']) : ''; // New field for report source
$latitude        = isset($_POST['latitude']) ? trim($_POST['latitude']) : null; // Retrieve latitude
$longitude       = isset($_POST['longitude']) ? trim($_POST['longitude']) : null; // Retrieve longitude

// If missing fields, bail out
if (empty($rescuerID) || empty($reportKey) || empty($location) || empty($fireStationName) || empty($reportVia)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Validate reportVia
$validReportVia = ['Call', 'Image'];
if (!in_array($reportVia, $validReportVia)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reportVia value.']);
    exit;
}

// Define the status we want to set in both places
$dispatchStatus = 'Dispatching';

// Set dispatch time to Philippine Standard Time (PST)
$dispatchTime = new DateTime('now', new DateTimeZone('Asia/Manila'));  // Current time in Asia/Manila timezone
$dispatchTime = $dispatchTime->format('Y-m-d H:i:s');  // Format the date as "YYYY-MM-DD HH:MM:SS"

// 1) Generate a new key for "dispatches"
$dispatchKey = $database->getReference('dispatches')->push()->getKey();

// 2) Generate a unique dispatchID
$dispatchID = uniqid('dispatch_', true);

// 3) Determine the report path based on reportVia
if ($reportVia === 'Image') {
    $reportPath = "reports_image/$reportKey";
} elseif ($reportVia === 'Call') {
    $reportPath = "Calls/$reportKey";
}

// Check if the report exists
$reportRef = $database->getReference($reportPath);
$report = $reportRef->getValue();

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found.']);
    exit;
}

// 4) Build the combined data you want to write
$updates = [
    // Write dispatch record under "dispatches/$dispatchKey"
    "dispatches/$dispatchKey" => [
        'dispatchID'      => $dispatchID,
        'rescuerID'       => $rescuerID,
        'reportKey'       => $reportKey,
        'location'        => $location,
        'latitude'        => $latitude,  // Include latitude in the data
        'longitude'       => $longitude, // Include longitude in the data
        'fireStationName' => $fireStationName,
        'dispatchTime'    => $dispatchTime,
        'status'          => $dispatchStatus,
        'reportVia'       => $reportVia  // Add the "Report Via" field here
    ],
    // Write the same status and dispatchID into the correct report node
    "$reportPath/status"       => $dispatchStatus,
    "$reportPath/dispatchID"   => $dispatchKey, // Link to dispatchKey
];

// 5) Perform a multi-path update
try {
    $database->getReference()->update($updates);

    // Also update the rescuer's node if needed
    $rescuersRef = $database->getReference("rescuer/$rescuerID");
    $rescuer = $rescuersRef->getValue();

    if ($rescuer) {
        $rescuerUpdates = [
            'status'           => $dispatchStatus,
            'dispatchLocation' => $location
        ];

        // Optionally, keep track of dispatch history if needed
        if (isset($rescuer['dispatchHistory']) && is_array($rescuer['dispatchHistory'])) {
            $rescuerUpdates['dispatchHistory'][] = $dispatchKey;
        } else {
            $rescuerUpdates['dispatchHistory'] = [$dispatchKey];
        }

        $database->getReference("rescuer/$rescuerID")->update($rescuerUpdates);
    }

    echo json_encode(['success' => true, 'message' => 'Dispatch sent successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error writing data: ' . $e->getMessage()]);
}
?>
