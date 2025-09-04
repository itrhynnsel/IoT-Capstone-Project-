<?php
include '1config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kunin muna data mula sa users
    $res = $conn->query("SELECT * FROM users WHERE id=$id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();

        // Insert sa archive table
        $stmt = $conn->prepare("INSERT INTO archive 
            (first_name, middle_name, last_name, extension_name, gender, contact_number, email, role, region, province, city, barangay, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssssssss",
            $row['first_name'], $row['middle_name'], $row['last_name'], $row['extension_name'],
            $row['gender'], $row['contact_number'], $row['email'], $row['role'],
            $row['region'], $row['province'], $row['city'], $row['barangay'],
            $row['created_at']
        );
        $stmt->execute();

        // Delete from active
        $conn->query("DELETE FROM users WHERE id=$id");
    }
}

header("Location: 1index.php");
exit;
?>
