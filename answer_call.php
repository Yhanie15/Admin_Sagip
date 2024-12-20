<?php
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

header('Content-Type: application/json'); // Ensure the response is JSON

try {
    // Initialize Firebase
    $factory = (new Factory)
        ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
        ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');
    $database = $factory->createDatabase();

    // Get the POST body as JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['callId'])) {
        // Missing callId in the request
        http_response_code(400);
        echo json_encode(['error' => 'Missing callId']);
        exit;
    }

    $callId = $data['callId'];

    // Update Firebase database with the new status
    $database->getReference("Calls/{$callId}")->update([
        'status' => 'Answered'
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Return error message if something goes wrong
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
    exit;
}
