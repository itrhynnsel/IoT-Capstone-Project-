<?php
include '1config.php';

// ✅ Timezone
date_default_timezone_set("Asia/Manila");
$nowDate = date("M d, Y");
$nowTime = date("h:i A");

// ADD SENSOR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sensor'])) {
    $name = $_POST['name'];
    $key_prefix = $_POST['key_prefix'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $icon = "";
    if (!empty($_FILES['icon']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $icon = time() . "_" . basename($_FILES['icon']['name']);
        move_uploaded_file($_FILES['icon']['tmp_name'], $targetDir . $icon);
    }

    $stmt = $conn->prepare("INSERT INTO sensors (icon, name, key_prefix, description, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssss", $icon, $name, $key_prefix, $description, $status);
    $stmt->execute();
    $stmt->close();
    header("Location: 7systemsensor.php?page=sensors");
    exit();
}

// EDIT SENSOR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_sensor'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $key_prefix = $_POST['key_prefix'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    $icon = $_POST['old_icon'];
    if (!empty($_FILES['icon']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $icon = time() . "_" . basename($_FILES['icon']['name']);
        move_uploaded_file($_FILES['icon']['tmp_name'], $targetDir . $icon);
    }

    $stmt = $conn->prepare("UPDATE sensors SET icon=?, name=?, key_prefix=?, description=?, status=? WHERE id=?");
    $stmt->bind_param("sssssi", $icon, $name, $key_prefix, $description, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: 7systemsensor.php?page=sensors");
    exit();
}

// DELETE SENSOR
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT icon FROM sensors WHERE id=$id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['icon']) && file_exists("uploads/" . $row['icon'])) unlink("uploads/" . $row['icon']);
    }
    $conn->query("DELETE FROM sensors WHERE id=$id");
    header("Location: 7systemsensor.php?page=sensors");
    exit();
}

