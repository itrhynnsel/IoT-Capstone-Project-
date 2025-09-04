<?php
include '1config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kunin muna yung data sa archive (users1)
    $sql = "SELECT * FROM users1 WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Hatiin ang fullname kung meron
            $parts = explode(" ", $row['fullname'] ?? '');
            $first_name = $parts[0] ?? '';
            $middle_name = '';
            $last_name = $parts[1] ?? '';
            $extension_name = '';

            // INSERT sa main users table
            $insert = "INSERT INTO users 
                (first_name, middle_name, last_name, extension_name, gender, contact_number, email, role, region, province, city, barangay, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_insert = $conn->prepare($insert)) {
                $gender = 'Male'; // default (pwede mo palitan kung may gender sa archive)
                $contact_number = '';
                $role = 'User';
                $region = '';
                $province = '';
                $city = '';
                $barangay = '';
                $created_at = $row['created_at'];

                $stmt_insert->bind_param(
                    "sssssssssssss",
                    $first_name,
                    $middle_name,
                    $last_name,
                    $extension_name,
                    $gender,
                    $contact_number,
                    $row['email'],
                    $role,
                    $region,
                    $province,
                    $city,
                    $barangay,
                    $created_at
                );

                if ($stmt_insert->execute()) {
                    // Delete sa archive
                    $delete = "DELETE FROM users1 WHERE id=?";
                    $stmt_del = $conn->prepare($delete);
                    $stmt_del->bind_param("i", $id);
                    $stmt_del->execute();

                    // ✅ JavaScript Alert + Redirect
                    echo "<script>
                            alert('✅ User restored successfully!');
                            window.location.href = '3archives.php';
                          </script>";
                    exit;
                } else {
                    echo "<script>
                            alert('❌ Restore failed: " . addslashes($stmt_insert->error) . "');
                            window.location.href = '3archives.php';
                          </script>";
                }
            } else {
                echo "<script>
                        alert('❌ Insert prepare failed: " . addslashes($conn->error) . "');
                        window.location.href = '3archives.php';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('❌ No record found in archive.');
                    window.location.href = '3archives.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('❌ Select prepare failed: " . addslashes($conn->error) . "');
                window.location.href = '3archives.php';
              </script>";
    }
} else {
    echo "<script>
            alert('❌ Invalid request.');
            window.location.href = '3archives.php';
          </script>";
}
?>
