<?php
include '2config.php';

if (isset($_GET['id'])) {
    $site_id = $_GET['id'];

    $sql = "DELETE FROM sites WHERE id = $site_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: 2sites.php?deleted=1");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: 2sites.php");
    exit();
}
?>