// SEARCH FILTER
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$sql = "SELECT * FROM sensors";
if ($search !== "") {
    $searchTerm = "%".$conn->real_escape_string($search)."%";
    $stmt = $conn->prepare("SELECT * FROM sensors WHERE name LIKE ? OR key_prefix LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM sensors ORDER BY created_at DESC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>System Sensors</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary:#28a745; --secondary:#ffc107; --danger:#dc3545; --info:#17a2b8; --gray:#6c757d; --sidebar-width:250px;
    }
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    body{background:#f4f6f9;display:flex;min-height:100vh;color:#212529;}

    /* Sidebar */
    .sidebar{width:var(--sidebar-width);background:#fff;border-right:1px solid #e0e0e0;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;}
    .profile{text-align:center;padding:20px;border-bottom:1px solid #e0e0e0;}
    .profile img{width:90px;height:90px;border-radius:50%;object-fit:cover;margin-bottom:10px;}
    .profile h3{font-size:16px;margin-bottom:4px;}
    .profile p{font-size:14px;color:var(--gray);}
    .sidebar ul{list-style:none;padding:0;margin-top:10px;}
    .sidebar ul li a{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#212529;text-decoration:none;font-size:15px;}
    .sidebar ul li a:hover,.sidebar ul li a.active{background:#d4f5df;color:var(--primary);font-weight:600;border-radius:6px;}
    .sidebar ul li a i{width:20px;text-align:center;}

    /* Main */
    .main-content{flex:1;margin-left:var(--sidebar-width);display:flex;flex-direction:column;}
    .topbar{background:var(--primary);color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;font-size:14px;font-weight:500;}
    .container{padding:20px;}
    .breadcrumb{font-size:14px;margin-bottom:15px;}
    .breadcrumb span{color:var(--gray);}

    .warning{margin-bottom:15px;padding:12px;background:#fff3cd;border:1px solid #ffeeba;border-radius:6px;font-size:14px;color:#856404;}

    .actions{display:flex;justify-content:space-between;margin-bottom:15px;gap:10px;flex-wrap:wrap;}
    .actions form{flex:1;}
    .actions form input[type="text"]{padding:10px 15px;border:1px solid #ddd;border-radius:6px;min-width:280px;width:100%;}
    .btn{padding:8px 14px;border:none;border-radius:6px;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;}
    .btn-add{background:var(--primary);color:#fff;}
    .btn-edit{background:var(--secondary);color:#fff;}
    .btn-delete{background:var(--danger);color:#fff;}

    table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
    th,td{padding:12px;text-align:center;border-bottom:1px solid #f0f0f0;font-size:14px;}
    th{background:#f9fbfc;color:#495057;font-weight:600;}
    tr:last-child td{border-bottom:none;}
    img.icon{width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #ddd;}
    .status{padding:4px 10px;border-radius:12px;font-size:12px;font-weight:500;color:#fff;}
    .status.Active{background:#28a745;}
    .status.Inactive{background:#6c757d;}

    /* Modal */
    .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;z-index:999;}
    .modal-content{background:#fff;padding:25px;border-radius:8px;width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.2);}
    .modal-content h2{margin-bottom:15px;font-size:18px;}
    .modal-content input,.modal-content textarea,.modal-content select{width:100%;padding:10px;margin:6px 0 15px;border:1px solid #ccc;border-radius:6px;}
    .modal-footer{display:flex;justify-content:flex-end;gap:10px;}
    .btn-close{background:#6c757d;color:#fff;}
    .btn-update{background:#28a745;color:#fff;}
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
      <li><a href="3archives.php"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="6activity.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="7systemsensor.php?page=sensors" class="active"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="8pagefeature.php"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-microchip"></i> System Sensors</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <div class="breadcrumb">Home / <span>System Sensors</span></div>

      <div class="warning">⚠️ Developer Only: Modifying sensor structure may cause system errors. Proceed carefully.</div>

      <div class="actions">
        <form method="get">
          <input type="text" name="search" placeholder="Search sensors..." value="<?php echo htmlspecialchars($search); ?>" oninput="this.form.submit()">
        </form>
        <button class="btn btn-add" type="button" onclick="openAddModal()"><i class="fa fa-plus"></i> Add Sensor</button>
      </div>

      <!-- Table -->
      <table>
        <tr><th>Icon</th><th>Name</th><th>Key Prefix</th><th>Description</th><th>Status</th><th>Created</th><th>Actions</th></tr>
        <?php if ($result && $result->num_rows > 0) { while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php if (!empty($row['icon'])) { ?><img class="icon" src="7uploads/<?php echo $row['icon']; ?>"><?php } else { ?>No Icon<?php } ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td style="color:crimson;"><?php echo htmlspecialchars($row['key_prefix']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
            <td>
              <button class="btn btn-edit" type="button" onclick="openEditModal(<?php echo $row['id']; ?>,'<?php echo addslashes($row['name']); ?>','<?php echo addslashes($row['key_prefix']); ?>','<?php echo addslashes($row['description']); ?>','<?php echo $row['icon']; ?>','<?php echo $row['status']; ?>')"><i class="fa fa-pen"></i></button>
              <a href="7systemsensor.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this sensor?')" class="btn btn-delete"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
        <?php }} else { ?>
          <tr><td colspan="7">No sensors found</td></tr>
        <?php } ?>
      </table>
    </div>
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2>Add Sensor</h2>
      <form method="post" enctype="multipart/form-data">
        <label>Icon</label><input type="file" name="icon" required>
        <label>Name</label><input type="text" name="name" required>
        <label>Key Prefix</label><input type="text" name="key_prefix" required>
        <label>Description</label><textarea name="description" required></textarea>
        <label>Status</label>
        <select name="status" required><option>Active</option><option>Inactive</option></select>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('addModal')">Close</button>
          <button type="submit" name="add_sensor" class="btn btn-update">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2>Edit Sensor</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="old_icon" id="edit_old_icon">
        <label>Icon</label><input type="file" name="icon">
        <label>Name</label><input type="text" name="name" id="edit_name" required>
        <label>Key Prefix</label><input type="text" name="key_prefix" id="edit_key_prefix" required>
        <label>Description</label><textarea name="description" id="edit_description" required></textarea>
        <label>Status</label>
        <select name="status" id="edit_status" required><option>Active</option><option>Inactive</option></select>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('editModal')">Close</button>
          <button type="submit" name="edit_sensor" class="btn btn-update">Update</button>
        </div>
      </form>
    </div>
  </div>

<script>
function openAddModal(){document.getElementById('addModal').style.display='flex';}
function openEditModal(id,name,key_prefix,description,icon,status){
  document.getElementById('edit_id').value=id;
  document.getElementById('edit_name').value=name;
  document.getElementById('edit_key_prefix').value=key_prefix;
  document.getElementById('edit_description').value=description;
  document.getElementById('edit_old_icon').value=icon;
  document.getElementById('edit_status').value=status;
  document.getElementById('editModal').style.display='flex';
}
function closeModal(id){document.getElementById(id).style.display='none';}
</script>

</body>
</html>
