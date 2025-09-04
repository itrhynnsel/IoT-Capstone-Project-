<?php
include '2config.php';

// Time & header info
date_default_timezone_set('Asia/Manila');
$nowDate = date("M d, Y");
$nowTime = date("h:i A");

// Kunin lahat ng archived users
$sql = "SELECT id, fullname, email, created_at FROM users1 ORDER BY id DESC";
$result = $conn->query($sql);

// Check if query was successful
if ($result === false) {
    $error_message = $conn->error;
    $num_archives = 0;
} else {
    $num_archives = $result->num_rows;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Font Awesome (for sidebar icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #28a745;
            --secondary: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --gray: #6c757d;
            --sidebar-width: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f4f6f9;
            display: flex;
            min-height: 100vh;
            color: #212529;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #e0e0e0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .profile {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .profile img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .profile h3 {
            font-size: 16px;
            margin-bottom: 4px;
            color: #212529;
        }

        .profile p {
            font-size: 14px;
            color: var(--gray);
            margin: 0;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 10px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #212529;
            text-decoration: none;
            transition: 0.3s;
            font-size: 15px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: #d4f5df;
            color: var(--primary);
            font-weight: 600;
            border-radius: 6px;
        }

        .sidebar ul li a i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: var(--primary);
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
        }

        .container {
            padding: 20px;
        }

        .breadcrumb {
            font-size: 14px;
            margin-bottom: 15px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            margin-right: 5px;
        }

        .breadcrumb span {
            color: var(--gray);
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--info);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .back-link:hover {
            opacity: 0.9;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        th {
            background: #f9fbfc;
            color: #495057;
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .restore {
            background: var(--primary);
        }

        .delete {
            background: var(--danger);
        }

        /* Alert Messages */
        .alert {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 880px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 600px) {
            th:nth-child(1), 
            td:nth-child(1) {
                display: none;
            }
        }
    </style>
</head>
<body>

  <div class="sidebar">
    <div class="profile">
      <img src="rhynnsel.png" alt="User">
      <h3>Juan D. Cruz Jr.</h3>
      <p>Developer</p>
    </div>
    <ul>
      <li><a href="dashboard.php"><i class="fa fa-gauge"></i> Dashboard</a></li>
      <li><a href="1index.php"><i class="fa fa-users"></i> Users</a></li>
      <li><a href="2sites.php"><i class="fa fa-building"></i> Sites</a></li>
      <li><a href="3archives.php" class="active"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="index.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="index.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-box-archive"></i> Archived Users</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <div class="breadcrumb">
       
      </div>

      <?php if (isset($_GET['restore']) && $_GET['restore'] === 'success'): ?>
        <div class="alert success">Archived user restored successfully.</div>
      <?php endif; ?>

      

      <table>
        <tr>
          <th>ID</th>
          <th>Fullname</th>
          <th>Email</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['fullname']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "<td>
                        <a class='btn restore' href='3restore_archive.php?id=" . (int)$row['id'] . "'><i class='fa fa-rotate-left'></i> Restore</a>
                        <a class='btn delete' href='3delete_archive.php?id=" . (int)$row['id'] . "' onclick=\"return confirm('Are you sure you want to delete this user permanently?');\"><i class='fa fa-trash'></i> Delete</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='no-data'>No archived users found</td></tr>";
        }
        ?>
      </table>
    </div>
  </div>
</body>
</html>