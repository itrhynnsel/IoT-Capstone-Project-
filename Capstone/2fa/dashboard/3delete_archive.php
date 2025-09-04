<?php
include '1config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM users1 WHERE id=$id");
}
header("Location: 3archives.php");
exit;
?>
