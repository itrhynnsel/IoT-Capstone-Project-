<?php
// 4config.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'smarttemp_rbac';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) { die("DB connect error: ".$conn->connect_error); }
$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);
$conn->set_charset('utf8mb4');
