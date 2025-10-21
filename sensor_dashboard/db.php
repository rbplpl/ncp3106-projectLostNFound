<?php
$host = 'localhost';
$db = 'sensor_data_db';
$user = 'root';        // Default XAMPP username
$pass = '';            // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
