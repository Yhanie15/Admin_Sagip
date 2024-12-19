<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input['channel'])) {
    file_put_contents('calls.json', json_encode([
        'incoming' => true,
        'channel' => $input['channel'],
        'timestamp' => time()
    ]));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
