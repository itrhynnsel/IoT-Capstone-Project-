<?php
include '1config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kunin muna data mula sa users
    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Buoin ang fullname
        $fullname = $row['first_name'];
        if (!empty($row['middle_name'])) $fullname .= " " . $row['middle_name'];
        $fullname .= " " . $row['last_name'];
        if (!empty($row['extension_name'])) $fullname .= " " . $row['extension_name'];

        // Insert sa archive table
        $stmt = $conn->prepare("INSERT INTO users1 (fullname, email, created_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $row['email'], $row['created_at']);
        $stmt->execute();

        // Delete from active
        $conn->query("DELETE FROM users WHERE id=$id");
    }
}

header("Location: 1index.php");
exit;
?>
