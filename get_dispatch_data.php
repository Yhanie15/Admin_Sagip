<?php
// get_dispatch_data.php

// Enable error reporting for debugging (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the content type to JSON
header('Content-Type: application/json');

// Start the session
session_start();
include('dbcon.php'); // sets up $database

if (!$database) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Fetch 'dispatches' data
try {
    $dispatchesRef = $database->getReference('dispatches');
    $dispatchesSnapshot = $dispatchesRef->getSnapshot();
    $dispatchesData = $dispatchesSnapshot->getValue();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching dispatches data: ' . $e->getMessage()]);
    exit;
}

// Initialize counts
$activeIncidents = 0;
$dispatchedFireTrucks = 0;
$resolvedIncidents = 0;

// Initialize array to store dispatched dispatches' coordinates
$dispatchedDispatches = [];

if ($dispatchesData) {
    foreach ($dispatchesData as $dispatchID => $dispatch) {
        if (isset($dispatch['status'])) {
            if ($dispatch['status'] === 'Dispatched') {
                $activeIncidents++;
                $dispatchedFireTrucks++; // Increment dispatched fire trucks count

                // Collect resident location
                if (isset($dispatch['latitude']) && isset($dispatch['longitude'])) {
                    $residentLatitude = $dispatch['latitude'];
                    $residentLongitude = $dispatch['longitude'];
                } else {
                    // Handle missing resident coordinates
                    error_log("Dispatch ID {$dispatchID} is missing resident coordinates.");
                    $residentLatitude = null;
                    $residentLongitude = null;
                }

                // Collect rescuer location from realTimeLocation
                if (isset($dispatch['realTimeLocation']['latitude']) && isset($dispatch['realTimeLocation']['longitude'])) {
                    $rescuerLatitude = $dispatch['realTimeLocation']['latitude'];
                    $rescuerLongitude = $dispatch['realTimeLocation']['longitude'];
                } else {
                    // Handle missing rescuer coordinates
                    error_log("Dispatch ID {$dispatchID} is missing rescuer coordinates.");
                    $rescuerLatitude = null;
                    $rescuerLongitude = null;
                }

                // Only add to dispatchedDispatches if both locations are available
                if ($residentLatitude !== null && $residentLongitude !== null && $rescuerLatitude !== null && $rescuerLongitude !== null) {
                    $dispatchedDispatches[] = [
                        'dispatchID' => $dispatchID,
                        'resident' => [
                            'latitude' => $residentLatitude,
                            'longitude' => $residentLongitude
                        ],
                        'rescuer' => [
                            'latitude' => $rescuerLatitude,
                            'longitude' => $rescuerLongitude
                        ]
                    ];
                }
            } elseif ($dispatch['status'] === 'Resolved') {
                $resolvedIncidents++;
            }
        } else {
            // Log or handle missing status
            error_log("Dispatch ID {$dispatchID} is missing status.");
        }
    }
} else {
    error_log("No dispatches data found.");
}

// Fetch 'reports_image' data
try {
    $reportsImageRef = $database->getReference('reports_image');
    $reportsImageSnapshot = $reportsImageRef->getSnapshot();
    $reportsImageData = $reportsImageSnapshot->getValue();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching reports_image data: ' . $e->getMessage()]);
    exit;
}

// Fetch 'Calls' data
try {
    $callsRef = $database->getReference('Calls');
    $callsSnapshot = $callsRef->getSnapshot();
    $callsData = $callsSnapshot->getValue();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching Calls data: ' . $e->getMessage()]);
    exit;
}

// Define 'today' date
$today = date('Y-m-d');

// Initialize total reports today
$totalReportsToday = 0;

// Process 'reports_image' data
if ($reportsImageData) {
    foreach ($reportsImageData as $reportID => $report) {
        if (isset($report['timestamp'])) {
            if (is_numeric($report['timestamp'])) {
                // If timestamp is UNIX timestamp in seconds
                $reportDate = date('Y-m-d', $report['timestamp']);
            } else {
                // Attempt to parse the date string
                $reportDate = date('Y-m-d', strtotime($report['timestamp']));
            }

            if ($reportDate === $today) {
                $totalReportsToday++;
            }
        } else {
            // Log or handle missing timestamp
            error_log("Report Image ID {$reportID} is missing timestamp.");
        }
    }
} else {
    error_log("No reports_image data found.");
}

// Process 'Calls' data
if ($callsData) {
    foreach ($callsData as $callID => $call) {
        if (isset($call['timestamp'])) {
            if (is_numeric($call['timestamp'])) {
                // If timestamp is UNIX timestamp in seconds
                $callDate = date('Y-m-d', $call['timestamp']);
            } else {
                // Attempt to parse the date string
                $callDate = date('Y-m-d', strtotime($call['timestamp']));
            }

            if ($callDate === $today) {
                $totalReportsToday++;
            }
        } else {
            // Log or handle missing timestamp
            error_log("Call ID {$callID} is missing timestamp.");
        }
    }
} else {
    error_log("No Calls data found.");
}

// Prepare the response data
$responseData = [
    'activeIncidents' => $activeIncidents,
    'dispatchedFireTrucks' => $dispatchedFireTrucks,
    'resolvedIncidents' => $resolvedIncidents,
    'totalReportsToday' => $totalReportsToday,
    'dispatches' => $dispatchedDispatches
];

// Output the JSON response
echo json_encode($responseData);
?>
