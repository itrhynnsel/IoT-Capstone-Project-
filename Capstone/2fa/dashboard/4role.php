<?php
include '4config.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y');
$nowTime = date('h:i A');

// Create roles table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTable);

// Handle Add Role
if (isset($_POST['add_role'])) {
    $name = trim($_POST['role_name']);
    $desc = trim($_POST['role_desc']);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc);
        $stmt->execute();
    }
    header("Location: 4role.php");
    exit();
}

// Handle Edit Role
if (isset($_POST['edit_role'])) {
    $id   = intval($_POST['role_id']);
    $name = trim($_POST['role_name']);
    $desc = trim($_POST['role_desc']);

    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE roles SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        $stmt->execute();
    }
    header("Location: 4role.php");
    exit();
}

// Handle Delete Role
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM roles WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: 4role.php");
    exit();
}

// Fetch roles
$result = $conn->query("SELECT * FROM roles ORDER BY created_at DESC");
$roles  = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Roles Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    /* General */
    body {
      font-family: "Segoe UI", sans-serif;
      background:#f4f6f9;
      margin:0; padding:0;
    }
    .topbar {
      background:#28a745;
      padding:12px 20px;
      color:#fff;
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:15px;
    }
    .container {
      max-width:1100px;
      margin:20px auto;
      padding:20px;
      background:#fff;
      border-radius:10px;
      box-shadow:0 2px 6px rgba(0,0,0,0.1);
    }

    /* Tabs */
    .tabs { display:flex; gap:10px; margin-bottom:20px; }
    .tab {
      padding:10px 18px;
      background:#fff;
      border:1px solid #ddd;
      border-radius:6px;
      text-decoration:none;
      color:#212529;
      font-weight:500;
      transition:.3s;
    }
    .tab:hover { background:#f1f1f1; }
    .tab.active { background:#28a745; color:#fff; border-color:#28a745; }

    /* Table */
    table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; }
    th, td { padding:12px; border:1px solid #ddd; text-align:center; font-size:14px; }
    th { background:#f9fbfc; }

    /* Buttons */
    .btn {
      padding:8px 14px;
      border:none;
      border-radius:6px;
      cursor:pointer;
      font-size:13px;
      font-weight:500;
      transition:0.3s;
      margin:2px;
    }
    .btn-add { background:#28a745; color:#fff; }
    .btn-add:hover { background:#218838; }
    .btn-edit { background:#007bff; color:#fff; }
    .btn-edit:hover { background:#0069d9; }
    .btn-del { background:#dc3545; color:#fff; }
    .btn-del:hover { background:#c82333; }

    /* Modal */
    .modal {
      display:none;
      position:fixed;
      top:0; left:0; width:100%; height:100%;
      background:rgba(0,0,0,0.5);
      justify-content:center; align-items:center;
      z-index:1000;
    }
    .modal-content {
      background:#fff;
      padding:20px;
      border-radius:10px;
      width:400px;
      max-width:95%;
    }
    .modal-header { font-size:18px; margin-bottom:10px; }
    .modal form input, .modal form textarea {
      width:100%;
      padding:10px;
      margin-bottom:10px;
      border:1px solid #ccc;
      border-radius:6px;
      font-size:14px;
    }
    .close { float:right; font-size:20px; cursor:pointer; }
   
    select {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 6px;
  border: 1px solid #ddd;
  font-size: 14px;
}
select:focus {
  outline: none;
  border-color: #28a745;
  box-shadow: 0 0 5px rgba(40, 167, 69, .3);
}

  </style>
</head>
<body>
  <div class="topbar">
    <div><i class="fa fa-user-shield"></i> Roles Management</div>
    <div><?php echo $nowDate . " • " . $nowTime; ?></div>
  </div>

  <div class="container">
    <!-- Tabs -->
    <div class="tabs">
      <a href="4role.php" class="tab active">Roles Management</a>
      <a href="4role_matrix.php" class="tab">Role-Feature Matrix</a>
      <a href="4user_restrictions.php" class="tab">User Restrictions</a>
    </div>

    <button class="btn btn-add" onclick="openModal('addModal')">
      <i class="fa fa-plus"></i> Add Role
    </button>

    <table>
      <tr>
        <th>ID</th>
        <th>Role Name</th>
        <th>Description</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
      <?php if($roles): foreach($roles as $role): ?>
      <tr>
        <td><?php echo $role['id']; ?></td>
        <td><?php echo htmlspecialchars($role['name']); ?></td>
        <td><?php echo htmlspecialchars($role['description']); ?></td>
        <td><?php echo date('M j, Y h:i A', strtotime($role['created_at'])); ?></td>
        <td>
          <button class="btn btn-edit" onclick="editRole('<?php echo $role['id']; ?>','<?php echo htmlspecialchars($role['name']); ?>','<?php echo htmlspecialchars($role['description']); ?>')">
            <i class="fa fa-edit"></i>
          </button>
          <a href="4role.php?delete=<?php echo $role['id']; ?>" class="btn btn-del" onclick="return confirm('Delete this role?');">
            <i class="fa fa-trash"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="5">No roles found</td></tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- ADD ROLE MODAL -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('addModal')">&times;</span>
    <div class="modal-header">Add Role</div>
    
    <form method="POST">
      <!-- Dropdown for Role Name -->
      <label for="role_name">Select Role</label>
      <select name="role_name" id="role_name" onchange="setRoleDescription()" required>
        <option value="" disabled selected>-- Choose Role --</option>
        <option value="Admin">Admin</option>
        <option value="Developer">Developer</option>
        <option value="Super Admin">Super Admin</option>
        <option value="Super Visors">Super Visors</option>
        <option value="Technician">Technician</option>
      </select>

      <!-- Auto-filled Description -->
      <label for="role_desc">Description</label>
      <textarea name="role_desc" id="role_desc" placeholder="Description" readonly></textarea>

      <!-- Save Button -->
      <button type="submit" name="add_role" class="btn btn-add">Save</button>
    </form>
  </div>
</div>

  <!-- Edit Role Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('editModal')">&times;</span>
      <div class="modal-header">Edit Role</div>
      <form method="POST">
        <input type="hidden" name="role_id" id="edit_id">
        <input type="text" name="role_name" id="edit_name" required>
        <textarea name="role_desc" id="edit_desc"></textarea>
        <button type="submit" name="edit_role" class="btn btn-edit">Update</button>
      </form>
    </div>
  </div>

  <script>
function setRoleDescription() {
  const role = document.getElementById("role_name").value;
  const descField = document.getElementById("role_desc");

  const descriptions = {
    "Admin": "Full CRUD on devices, users, thresholds, and settings.",
    "Developer": "Special access to modify process of the system.",
    "Super Admin": "Full access to the system.",
    "Super Visors": "View live/historical data and generate reports; cannot modify device settings.",
    "Technician": "View live/historical data, acknowledge alerts, but cannot modify system configuration."
  };

  descField.value = descriptions[role] || "";
}


    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }
    function editRole(id, name, desc) {
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_desc').value = desc;
      openModal('editModal');
    }
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
      }
    }
  </script>
</body>
</html>
