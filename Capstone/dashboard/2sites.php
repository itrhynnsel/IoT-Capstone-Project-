<?php
include '1config.php';

// Set timezone para consistent ang oras
date_default_timezone_set('Asia/Manila');
$nowDate = date("l, F d, Y");   // Thursday, August 28, 2025
$nowTime = date("h:i A");       // 02:54 AM

// Kunin lahat ng sites kasama ang owner details
$sql = "SELECT s.id, s.name, s.region, s.province, s.city, s.barangay, 
               u.first_name, u.last_name
        FROM sites s
        LEFT JOIN users u ON s.owner = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartTemp SYSTEM - Sites</title>
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

    /* Actions */
    .actions-container {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .search-bar {
      display: flex;
      gap: 10px;
    }

    input[type="text"], select {
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
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
      padding: 6px 10px;
      font-size: 13px;
    }

    .btn-delete {
      background: var(--danger);
      color: #fff;
      padding: 6px 10px;
      font-size: 13px;
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

    .action-buttons {
      display: flex;
      gap: 8px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .actions-container {
        flex-direction: column;
      }
      
      .search-bar {
        width: 100%;
      }
      
      input[type="text"], select {
        flex: 1;
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
      <li><a href="2sites.php" class="active"><i class="fa fa-building"></i> Sites</a></li>
      <li><a href="3archives.php"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="6activity.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="7systemsensor.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="8pagefeature.php"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-building"></i> Sites Management</div>
      <div><?php echo $nowDate; ?> • <span id="clock"><?php echo $nowTime; ?></span></div>
    </div>

    <div class="container">
      <div class="breadcrumb">
        
      </div>

      <div class="actions-container">
        <div class="search-bar">
          <select>
            <option value="all">All</option>
            <option value="region">By Region</option>
            <option value="province">By Province</option>
          </select>
          <input type="text" placeholder="Search sites...">
        </div>
        <a href="2add_site.php" class="btn btn-add"><i class="fa fa-plus"></i> Add Site</a>
      </div>

      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Owner</th>
            <th>Region</th>
            <th>Province</th>
            <th>City/Municipality</th>
            <th>Barangay</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo "<tr>
                          <td>{$row['name']}</td>
                          <td>{$row['first_name']} {$row['last_name']}</td>
                          <td>{$row['region']}</td>
                          <td>{$row['province']}</td>
                          <td>{$row['city']}</td>
                          <td>{$row['barangay']}</td>
                          <td>
                            <div class='action-buttons'>
                              <a class='btn btn-edit' href='2edit_site.php?id={$row['id']}'><i class='fa fa-pen'></i> Edit</a>
                              <a class='btn btn-delete' href='2delete_site.php?id={$row['id']}'
                                 onclick=\"return confirm('Are you sure you want to delete this site?');\">
                                 <i class='fa fa-trash'></i> Delete
                              </a>
                            </div>
                          </td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='7'>No sites found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

<script>
// Live clock updater
function updateClock() {
    const now = new Date();
    let hours = now.getHours();
    let minutes = now.getMinutes();
    let ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; 
    minutes = minutes < 10 ? '0' + minutes : minutes;
    document.getElementById('clock').textContent = hours + ':' + minutes + ' ' + ampm;
}
setInterval(updateClock, 1000);
updateClock();
</script>
</body>
</html>