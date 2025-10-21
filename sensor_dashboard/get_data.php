<?php
// Database connection details
$servername = "localhost";  // Database host (usually localhost)
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "sensor_data";    // Your database name

// Create connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if POST data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data sent via POST
    $sound = $_POST['sound'];
    $oxygen = $_POST['oxygen'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $timestamp = date("Y-m-d H:i:s");  // Current timestamp

    // Prepare the SQL query to insert the data into the database
    $sql = "INSERT INTO sensor_readings (sound, oxygen, latitude, longitude, timestamp)
            VALUES ('$sound', '$oxygen', '$latitude', '$longitude', '$timestamp')";

    // Execute the query and check if it was successful
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";  // Optional response
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;  // Error if query fails
    }
} else {
    echo "No data received!";
}

// Close the connection
$conn->close();
?>
