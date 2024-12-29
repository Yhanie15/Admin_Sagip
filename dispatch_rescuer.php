<?php
include('dbcon.php'); // sets up $database

// POST fields
$rescuerID       = $_POST['rescuerID']       ?? '';
$reportKey       = $_POST['reportKey']       ?? '';
$location        = $_POST['location']        ?? '';
$fireStationName = $_POST['fireStationName'] ?? '';

// If missing fields, bail out
if (empty($rescuerID) || empty($reportKey) || empty($location) || empty($fireStationName)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Let's define the status we want to set in both places
$dispatchStatus = 'Dispatching';
$dispatchTime   = date('Y-m-d H:i:s');

// 1) Generate a new key for "dispatches"
$dispatchKey = $database->getReference('dispatches')->push()->getKey();

// 2) Build the combined data you want to write
$updates = [
    // Write dispatch record under "dispatches/$dispatchKey"
    "dispatches/$dispatchKey" => [
        'rescuerID'       => $rescuerID,
        'reportKey'       => $reportKey,
        'location'        => $location,
        'fireStationName' => $fireStationName,
        'dispatchTime'    => $dispatchTime,
        'status'          => $dispatchStatus
    ],
    // Write the same status into "reports_image/$reportKey"
    "reports_image/$reportKey/status" => $dispatchStatus,
];

// 3) Perform a multi-path update
try {
    $database->getReference()->update($updates);

    // Also update the rescuer's node if needed
    $database->getReference("rescuer/$rescuerID")->update([
        'status'           => $dispatchStatus,
        'dispatchLocation' => $location
    ]);

    echo json_encode(['success' => true, 'message' => 'Dispatch sent successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error writing data: ' . $e->getMessage()]);
}
