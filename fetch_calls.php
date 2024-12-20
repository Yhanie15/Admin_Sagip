<?php 
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
    ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();
$calls = $database->getReference('Calls')->getValue();

$totalCalls = 0;
$missedCalls = 0;
$answeredCalls = 0;
$ongoingCalls = 0;
$incomingCalls = [];
$callLog = [];

$today = (new DateTime())->format('Y-m-d');

if ($calls) {
    foreach ($calls as $key => $call) {
        $callDate = isset($call['time']) ? (new DateTime($call['time']))->format('Y-m-d') : null;
        if ($callDate === $today) {
            $totalCalls++;
            if ($call['status'] === 'Missed Call') {
                $missedCalls++;
            } elseif ($call['status'] === 'Answered') {
                $answeredCalls++;
            } elseif ($call['status'] === 'Ongoing') {
                $ongoingCalls++;
                $incomingCalls[$key] = $call;
            }
        }
        $callLog[] = $call;
    }
}

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
