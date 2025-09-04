<?php
include '5config.php';
require_once '5rbac.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y'); 
$nowTime = date('h:i A');

/* Add / Delete */
$success_message = $error_message = '';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  if (isset($_POST['add_role'])){
    $name = trim($_POST['role_name']??'');
    $desc = trim($_POST['role_desc']??'');
    $isDev = ($name==='Developer') ? 1 : 0;
    if ($name==='' || $desc===''){ $error_message = 'Please complete all fields.'; }
    else {
      $stmt = $conn->prepare("INSERT INTO roles(name,description,is_developer) VALUES(?,?,?)");
      $stmt->bind_param("ssi",$name,$desc,$isDev);
      if ($stmt->execute()){
        $success_message = 'Role added successfully!';
        // ensure role_features rows for new role
        $rid = $conn->insert_id;
        $fs = $conn->query("SELECT id FROM features");
        $ins = $conn->prepare("INSERT IGNORE INTO role_features(role_id,feature_id,allowed) VALUES(?,?,0)");
        while($f=$fs->fetch_assoc()){ $fid=(int)$f['id']; $ins->bind_param("ii",$rid,$fid); $ins->execute(); }
      } else {
        $error_message = 'Error: '.$conn->error;
      }
    }
  }
  if (isset($_POST['delete_role'])){
    $id = (int)($_POST['role_id']??0);
    if ($id>0){
      $conn->query("DELETE FROM role_features WHERE role_id=$id");
      if ($conn->query("DELETE FROM roles WHERE id=$id")) $success_message='Role deleted successfully.';
      else $error_message='Delete failed: '.$conn->error;
    }
  }
}

