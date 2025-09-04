<?php
// role_matrix.php
include '4config.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y');
$nowTime = date('h:i A');

/* ───────────────────────── SCHEMA / SEED ───────────────────────── */
$conn->query("CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NOT NULL,
  is_developer TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS features (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(80) NOT NULL,
  code VARCHAR(120) NOT NULL UNIQUE,
  label VARCHAR(120) NOT NULL,
  sort_order INT DEFAULT 0
)");

$conn->query("CREATE TABLE IF NOT EXISTS role_features (
  role_id INT NOT NULL,
  feature_id INT NOT NULL,
  allowed TINYINT(1) DEFAULT 0,
  PRIMARY KEY (role_id, feature_id)
)");

/* Seed roles if empty (keeps your existing names) */
$rcnt = $conn->query("SELECT COUNT(*) c FROM roles")->fetch_assoc()['c'] ?? 0;
if ((int)$rcnt === 0) {
  $conn->query("INSERT INTO roles(name,description,is_developer) VALUES
   ('Admin','Full CRUD on devices, users, thresholds, and settings.',0),
   ('Developer','Special access to modify process of the system',1),
   ('Super Admin','Full access to the system',0),
   ('Super Visors','View live/historical data and generate reports; cannot modify device settings',0),
   ('Technician','View live/historical data, acknowledge alerts, but cannot modify system configuration',0)");
}

/* Seed features (the groups/rows shown in your screenshots) */
$fcnt = $conn->query("SELECT COUNT(*) c FROM features")->fetch_assoc()['c'] ?? 0;
if ((int)$fcnt === 0) {
  $seed = [
    ['Archives','archives.select_page','Select Page',0],
    ['Archives','archives.purge_users','Purge Users',1],
    ['Sensors','sensors.add','Add Sensor',0],
    ['Sensors','sensors.delete','Delete Sensor',1],
    ['Sensors','sensors.edit','Edit Sensor',2],
    ['Sensors','sensors.view_all','View All Sensors',3],
  ];
  $stmt = $conn->prepare("INSERT INTO features(group_name,code,label,sort_order) VALUES(?,?,?,?)");
  foreach ($seed as $s){ $stmt->bind_param("sssi",$s[0],$s[1],$s[2],$s[3]); $stmt->execute(); }
}

/* Make sure each role has entries for every feature (default 0) */
$roles = $conn->query("SELECT * FROM roles ORDER BY id");
$features = $conn->query("SELECT * FROM features ORDER BY group_name, sort_order, id");

$roleRows = $roles->fetch_all(MYSQLI_ASSOC);
$featRows = $features->fetch_all(MYSQLI_ASSOC);

$ins = $conn->prepare("INSERT IGNORE INTO role_features(role_id, feature_id, allowed) VALUES(?,?,0)");
foreach ($roleRows as $r) {
  foreach ($featRows as $f) { $ins->bind_param("ii",$r['id'],$f['id']); $ins->execute(); }
}

/* ────────────────────── SAVE (AJAX) ────────────────────── */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_matrix'])) {
  // Expected: JSON array of {role_id, feature_id, allowed}
  $payload = json_decode($_POST['save_matrix'], true);
  if (is_array($payload)) {
    $upd = $conn->prepare("UPDATE role_features SET allowed=? WHERE role_id=? AND feature_id=?");
    foreach ($payload as $row) {
      $allowed = (int)($row['allowed'] ?? 0);
      $rid = (int)($row['role_id'] ?? 0);
      $fid = (int)($row['feature_id'] ?? 0);
      $upd->bind_param("iii",$allowed,$rid,$fid);
      $upd->execute();
    }
    header('Content-Type: application/json');
    echo json_encode(['ok'=>true]);
    exit;
  }
  header('Content-Type: application/json');
  echo json_encode(['ok'=>false]);
  exit;
}

/* ────────────────────── FETCH MATRIX ────────────────────── */
$rf = $conn->query("SELECT role_id, feature_id, allowed FROM role_features");
$allowedMap = []; // [$feature_id][$role_id] = 0/1
while($row = $rf->fetch_assoc()){
  $fid = (int)$row['feature_id']; $rid = (int)$row['role_id'];
  if(!isset($allowedMap[$fid])) $allowedMap[$fid] = [];
  $allowedMap[$fid][$rid] = (int)$row['allowed'];
}

/* group → [features…] */
$groups = [];
foreach ($featRows as $f){ $groups[$f['group_name']][] = $f; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Role-Feature Matrix</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
  :root{ --primary:#28a745; --muted:#6c757d; --chip:#e9ecef; --danger:#dc3545; --gray:#f1f3f5; }
  *{box-sizing:border-box;font-family:'Segoe UI',Tahoma,Arial,sans-serif;}
  body{margin:0;background:#f4f6f9;color:#212529;}
  .topbar{background:linear-gradient(180deg,#2ebd59,#249a48);color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;}
  .container{padding:18px;max-width:1200px;margin:0 auto;}
  .breadcrumb{font-size:14px;margin:6px 0 14px}
  .breadcrumb a{color:var(--primary);text-decoration:none}
  .tabs{display:flex;gap:12px;margin:8px 0 18px}
  .tab{padding:12px 18px;background:#fff;border:2px solid #cdebd6;border-radius:10px;display:flex;gap:10px;align-items:center;color:#2f6f3a;text-decoration:none;font-weight:600}
  .tab.active{border-color:#1b8f42;box-shadow:0 0 0 3px #e3f6ea inset}
  .controls{display:flex;gap:12px;align-items:center;margin:10px 0 14px}
  .search{flex:1;display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dfe3e8;border-radius:10px;padding:10px 12px}
  .search input{border:none;outline:none;width:100%;font-size:14px}
  .btn{border:none;border-radius:10px;padding:10px 16px;cursor:pointer;font-weight:600}
  .btn.save{background:#1e7e34;color:#fff}
  .btn.reset{background:#6c757d;color:#fff}
  .note{background:#e9f7ef;border:1px solid #b9e2c6;color:#1f6f3c;border-radius:10px;padding:10px 12px;font-size:14px;display:flex;gap:8px;align-items:center;margin:6px 0 14px}
  .card{background:#fff;border:1px solid #e6e9ec;border-radius:12px;margin:16px 0;overflow:hidden}
  .card-header{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #eef1f3;background:#fbfcfd}
  .chip{background:var(--chip);border-radius:999px;padding:4px 8px;font-size:12px}
  table{width:100%;border-collapse:collapse}
  th,td{border-bottom:1px solid #f0f2f4;padding:10px;text-align:center;font-size:14px}
  th:first-child, td:first-child{text-align:left}
  .role-head{font-weight:700}
  /* Switch */
  .sw{position:relative;display:inline-block;width:44px;height:24px}
  .sw input{display:none}
  .knob{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#dee2e6;border-radius:999px;transition:.2s}
  .knob:before{content:"";position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.2)}
  .sw input:checked + .knob{background:#34c759}
  .sw input:checked + .knob:before{transform:translateX(20px)}
  .all-pill{display:inline-flex;align-items:center;gap:6px;font-size:12px;background:#eaf7ec;color:#1e7e34;border-radius:999px;padding:4px 8px}
  .dev-badge{display:block;font-size:12px;color:#0d6efd}
  .table-wrap{overflow:auto}
  .hidden{display:none !important}
</style>
</head>
<body>
  <div class="topbar">
    <div>SmartTemp SYSTEM</div>
    <div><?php echo htmlspecialchars($nowDate) . " • " . htmlspecialchars($nowTime); ?></div>
  </div>

  <div class="container">
    <div class="breadcrumb"><a href="dashboard.php">Home</a> / <span>Role Management</span></div>

    <div class="tabs">
      <a class="tab" href="4role.php"><i class="fa fa-id-badge"></i> ROLES MANAGEMENT</a>
      <a class="tab active" href="4role_matrix.php"><i class="fa fa-table"></i> ROLE-FEATURE MATRIX</a>
      <a class="tab" href="4user_restrictions.php"><i class="fa fa-user-lock"></i> USER RESTRICTIONS</a>
    </div>

    <div class="controls">
      <div class="search"><i class="fa fa-search"></i>
        <input id="q" type="text" placeholder="Search features...">
      </div>
      <button class="btn save" id="btnSave"><i class="fa fa-floppy-disk"></i> SAVE CHANGES</button>
      <button class="btn reset" id="btnReset"><i class="fa fa-rotate"></i> RESET</button>
    </div>

    <div class="note"><i class="fa fa-circle-info"></i>
      <div><strong>Permission Matrix:</strong> Features are grouped by page. Check the boxes to grant features to roles.</div>
    </div>

    <?php
      // Roles header labels (keep order from DB)
      $roleCols = $roleRows; // already sorted by id
    ?>

    <?php foreach ($groups as $groupName => $groupFeatures): ?>
      <div class="card feature-group" data-group="<?php echo htmlspecialchars($groupName); ?>">
        <div class="card-header">
          <i class="fa fa-folder-open" style="color:#2c974b;"></i>
          <strong><?php echo htmlspecialchars($groupName); ?></strong>
          <span class="chip"><?php echo count($groupFeatures); ?> features</span>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th style="min-width:220px">Feature</th>
                <?php foreach ($roleCols as $role): ?>
                  <th>
                    <div class="role-head"><?php echo htmlspecialchars($role['name']); ?></div>
                    <?php if((int)$role['is_developer']===1): ?>
                      <span class="dev-badge">Developer</span>
                    <?php endif; ?>
                    <span class="all-pill">
                      <span>All</span>
                      <label class="sw">
                        <input type="checkbox" class="all-role" data-role="<?php echo (int)$role['id']; ?>" data-group="<?php echo htmlspecialchars($groupName); ?>">
                        <span class="knob"></span>
                      </label>
                    </span>
                  </th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($groupFeatures as $f): ?>
                <tr class="feat-row" data-feature-id="<?php echo (int)$f['id']; ?>" data-label="<?php echo htmlspecialchars(strtolower($f['label'])); ?>">
                  <td>
                    <?php echo htmlspecialchars($f['label']); ?>
                  </td>
                  <?php foreach ($roleCols as $role): 
                    $rid = (int)$role['id']; $fid = (int)$f['id'];
                    $on = (int)($allowedMap[$fid][$rid] ?? 0);
                  ?>
                    <td>
                      <label class="sw">
                        <input
                          type="checkbox"
                          class="cell"
                          data-role="<?php echo $rid; ?>"
                          data-feature="<?php echo $fid; ?>"
                          <?php echo $on ? 'checked' : ''; ?>
                        >
                        <span class="knob"></span>
                      </label>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<script>
  const $ = (s, c=document)=>c.querySelector(s);
  const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));

  // Search filter
  $('#q').addEventListener('input', e=>{
    const q = e.target.value.trim().toLowerCase();
    $$('.feat-row').forEach(tr=>{
      const label = tr.dataset.label;
      tr.classList.toggle('hidden', q && !label.includes(q));
    });
  });

  // Per-role "All" within each group
  $$('.all-role').forEach(sw=>{
    sw.addEventListener('change', e=>{
      const roleId = e.target.dataset.role;
      const group = e.target.dataset.group;
      const card = document.querySelector(`.card.feature-group[data-group="${CSS.escape(group)}"]`);
      $$(`input.cell[data-role="${roleId}"]`, card).forEach(cb=>cb.checked = e.target.checked);
    });
  });

  // RESET
  $('#btnReset').addEventListener('click', ()=>location.reload());

  // SAVE
  $('#btnSave').addEventListener('click', async ()=>{
    const payload = $$('input.cell').map(cb=>({
      role_id: parseInt(cb.dataset.role),
      feature_id: parseInt(cb.dataset.feature),
      allowed: cb.checked ? 1 : 0
    }));
    const form = new FormData();
    form.append('save_matrix', JSON.stringify(payload));
    const r = await fetch('role_matrix.php', {method:'POST', body:form});
    const j = await r.json().catch(()=>({ok:false}));
    alert(j.ok ? 'Permissions saved.' : 'Save failed.');
  });
</script>
</body>
</html>
