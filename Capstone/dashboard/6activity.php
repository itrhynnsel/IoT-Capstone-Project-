<?php
include '1config.php';

// ✅ Timezone
date_default_timezone_set('Asia/Manila'); 
$nowDate = date('l, F j, Y'); 
$nowTime = date('h:i A'); 

// Handle Add
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $iconPath = "";
    if (!empty($_FILES["icon"]["name"])) {
        $targetDir = "icons/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["icon"]["name"]);
        $iconPath = $targetDir . $fileName;
        move_uploaded_file($_FILES["icon"]["tmp_name"], $iconPath);
    }

    $conn->query("INSERT INTO activity_types (icon, name, description, status, created_at) 
                  VALUES ('$iconPath','$name','$desc','$status',NOW())");
    header("Location: index.php?page=greenhouse");
    exit;
}

// Handle Edit
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    if (!empty($_FILES["icon"]["name"])) {
        $targetDir = "icons/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["icon"]["name"]);
        $iconPath = $targetDir . $fileName;
        move_uploaded_file($_FILES["icon"]["tmp_name"], $iconPath);

        $conn->query("UPDATE activity_types SET name='$name', description='$desc', status='$status', icon='$iconPath' WHERE id=$id");
    } else {
        $conn->query("UPDATE activity_types SET name='$name', description='$desc', status='$status' WHERE id=$id");
    }
    header("Location: index.php?page=greenhouse");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $res = $conn->query("SELECT icon FROM activity_types WHERE id=$id");
    if ($res && $row = $res->fetch_assoc()) {
        if (!empty($row['icon']) && file_exists($row['icon'])) {
            unlink($row['icon']);
        }
    }
    $conn->query("DELETE FROM activity_types WHERE id=$id");
    header("Location: index.php?page=greenhouse");
    exit;
}

