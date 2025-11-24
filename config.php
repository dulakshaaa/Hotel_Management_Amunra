<?php
session_start();
// Database configuration
$host = "localhost";      // or 127.0.0.1
$username = "root";       // default XAMPP username
$password = "";           // default XAMPP password is empty
$database = "amunra";  // MUST match the database name created in setup.sql

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// OPTIONAL: Set character set to UTF-8
$conn->set_charset("utf8");

// Error reporting for development (remove in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
