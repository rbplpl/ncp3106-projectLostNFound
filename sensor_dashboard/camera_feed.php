<?php
header('Content-Type: multipart/x-mixed-replace; boundary=frame');

$cameraStream = "http://your_camera_ip/video"; // Replace with your actual camera stream URL

while(true) {
    $frame = file_get_contents($cameraStream);
    echo "--frame\r\n";
    echo "Content-Type: image/jpeg\r\n\r\n";
    echo $frame;
    echo "\r\n";
    ob_flush();
    flush();
    usleep(100000); // Adjust for frame rate
}
?>
