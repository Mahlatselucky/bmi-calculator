<?php
// db.php

$host     = 'localhost';
$dbname   = 'bmi_calculator';
$username = 'root';
$password = ''; // XAMPP default password is empty

// Create the connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if connection failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
