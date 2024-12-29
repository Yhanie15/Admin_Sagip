<?php
// 1) Set the default time zone. Adjust as needed, e.g. 'Asia/Manila'.
date_default_timezone_set('Asia/Manila');

// 2) Include Firebase and other required libraries
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

// 3) Create the Firebase factory
$factory = (new Factory)
    ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
    ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();

// 4) Fetch the "Calls" reference from Firebase
$calls = $database->getReference('Calls')->getValue();

// Initialize counters/arrays
$totalCalls = 0;
$missedCalls = 0;
$answeredCalls = 0;
$ongoingCalls = 0;
$incomingCalls = [];
$callLog = [];

// This is "today" in your local time zone
$today = new DateTime('now', new DateTimeZone('Asia/Manila'));
$todayString = $today->format('Y-m-d');

// Uncomment this line if you want to see in your server logs what "today" is
// error_log("Today (PHP side): " . $todayString);

if ($calls) {
    foreach ($calls as $key => $call) {
        // Default null or empty if 'time' not set
        if (!isset($call['time'])) {
            $callLog[] = $call;
            continue;
        }

        // 5) Parse the call time in the same time zone or convert from UTC
        //    If your stored time is UTC, do: new DateTimeZone('UTC') then convert.
        //    If your stored time is already in local time, you can do 'Asia/Manila' directly.

        // Example if stored as UTC string: "2024-12-31T00:15:00Z"
        // $callTime = new DateTime($call['time'], new DateTimeZone('UTC'));
        // $callTime->setTimezone(new DateTimeZone('Asia/Manila'));

        // If stored in a format that is already Asia/Manila (or local):
        $callTime = new DateTime($call['time'], new DateTimeZone('Asia/Manila'));
        $callDateString = $callTime->format('Y-m-d');

        // Uncomment to debug in logs
        // error_log("Call key=$key => raw time: " . $call['time'] . " | local date: $callDateString");

        // 6) Compare the call's date to "today" (local) to see if it's from the current calendar day
        if ($callDateString === $todayString) {
            $totalCalls++;

            if (isset($call['status'])) {
                if ($call['status'] === 'Missed Call') {
                    $missedCalls++;
                } elseif ($call['status'] === 'Answered') {
                    $answeredCalls++;
                } elseif ($call['status'] === 'Ongoing') {
                    $ongoingCalls++;
                    // This is where we add "Ongoing" calls to incoming
                    $incomingCalls[$key] = $call;
                }
            }
        }

        // We store all calls in callLog (not just today's),
        // so you can filter in the front-end if needed.
        $callLog[] = $call;
    }
}

// 7) Return JSON response for the front end
header('Content-Type: application/json');
echo json_encode([
    'incoming' => $incomingCalls,
    'callLog' => $callLog,
    'stats' => [
        'totalCalls' => $totalCalls,
        'missedCalls' => $missedCalls,
        'answeredCalls' => $answeredCalls,
        'ongoingCalls' => $ongoingCalls,
    ],
]);
