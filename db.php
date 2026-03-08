<?php
// db.php — MySQL connection (XAMPP)
$host     = 'localhost';
$dbname   = 'bmi.calculator';
$username = 'root';
$password = '';  // XAMPP default

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        'status'  => 'error',
        'message' => 'DB connection failed: ' . $conn->connect_error
    ]));
}
?>
