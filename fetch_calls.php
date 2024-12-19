<?php 
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
    ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();

// Fetch incoming calls
$calls = $database->getReference('Calls')->getValue();

// Separate incoming calls and call logs
$incomingCalls = [];
$callLog = [];

if ($calls) {
    foreach ($calls as $key => $call) {
        if ($call['status'] === 'Ongoing') {
            $incomingCalls[$key] = $call;
        }
        $callLog[] = $call;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'incoming' => $incomingCalls,
    'callLog' => $callLog
]);
