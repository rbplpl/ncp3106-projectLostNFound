<?php
require 'db.php';

$sound = rand(30, 100);
$lat = 37.7749 + rand(-10, 10)/1000;
$lng = -122.4194 + rand(-10, 10)/1000;
$oxygen = rand(90, 100);

$stmt = $pdo->prepare("INSERT INTO sensor_data (sound_level, latitude, longitude, oxygen_level) VALUES (?, ?, ?, ?)");
$stmt->execute([$sound, $lat, $lng, $oxygen]);

echo "Random sensor data inserted.";
