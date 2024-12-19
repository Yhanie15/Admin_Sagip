<?php
// Include Firebase connection
include('dbcon.php');

// Get the data from the POST request
$rescuerID = $_POST['rescuerID']; // Rescuer ID
$reportKey = $_POST['reportKey']; // Fire report ID
$location = $_POST['location']; // Fire incident location
$fireStationName = $_POST['fireStationName']; // Fire station name

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
