<?php
// === pages_and_features.php ===
require_once __DIR__ . "/1config.php";
date_default_timezone_set("Asia/Manila");
$nowDate = date("M d, Y");
$nowTime = date("h:i A");

// ---------- Helper Function ----------
if (!function_exists('now_ph')) {
    function now_ph() {
        date_default_timezone_set("Asia/Manila");
        return date("Y-m-d H:i:s");
    }
}

// ---------- Defaults ----------
$defaultDescs = [
    "archives" => "Auto-discovered page: Archives",
    "dashboard" => "Auto-discovered page: Dashboard",
    "mcus" => "Auto-discovered page: Mcus",
    "pages and features" => "Auto-discovered page: Pages And Features",
    "roles" => "Auto-discovered page: Roles",
    "sensors" => "Auto-discovered page: Sensors",
    "sites" => "Auto-discovered page: Sites",
    "users" => "Auto-discovered page: Users",
];

// ---------- Add Page ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_page') {
    $name = trim($_POST['name'] ?? '');
    $url  = trim($_POST['url'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($name === '' || $url === '') {
        $error = "Name at URL ay required.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM pages WHERE name=? OR url=? LIMIT 1");
        $stmt->bind_param("ss", $name, $url);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Duplicate: Name o URL Already Exist.";
        } else {
            if ($desc === '' && isset($defaultDescs[strtolower($name)])) {
                $desc = $defaultDescs[strtolower($name)];
            }
            $created_at = now_ph();
            $source = 'manual';
            $stmt = $conn->prepare("INSERT INTO pages (name, url, description, source, created_at) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $url, $desc, $source, $created_at);
            $stmt->execute();
            $success = "Page added.";
        }
    }
}

// ---------- Edit Page ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_page') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $url  = trim($_POST['url'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($id <= 0 || $name === '' || $url === '') {
        $error = "Invalid data for edit.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM pages WHERE (name=? OR url=?) AND id<>? LIMIT 1");
        $stmt->bind_param("ssi", $name, $url, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Duplicate: Name o URL Already Exist.";
        } else {
            $stmt = $conn->prepare("UPDATE pages SET name=?, url=?, description=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $url, $desc, $id);
            $stmt->execute();
            $success = "Page updated.";
        }
    }
}

// ---------- Delete Page ----------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM pages WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header("Location: 8pagefeature.php");
    exit;
}

// ---------- Auto-Discover ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'auto_discover') {
    $scanDir = __DIR__;
    $created = 0; $skipped = 0; $found = [];

    if (is_dir($scanDir)) {
        $files = glob($scanDir . "/*.php");
        foreach ($files as $file) {
            $slug = basename($file, ".php");
            $name = ucwords(str_replace("_", " ", $slug));
            $url  = $slug;
            $desc = $defaultDescs[strtolower($slug)] ?? ("Auto-discovered page: " . $name);

            $stmt = $conn->prepare("SELECT id FROM pages WHERE url=? LIMIT 1");
            $stmt->bind_param("s", $url);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $created_at = now_ph();
                $source = 'auto';
                $stmt2 = $conn->prepare("INSERT INTO pages (name, url, description, source, created_at) VALUES (?,?,?,?,?)");
                $stmt2->bind_param("sssss", $name, $url, $desc, $source, $created_at);
                $stmt2->execute();
                $created++;
                $new_page_id = $stmt2->insert_id;

                if ($slug === 'sensors') {
                    $features = [
                        ["Add Sensor", "Action", "Adds a Sensor"],
                        ["Delete Sensor", "Action", "Deletes a Sensor"],
                        ["Edit Sensor", "Action", "Edits a Sensor"],
                        ["View All Sensors", "View", "Views all sensors"],
                    ];
                    $fi = $conn->prepare("INSERT INTO page_features (page_id, feature_name, feature_type, description, created_at) VALUES (?,?,?,?,?)");
                    foreach ($features as $f) {
                        $ts = now_ph();
                        $fi->bind_param("issss", $new_page_id, $f[0], $f[1], $f[2], $ts);
                        $fi->execute();
                    }
                }
            } else {
                $skipped++;
            }
            $found[] = $name . " ($slug)";
        }
    }

    $report = [
        "created" => $created,
        "skipped" => $skipped,
        "found"   => $found
    ];
    $stmt = $conn->prepare("INSERT INTO discovery_logs (summary, created_at) VALUES (?,?)");
    $summary = json_encode($report, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    $ts = now_ph();
    $stmt->bind_param("ss", $summary, $ts);
    $stmt->execute();

    $auto_discover_result = $report;
}

// ---------- Fetch Pages ----------
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = "%" . $conn->real_escape_string($search) . "%";
    $pages = $conn->query("SELECT * FROM pages WHERE name LIKE '$like' OR url LIKE '$like' OR description LIKE '$like' ORDER BY created_at DESC");
} else {
    $pages = $conn->query("SELECT * FROM pages ORDER BY created_at DESC");
}

