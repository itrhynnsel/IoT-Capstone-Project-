<?php
$host = "localhost";
$user = "root";   // palitan kung may password ka
$pass = "";
$db   = "user_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
