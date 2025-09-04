<?php  
include '1config.php'; 

// ✅ Timezone at Date/Time
date_default_timezone_set('Asia/Manila'); 
$nowDate = date('l, F j, Y'); 
$nowTime = date('h:i A'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    /* Actions */
    .actions {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .actions form input[type="text"] {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      min-width: 280px;
    }

    .btn {
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-add {
      background: var(--primary);
      color: #fff;
    }

    .btn-edit {
      background: var(--secondary);
      color: #fff;
    }

    .btn-delete {
      background: var(--danger);
      color: #fff;
    }

    .btn-view {
      background: var(--info);
      color: #fff;
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
      text-align: center;
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

    .role-badge {
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      background: #eaf7ec;
      color: var(--primary);
      font-weight: 500;
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
      <li><a href="1index.php" class="active"><i class="fa fa-users"></i> Users</a></li>
      <li><a href="2sites.php"><i class="fa fa-building"></i> Sites</a></li>
      <li><a href="3archives.php"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="index.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="index.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-users"></i> User Management</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <div class="breadcrumb">
      
      </div>

      <!-- Actions -->
      <div class="actions">
        <form method="GET" action="">
          <input type="text" name="search" placeholder="Search user by name, username, email or role..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : '' ?>">
        </form>
        <a href="1add_user.php" class="btn btn-add"><i class="fa fa-plus"></i> Add User</a>
      </div>

      <!-- Users Table -->
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
          <th>Actions</th>
        </tr>
        <?php
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $sql = "SELECT * FROM users WHERE 
                  first_name LIKE '%$search%' OR 
                  last_name LIKE '%$search%' OR 
                  email LIKE '%$search%' OR
                  role LIKE '%$search%' OR
                  region LIKE '%$search%' OR
                  province LIKE '%$search%' OR
                  city LIKE '%$search%' OR
                  barangay LIKE '%$search%'";

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['id']."</td>
                    <td>".$row['first_name']." ".$row['middle_name']." ".$row['last_name']." ".$row['extension_name']."</td>
                    <td>".$row['gender']."</td>
                    <td>".$row['contact_number']."</td>
                    <td>".$row['email']."</td>
                    <td><span class='role-badge'>".$row['role']."</span></td>
                    <td>".$row['region']."</td>
                    <td>".$row['province']."</td>
                    <td>".$row['city']."</td>
                    <td>".$row['barangay']."</td>
                    <td>
                      <a href='1view_user.php?id=".$row['id']."' class='btn btn-view'><i class='fa fa-eye'></i></a>
                      <a href='1edit_user.php?id=".$row['id']."' class='btn btn-edit'><i class='fa fa-pen'></i></a>
                      <a href='1delete_user.php?id=".$row['id']."' class='btn btn-delete' onclick=\"return confirm('Are you sure?')\"><i class='fa fa-trash'></i></a>
                    </td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='11'>No users found</td></tr>";
        }
        ?>
      </table>
    </div>
  </div>
</body>
</html>