$search = trim($_GET['search']??'');
if ($search!==''){
  $like = "%".$conn->real_escape_string($search)."%";
  $roles = $conn->query("SELECT * FROM roles WHERE name LIKE '$like' OR description LIKE '$like' ORDER BY created_at DESC");
} else {
  $roles = $conn->query("SELECT * FROM roles ORDER BY created_at DESC");
}
$rows = $roles? $roles->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roles Management</title>
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

    /* Sidebar - Exactly matching user.php */
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

    /* Tabs */
    .tabs {
      display: flex;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .tab {
      flex: 1;
      text-align: center;
      padding: 12px;
      text-decoration: none;
      color: var(--gray);
      font-weight: 500;
      transition: 0.3s;
      border-bottom: 3px solid transparent;
    }
    
    .tab:hover {
      color: var(--primary);
      background: #f9fafd;
    }
    
    .tab.active {
      color: var(--primary);
      border-bottom: 3px solid var(--primary);
      background: #f9fafd;
    }
    
    .tab i {
      margin-right: 8px;
    }

    /* Alerts */
    .alert {
      padding: 12px 16px;
      border-radius: 6px;
      margin: 15px 0;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .success {
      background: #e6f7ee;
      color: #0d6832;
      border-left: 4px solid var(--primary);
    }
    
    .error {
      background: #fde8e8;
      color: #c81e1e;
      border-left: 4px solid var(--danger);
    }

    /* Actions */
    .actions {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .search-box {
      position: relative;
      min-width: 280px;
    }
    
    .search-box input {
      padding: 10px 15px 10px 40px;
      border: 1px solid #ddd;
      border-radius: 6px;
      width: 100%;
      font-size: 14px;
    }
    
    .search-box i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
    }

    .btn {
      padding: 10px 16px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 500;
    }

    .btn-add {
      background: var(--primary);
      color: #fff;
    }

    .btn-danger {
      background: var(--danger);
      color: #fff;
      padding: 6px 10px;
    }

    /* Table */
    .table-container {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      min-width: 600px;
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

    tr:hover {
      background: #f9fafd;
    }

    .dev-pill {
      display: inline-block;
      background: #eaf7ec;
      color: var(--primary);
      border-radius: 12px;
      padding: 4px 10px;
      font-size: 12px;
      font-weight: 500;
      margin-left: 8px;
    }

    .act-inline {
      display: flex;
      gap: 6px;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.5);
      align-items: center;
      justify-content: center;
      z-index: 1000;
      padding: 20px;
    }
    
    .modal-content {
      background: #fff;
      width: 500px;
      max-width: 100%;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    
    .modal-header {
      font-weight: 600;
      font-size: 16px;
      margin-bottom: 15px;
      color: var(--dark);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .close {
      font-size: 22px;
      cursor: pointer;
      color: var(--gray);
    }
    
    .form-row {
      margin-bottom: 15px;
    }
    
    .form-row label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: var(--dark);
    }
    
    select, textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }
    
    textarea[readonly] {
      background: #f9f9f9;
      color: var(--gray);
    }
    
    .form-actions {
      text-align: right;
      margin-top: 10px;
    }
    
    .no-data {
      text-align: center;
      padding: 30px;
      color: var(--gray);
    }
    
    .no-data i {
      font-size: 40px;
      margin-bottom: 10px;
      color: #e9ecef;
    }
  </style>
</head>
<body>
  <!-- Sidebar - Exactly matching user.php -->
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
      <li><a href="3archives.php"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php" class="active"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="index.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="index.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-id-badge"></i> Roles Management</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <!-- Tabs -->
      <div class="tabs">
        <a class="tab active" href="5role.php"><i class="fa fa-id-badge"></i> Roles Management</a>
        <a class="tab" href="5role_matrix.php"><i class="fa fa-table"></i> Role-Feature Matrix</a>
        <a class="tab" href="5user_restrictions.php"><i class="fa fa-user-lock"></i> User Restrictions</a>
      </div>

      <?php if($success_message): ?>
        <div class="alert success">
          <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
      <?php endif; ?>
      
      <?php if($error_message): ?>
        <div class="alert error">
          <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
      <?php endif; ?>

      <!-- Actions -->
      <div class="actions">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <form method="get">
            <input type="text" name="search" placeholder="Search roles..." value="<?php echo htmlspecialchars($search??''); ?>">
          </form>
        </div>
        <button class="btn btn-add" onclick="openModal('addModal')"><i class="fa fa-plus"></i> Add New Role</button>
      </div>

      <!-- Roles Table -->
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Description</th>
              <th>Developer</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($rows)): ?>
              <?php foreach($rows as $r): ?>
                <tr>
                  <td>
                    <?php echo htmlspecialchars($r['name']); ?>
                    <?php if((int)$r['is_developer']===1): ?>
                      <span class="dev-pill">Developer</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo htmlspecialchars($r['description']); ?></td>
                  <td><?php echo ((int)$r['is_developer']===1)?'Yes':'No'; ?></td>
                  <td><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                  <td class="act-inline">
                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this role?');">
                      <input type="hidden" name="role_id" value="<?php echo (int)$r['id']; ?>">
                      <button class="btn btn-danger" name="delete_role" title="Delete Role">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">
                  <div class="no-data">
                    <i class="fas fa-inbox"></i>
                    <p>No roles found</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ADD ROLE MODAL -->
  <div id="addModal" class="modal" aria-hidden="true">
    <div class="modal-content">
      <div class="modal-header">
        <span>Add New Role</span>
        <span class="close" onclick="closeModal('addModal')">&times;</span>
      </div>
      <form method="post">
        <div class="form-row">
          <label for="role_name">Role Name</label>
          <select id="role_name" name="role_name" onchange="setRoleDesc()" required>
            <option value="" selected disabled>-- Select Role --</option>
            <option>Admin</option>
            <option>Developer</option>
            <option>Super Admin</option>
            <option>Super Visors</option>
            <option>Technician</option>
          </select>
        </div>
        <div class="form-row">
          <label for="role_desc">Description</label>
          <textarea id="role_desc" name="role_desc" rows="3" placeholder="Role description" readonly></textarea>
        </div>
        <div class="form-actions">
          <button type="button" class="btn" style="background: var(--secondary); color: #000;" onclick="closeModal('addModal')">Cancel</button>
          <button type="submit" class="btn btn-add" name="add_role">Save Role</button>
        </div>
      </form>
    </div>
  </div>

<script>
  const DESCS = {
    "Admin":"Full CRUD on devices, users, thresholds, and settings.",
    "Developer":"Special access to modify process of the system.",
    "Super Admin":"Full access to the system.",
    "Super Visors":"View live/historical data and generate reports; cannot modify device settings.",
    "Technician":"View live/historical data, acknowledge alerts, but cannot modify system configuration."
  };
  
  function openModal(id){ 
    document.getElementById(id).style.display='flex'; 
  }
  
  function closeModal(id){ 
    document.getElementById(id).style.display='none'; 
  }
  
  function setRoleDesc(){
    const v = document.getElementById('role_name').value;
    document.getElementById('role_desc').value = DESCS[v] || '';
  }
  
  window.addEventListener('click', e => {
    const m = document.getElementById('addModal'); 
    if(e.target === m) closeModal('addModal');
  });
  
  // Close modal on escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeModal('addModal');
    }
  });
</script>
</body>
</html>