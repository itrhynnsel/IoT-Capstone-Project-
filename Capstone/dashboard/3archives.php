<?php
include '1config.php';

// Time & header info
date_default_timezone_set('Asia/Manila');
$nowDate = date("M d, Y");
$nowTime = date("h:i A");

// Kunin lahat ng archived users
$result = $conn->query("SELECT * FROM archive ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Font Awesome -->
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
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
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
            font-size: 13px;
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

  <!-- Sidebar -->
  <div class="sidebar">
    <div class="profile">
      <img src="rhynnsel.png" alt="User">
      <h3>Juan D. Cruz Jr.</h3>
      <p>Developer</p>
    </div>
    <ul>
      <li><a href="0dashboard.php"><i class="fa fa-gauge"></i> Dashboard</a></li>
      <li><a href="1index.php"><i class="fa fa-users"></i> Users</a></li>
      <li><a href="2sites.php"><i class="fa fa-building"></i> Sites</a></li>
      <li><a href="3archives.php" class="active"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="6activity.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="7systemsensor.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="8pagefeature.php"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-box-archive"></i> Archived Users</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

   

      <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Gender</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Role</th>
            <th>Region</th>
            <th>Province</th>
            <th>City</th>
            <th>Barangay</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td>
                        <?php 
                            echo htmlspecialchars($row['first_name']);
                            if (!empty($row['middle_name'])) echo " " . htmlspecialchars($row['middle_name']);
                            echo " " . htmlspecialchars($row['last_name']);
                            if (!empty($row['extension_name'])) echo " " . htmlspecialchars($row['extension_name']);
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo htmlspecialchars($row['region']); ?></td>
                    <td><?php echo htmlspecialchars($row['province']); ?></td>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><?php echo htmlspecialchars($row['barangay']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <a href="3restore_archive.php?id=<?php echo $row['id']; ?>" class="btn restore"><i class="fa fa-rotate-left"></i> Restore</a>
                        <a href="3delete_archive.php?id=<?php echo $row['id']; ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this user permanently?');"><i class="fa fa-trash"></i> Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="12" class="no-data">No archived users found</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</body>
</html>
