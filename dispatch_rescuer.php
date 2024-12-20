<?php
// Include Firebase connection
include('dbcon.php');

// Get the data from the POST request
$rescuerID = isset($_POST['rescuerID']) ? $_POST['rescuerID'] : '';
$reportKey = isset($_POST['reportKey']) ? $_POST['reportKey'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';
$fireStationName = isset($_POST['fireStationName']) ? $_POST['fireStationName'] : '';

// Ensure that required fields are present
if (empty($rescuerID) || empty($reportKey) || empty($location) || empty($fireStationName)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

// Get current time (dispatch time)
$dispatchTime = date('Y-m-d H:i:s');

// Create a dispatch record in Firebase under "dispatches"
$dispatchRef = $database->getReference('dispatches')->push([
    'rescuerID' => $rescuerID,
    'reportKey' => $reportKey,
    'location' => $location,
    'fireStationName' => $fireStationName,
    'dispatchTime' => $dispatchTime,
    'status' => 'dispatching' // Initial status is "dispatching"
]);

// Send a notification by updating the status field in Firebase (simulate notification)
$rescuersRef = $database->getReference('rescuer/' . $rescuerID);
$rescuersRef->update([
    'status' => 'dispatching', // Trigger the notification
    'dispatchLocation' => $location, // Include the location of the fire
]);

// Return a response back to admin
echo json_encode(['success' => true, 'message' => 'Dispatch sent successfully']);
?>
