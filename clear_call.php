<?php
header('Content-Type: application/json');

if (file_exists('calls.json')) {
    unlink('calls.json');
}

echo json_encode(['success' => true]);
?>
