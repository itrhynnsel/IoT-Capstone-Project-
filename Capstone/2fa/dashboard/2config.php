<?php
$servername = "localhost";
$username   = "root";      // default sa XAMPP/MAMP
$password   = "";          // default blank sa XAMPP
$dbname     = "user_management"; // pangalan ng database mo

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
