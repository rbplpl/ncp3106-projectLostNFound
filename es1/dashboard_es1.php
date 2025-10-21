<?php
header('Content-Type: application/json');

// Dummy data simulation - Replace with real sensor readings from a database or hardware
$response = [
    'timestamp' => date('H:i:s'),
    'sound' => rand(30, 90), // dB
    'oxygen' => rand(18, 21) // %
];

echo json_encode($response);
?>
