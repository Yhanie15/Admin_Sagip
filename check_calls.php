<?php
header('Content-Type: application/json');

$file = 'calls.json';

if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);

    if ($data['incoming']) {
        echo json_encode(['incoming' => true, 'channel' => $data['channel']]);
        file_put_contents($file, json_encode(['incoming' => false])); // Reset state
        exit;
    }
}

echo json_encode(['incoming' => false]);
?>