// Fetch all pages for the dropdown
$all_pages = $conn->query("SELECT id, name FROM pages ORDER BY name");

// Fetch features for the selected page
$selected_page_id = $_GET['page_id'] ?? '';
$features = [];
if ($selected_page_id) {
    $stmt = $conn->prepare("SELECT * FROM page_features WHERE page_id = ? ORDER BY created_at");
    $stmt->bind_param("i", $selected_page_id);
    $stmt->execute();
    $features = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SmartTemp SYSTEM - Pages & Features</title>
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
    .btn-blue{background:#00a3d7;color:#fff;}
    .btn-edit{background:var(--secondary);color:#fff;}
    .btn-delete{background:var(--danger);color:#fff;}

    table{width:100%;border-collapse:separate;border-spacing:0;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.05);}
    th,td{padding:12px;text-align:left;border-bottom:1px solid #f0f0f0;font-size:14px;}
    th{background:#f9fbfc;color:#495057;font-weight:600;}
    tr:last-child td{border-bottom:none;}
    .muted{color:var(--gray);}
    .actions-cell{display:flex;gap:6px;}
    .iconbtn{
        border:0;border-radius:6px;padding:6px 8px;color:#fff;cursor:pointer;text-decoration:none;display:inline-flex;
        align-items:center;justify-content:center;width:32px;height:32px;
    }
    .icon-edit{background:var(--secondary);}
    .icon-del{background:var(--danger);}

    /* Modal */
    .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);justify-content:center;align-items:center;z-index:999;}
    .modal-content{background:#fff;padding:25px;border-radius:8px;width:450px;box-shadow:0 4px 12px rgba(0,0,0,0.2);}
    .modal-content h2{margin-bottom:15px;font-size:18px;}
    .modal-content input,.modal-content textarea,.modal-content select{width:100%;padding:10px;margin:6px 0 15px;border:1px solid #ccc;border-radius:6px;}
    .modal-footer{display:flex;justify-content:flex-end;gap:10px;}
    .btn-close{background:#6c757d;color:#fff;}
    .btn-update{background:#28a745;color:#fff;}
    
    /* Form Controls */
    .form-control{margin-bottom:12px;}
    .form-control label{display:block;margin-bottom:4px;font-weight:500;}
    
    /* Feature Section */
    .feature-section{margin-top:30px;padding-top:20px;border-top:1px solid #e0e0e0;}
    .feature-section h2{margin-bottom:15px;color:#333;}
    .form-row{display:flex;gap:12px;align-items:end;margin-bottom:15px;}
    .form-row .form-control{flex:1;}
    .empty-row{text-align:center;padding:20px;color:var(--gray);font-style:italic;}
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
      <li><a href="7systemsensor.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="8pagefeature.php" class="active"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-layer-group"></i> Pages & Features Management</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <div class="breadcrumb">Home / <span>Pages & Features Management</span></div>

      <div class="warning">⚠️ Developer Only: Use this page carefully. Editing may cause system issues.</div>

      <div class="actions">
        <form method="get">
          <input type="text" name="q" placeholder="Search pages..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <button class="btn btn-blue" onclick="openDiscover()"><i class="fa fa-search"></i> AUTO-DISCOVER</button>
        <button class="btn btn-add" onclick="openAdd()"><i class="fa fa-plus"></i> ADD PAGE</button>
      </div>

      <!-- Pages Table -->
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>URL</th>
            <th>Description</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($pages && $pages->num_rows): ?>
          <?php while($row = $pages->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><span class="muted"><?= htmlspecialchars($row['url']) ?></span></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><?= date("M d, Y", strtotime($row['created_at'])) ?></td>
              <td class="actions-cell">
                <button class="iconbtn icon-edit" onclick="openEdit(<?= (int)$row['id'] ?>,'<?= htmlspecialchars(addslashes($row['name'])) ?>','<?= htmlspecialchars(addslashes($row['url'])) ?>','<?= htmlspecialchars(addslashes($row['description'])) ?>')">
                  <i class="fa fa-pen"></i>
                </button>
                <a class="iconbtn icon-del" href="?delete=<?= (int)$row['id'] ?>" onclick="return confirm('Delete this page?')">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5" class="empty-row">No pages found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>

      <!-- Page Features Section -->
      <div class="feature-section">
        <h2>Page Features</h2>

        <form method="get" class="form-row">
          <div class="form-control">
            <label for="pageSelect">Select Page:</label>
            <select id="pageSelect" name="page_id">
              <option value="">Choose a page...</option>
              <?php 
              $all_pages = $conn->query("SELECT id, name FROM pages ORDER BY name");
              if ($all_pages && $all_pages->num_rows): 
                while($page = $all_pages->fetch_assoc()): 
              ?>
                <option value="<?= htmlspecialchars($page['id']) ?>" <?= ($selected_page_id == $page['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($page['name']) ?>
                </option>
              <?php 
                endwhile; 
              endif; 
              ?>
            </select>
          </div>
          <button type="submit" class="btn btn-add">View Features</button>
          <button type="button" class="btn btn-add"><i class="fa fa-plus"></i> ADD FEATURE</button>
        </form>

        <table>
          <thead>
            <tr>
              <th>Feature Name</th>
              <th>Feature Type</th>
              <th>Description</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($selected_page_id && $features && $features->num_rows): ?>
              <?php while($feature = $features->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($feature['feature_name']) ?></td>
                  <td><?= htmlspecialchars($feature['feature_type']) ?></td>
                  <td><?= htmlspecialchars($feature['description']) ?></td>
                  <td class="actions-cell">
                    <button class="iconbtn icon-edit"><i class="fa fa-pen"></i></button>
                    <button class="iconbtn icon-del"><i class="fa fa-trash"></i></button>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="empty-row">
                  <?= $selected_page_id ? 'No features found for this page' : 'Select a page to view its features' ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ADD PAGE MODAL -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <h2>Add Page</h2>
      <form method="post">
        <input type="hidden" name="action" value="add_page" />
        <div class="form-control">
          <label>Name</label>
          <select name="name" id="pageName" required>
            <option value="">-- Select Page --</option>
            <?php foreach($defaultDescs as $key=>$desc): ?>
              <option value="<?= htmlspecialchars(ucwords($key)) ?>"><?= htmlspecialchars(ucwords($key)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-control">
          <label>URL</label>
          <input id="pageUrl" name="url" placeholder="dashboard" required />
        </div>
        <div class="form-control">
          <label>Description</label>
          <textarea id="pageDesc" name="description" rows="3" placeholder="Describe this page..."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('addModal')">Cancel</button>
          <button type="submit" class="btn btn-update">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT PAGE MODAL -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2>Edit Page</h2>
      <form method="post">
        <input type="hidden" name="action" value="edit_page" />
        <input type="hidden" name="id" id="editId" />
        <div class="form-control">
          <label>Name</label>
          <input id="editName" name="name" required />
        </div>
        <div class="form-control">
          <label>URL</label>
          <input id="editUrl" name="url" required />
        </div>
        <div class="form-control">
          <label>Description</label>
          <textarea id="editDesc" name="description" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('editModal')">Cancel</button>
          <button type="submit" class="btn btn-update">Update</button>
        </div>
      </form>
    </div>
  </div>

  <!-- AUTO-DISCOVER MODAL -->
  <div id="discoverModal" class="modal">
    <div class="modal-content">
      <h2>Auto-Discover Pages?</h2>
      <form method="post">
        <input type="hidden" name="action" value="auto_discover" />
        <p>This will scan the directory and add new PHP files as pages.</p>
        <div class="modal-footer">
          <button type="button" class="btn btn-close" onclick="closeModal('discoverModal')">Cancel</button>
          <button type="submit" class="btn btn-update">Yes, discover pages</button>
        </div>
      </form>
    </div>
  </div>

  <?php if (!empty($auto_discover_result)): ?>
  <!-- RESULT MODAL -->
  <div class="modal" style="display:flex">
    <div class="modal-content">
      <h2>Pages Discovered</h2>
      <p>Discovery completed!</p>
      <ul>
        <li><b>Created:</b> <?= (int)$auto_discover_result['created'] ?></li>
        <li><b>Skipped:</b> <?= (int)$auto_discover_result['skipped'] ?></li>
        <li><?= htmlspecialchars(implode(" • ", $auto_discover_result['found'])) ?></li>
      </ul>
      <div class="modal-footer">
        <a href="8pagefeature.php" class="btn btn-update">OK</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

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

function openAdd(){ document.getElementById('addModal').style.display='flex'; }
function openDiscover(){ document.getElementById('discoverModal').style.display='flex'; }
function closeAdd(){ document.getElementById('addModal').style.display='none'; }
function closeDiscover(){ document.getElementById('discoverModal').style.display='none'; }

function openEdit(id, name, url, desc){
  document.getElementById('editId').value = id;
  document.getElementById('editName').value = name;
  document.getElementById('editUrl').value = url;
  document.getElementById('editDesc').value = desc;
  document.getElementById('editModal').style.display='flex';
}
function closeEdit(){ document.getElementById('editModal').style.display='none'; }

// Auto-fill URL & Description based on Name for ADD
const defaultDescs = <?php echo json_encode($defaultDescs, JSON_UNESCAPED_UNICODE); ?>;
document.getElementById('pageName').addEventListener('change', function(){
  let val = this.value.toLowerCase();
  let url = val.replace(/\s+/g, "_");
  document.getElementById('pageUrl').value = url;
  document.getElementById('pageDesc').value = defaultDescs[val] || "";
});
</script>

</body>
</html>