// Handle Search
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
if (!empty($search)) {
    $sql = "SELECT * FROM activity_types 
            WHERE name LIKE '%$search%' OR description LIKE '%$search%' 
            ORDER BY created_at DESC";
} else {
    $sql = "SELECT * FROM activity_types ORDER BY created_at DESC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Greenhouse Activity Types</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #28a745;
      --secondary: #ffc107;
      --danger: #dc3545;
      --info: #17a2b8;
      --dark: #343a40;
      --gray: #6c757d;
      --sidebar-width: 250px;
    }
    * {margin:0;padding:0;box-sizing:border-box;font-family: 'Segoe UI',sans-serif;}
    body {background:#f4f6f9;display:flex;min-height:100vh;color:#212529;}

    /* Sidebar (same as 1index.php) */
    .sidebar {
      width: var(--sidebar-width);
      background: #fff;
      border-right: 1px solid #e0e0e0;
      height: 100vh;
      position: fixed;
      top: 0; left: 0;
      display: flex; flex-direction: column;
    }
    .profile {text-align:center;padding:20px;border-bottom:1px solid #e0e0e0;}
    .profile img {width:90px;height:90px;border-radius:50%;object-fit:cover;margin-bottom:10px;}
    .profile h3 {font-size:16px;margin-bottom:4px;}
    .profile p {font-size:14px;color:var(--gray);}
    .sidebar ul {list-style:none;padding:0;margin-top:10px;}
    .sidebar ul li a {display:flex;align-items:center;gap:10px;padding:12px 20px;color:#212529;text-decoration:none;font-size:15px;}
    .sidebar ul li a:hover, .sidebar ul li a.active {background:#d4f5df;color:var(--primary);font-weight:600;border-radius:6px;}
    .sidebar ul li a i {width:20px;text-align:center;}

    /* Main */
    .main-content {flex:1;margin-left:var(--sidebar-width);display:flex;flex-direction:column;}
    .topbar {background:var(--primary);color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;font-size:14px;font-weight:500;}
    .container {padding:20px;}
    .breadcrumb {font-size:14px;margin-bottom:15px;}
    .breadcrumb span {color:var(--gray);}

    .actions {display:flex;justify-content:space-between;margin-bottom:15px;gap:10px;flex-wrap:wrap;}
    .actions form input[type="text"] {padding:10px 15px;border:1px solid #ddd;border-radius:6px;min-width:280px;}
    .btn {padding:8px 14px;border:none;border-radius:6px;cursor:pointer;font-size:14px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;}
    .btn-add {background:var(--primary);color:#fff;}
    .btn-edit {background:var(--secondary);color:#fff;}
    .btn-delete {background:var(--danger);color:#fff;}
    .btn-view {background:var(--info);color:#fff;}

    table {width:100%;border-collapse:separate;border-spacing:0;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
    th,td {padding:12px;text-align:center;border-bottom:1px solid #f0f0f0;font-size:14px;}
    th {background:#f9fbfc;color:#495057;font-weight:600;}
    tr:last-child td {border-bottom:none;}
    img.icon {width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #ddd;}
    .status {padding:4px 10px;border-radius:12px;font-size:12px;font-weight:500;color:#fff;}
    .status.active {background:#28a745;}
    .status.inactive {background:#6c757d;}

    /* Modal same style as add/edit user */
    .modal {display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;z-index:999;}
    .modal-content {background:#fff;padding:25px;border-radius:8px;width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.2);}
    .modal-content h2 {margin-bottom:15px;font-size:18px;}
    .modal-content input,.modal-content textarea,.modal-content select {width:100%;padding:10px;margin:6px 0 15px;border:1px solid #ccc;border-radius:6px;}
    .modal-footer {display:flex;justify-content:flex-end;gap:10px;}
    .btn-close {background:#6c757d;color:#fff;}
    .btn-update {background:#28a745;color:#fff;}
    .icon-preview {width:80px;height:80px;object-fit:cover;border:1px solid #ddd;border-radius:6px;margin-bottom:10px;}
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
      <li><a href="6activity.php?page=greenhouse" class="active"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="7systemsensor.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-leaf"></i> Greenhouse Activity Types</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <div class="breadcrumb">Home / <span>Greenhouse Activity Types</span></div>

      <!-- Search + Add -->
      <div class="actions">
        <form method="get">
          <input type="text" name="search" placeholder="Search activity types..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
        <button class="btn btn-add" onclick="openAddModal()"><i class="fa fa-plus"></i> Add Activity Type</button>
      </div>

      <!-- Table -->
      <table>
        <tr><th>Icon</th><th>Name</th><th>Description</th><th>Status</th><th>Created</th><th>Actions</th></tr>
        <?php if ($result && $result->num_rows > 0) { while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php if (!empty($row['icon'])) { ?><img class="icon" src="<?php echo $row['icon']; ?>"><?php } else { ?>No Icon<?php } ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><span class="status <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
            <td>
              <button class="btn btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>,'<?php echo addslashes($row['name']); ?>','<?php echo addslashes($row['description']); ?>','<?php echo $row['status']; ?>','<?php echo $row['icon']; ?>')"><i class="fa fa-pen"></i></button>
              <a href="index.php?page=greenhouse&delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete this activity type?')" class="btn btn-delete"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
        <?php }} else { ?>
          <tr><td colspan="6">No activity types found</td></tr>
        <?php } ?>
      </table>
    </div>
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2>Add Activity Type</h2>
      <form method="post" enctype="multipart/form-data">
        <label>Name</label><input type="text" name="name" required>
        <label>Description</label><textarea name="description" required></textarea>
        <label>Status</label>
        <select name="status" required><option>Active</option><option>Inactive</option></select>
        <label>Icon</label><input type="file" name="icon" accept="image/*" required>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('addModal')">Close</button>
          <button type="submit" name="add" class="btn btn-update">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2>Edit Activity Type</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit_id">
        <img id="edit_icon_preview" class="icon-preview" src="">
        <label>Icon</label><input type="file" name="icon" accept="image/*">
        <label>Name</label><input type="text" name="name" id="edit_name" required>
        <label>Description</label><textarea name="description" id="edit_desc" required></textarea>
        <label>Status</label>
        <select name="status" id="edit_status" required><option>Active</option><option>Inactive</option></select>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('editModal')">Close</button>
          <button type="submit" name="edit" class="btn btn-update">Update</button>
        </div>
      </form>
    </div>
  </div>

<script>
function openAddModal(){document.getElementById('addModal').style.display='flex';}
function openEditModal(id,name,desc,status,icon){
  document.getElementById('edit_id').value=id;
  document.getElementById('edit_name').value=name;
  document.getElementById('edit_desc').value=desc;
  document.getElementById('edit_status').value=status;
  if(icon){document.getElementById('edit_icon_preview').src=icon;document.getElementById('edit_icon_preview').style.display='block';}
  else{document.getElementById('edit_icon_preview').style.display='none';}
  document.getElementById('editModal').style.display='flex';
}
function closeModal(id){document.getElementById(id).style.display='none';}
</script>

</body>
</html>
