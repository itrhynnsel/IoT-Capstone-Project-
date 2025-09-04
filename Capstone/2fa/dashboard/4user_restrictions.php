<?php
// user_restrictions.php
include '4config.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y');
$nowTime = date('h:i A');

/* ─────────── Ensure same schema as matrix ─────────── */
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
$conn->query("CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  role_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS user_restrictions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  feature_id INT NOT NULL,
  restricted TINYINT(1) DEFAULT 0,
  UNIQUE KEY uq_user_feat (user_id, feature_id)
)");

/* Seed users (only if none found) */
$ucnt = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0;
if ((int)$ucnt === 0) {
  // Get some role ids
  $roles = $conn->query("SELECT id, name FROM roles")->fetch_all(MYSQLI_ASSOC);
  $rid = [];
  foreach($roles as $r){ $rid[$r['name']] = (int)$r['id']; }
  $stmt = $conn->prepare("INSERT INTO users(full_name, username, role_id) VALUES (?,?,?)");
  $data = [
    ['Carla F. Shields Daria Roberts','qodixupany', $rid['Admin'] ?? 1],
    ['Deirdre A. Best Mark Fuller','hohokomy', $rid['Super Admin'] ?? 3],
    ['Juan D. Cruz Jr.','dev.juan', $rid['Developer'] ?? 2],
  ];
  foreach($data as $u){ $stmt->bind_param("ssi",$u[0],$u[1],$u[2]); $stmt->execute(); }
}

/* SAVE (AJAX) */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_restrictions'])) {
  $payload = json_decode($_POST['save_restrictions'], true);
  $userId = (int)($payload['user_id'] ?? 0);
  $rows   = $payload['rows'] ?? [];
  if ($userId>0 && is_array($rows)) {
    $ins = $conn->prepare("INSERT INTO user_restrictions(user_id, feature_id, restricted) VALUES (?,?,?)
                           ON DUPLICATE KEY UPDATE restricted=VALUES(restricted)");
    foreach ($rows as $row) {
      $fid = (int)$row['feature_id'];
      $restr = (int)$row['restricted'];
      $ins->bind_param("iii",$userId,$fid,$restr);
      $ins->execute();
    }
    header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
  }
  header('Content-Type: application/json'); echo json_encode(['ok'=>false]); exit;
}

/* Data for dropdown & table */
$users = $conn->query("SELECT u.id, u.full_name, u.username, r.name role_name
                       FROM users u JOIN roles r ON r.id=u.role_id
                       ORDER BY u.full_name")->fetch_all(MYSQLI_ASSOC);

$selected_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;

$features = $conn->query("SELECT * FROM features ORDER BY group_name, sort_order, id")->fetch_all(MYSQLI_ASSOC);

/* If someone is selected, fetch role permission + current restrictions */
$rows = [];
$userMeta = null;
if ($selected_user_id) {
  $userMeta = $conn->query("SELECT u.*, r.name role_name, r.id role_id
                            FROM users u JOIN roles r ON r.id=u.role_id
                            WHERE u.id={$selected_user_id}")->fetch_assoc();

  if ($userMeta) {
    $role_id = (int)$userMeta['role_id'];

    // map role permissions
    $perm = [];
    $rf = $conn->query("SELECT feature_id, allowed FROM role_features WHERE role_id={$role_id}");
    while($p = $rf->fetch_assoc()){ $perm[(int)$p['feature_id']] = (int)$p['allowed']; }

    // map user restrictions
    $ures = [];
    $ur = $conn->query("SELECT feature_id, restricted FROM user_restrictions WHERE user_id={$selected_user_id}");
    while($r = $ur->fetch_assoc()){ $ures[(int)$r['feature_id']] = (int)$r['restricted']; }

    // build rows per feature with group split
    foreach ($features as $f) {
      $fid = (int)$f['id'];
      $roleAllowed = (int)($perm[$fid] ?? 0);
      $restricted  = (int)($ures[$fid] ?? 0);
      $final = $roleAllowed ? ( $restricted ? 0 : 1 ) : 0; // restriction overrides role
      $rows[] = [
        'group' => $f['group_name'],
        'feature_id' => $fid,
        'label' => $f['label'],
        'role_allowed' => $roleAllowed,
        'restricted' => $restricted,
        'final_allowed' => $final
      ];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Restrictions</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
  :root{ --primary:#28a745; --muted:#6c757d; --chip:#e9ecef; --ok:#198754; --no:#dc3545; }
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
  .select{flex:0 0 420px;background:#fff;border:2px solid #1b8f42;border-radius:10px;padding:8px}
  select{width:100%;border:none;outline:none;font-size:14px;background:transparent;padding:6px}
  .search{flex:1;display:flex;align-items:center;gap:8px;background:#fff;border:1px solid #dfe3e8;border-radius:10px;padding:10px 12px}
  .search input{border:none;outline:none;width:100%;font-size:14px}
  .btn{border:none;border-radius:10px;padding:10px 16px;cursor:pointer;font-weight:600}
  .btn.save{background:#1e7e34;color:#fff}
  .btn.reset{background:#6c757d;color:#fff}
  .note{background:#fff3cd;border:1px solid #ffe08a;border-radius:10px;padding:10px 12px;font-size:14px;margin:6px 0 14px}

  .card{background:#fff;border:1px solid #e6e9ec;border-radius:12px;margin:16px 0;overflow:hidden}
  .card-header{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #eef1f3;background:#fbfcfd}
  .chip{background:#e9ecef;border-radius:999px;padding:4px 8px;font-size:12px}
  table{width:100%;border-collapse:collapse}
  th,td{border-bottom:1px solid #f0f2f4;padding:10px;text-align:center;font-size:14px}
  th:first-child, td:first-child{text-align:left}
  .status{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:4px 10px;font-size:12px}
  .ok{background:#e7f6ed;color:#0f5132}
  .no{background:#fde2e1;color:#842029}

  /* Switch */
  .sw{position:relative;display:inline-block;width:44px;height:24px}
  .sw input{display:none}
  .knob{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#dee2e6;border-radius:999px;transition:.2s}
  .knob:before{content:"";position:absolute;height:18px;width:18px;left:3px;top:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.2)}
  .sw input:checked + .knob{background:#34c759}
  .sw input:checked + .knob:before{transform:translateX(20px)}
  .hidden{display:none !important}
  .userbar{background:#fff;border:1px solid #e6e9ec;border-radius:12px;padding:12px 14px;margin-top:6px}
  .muted{color:#6c757d}
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
      <a class="tab" href="4role_matrix.php"><i class="fa fa-table"></i> ROLE-FEATURE MATRIX</a>
      <a class="tab active" href="4user_restrictions.php"><i class="fa fa-user-lock"></i> USER RESTRICTIONS</a>
    </div>

    <div class="controls">
      <div class="select">
        <select id="userSelect" onchange="onUserChange(this.value)">
          <option value="">Select a user...</option>
          <?php foreach($users as $u): ?>
            <option value="<?php echo (int)$u['id']; ?>" <?php echo $selected_user_id==$u['id']?'selected':''; ?>>
              <?php echo htmlspecialchars($u['full_name']); ?> (@<?php echo htmlspecialchars($u['username']); ?>) - <?php echo htmlspecialchars($u['role_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="search">
        <i class="fa fa-search"></i>
        <input id="q" type="text" placeholder="Search features...">
      </div>
      <button class="btn save" id="btnSave"><i class="fa fa-floppy-disk"></i> SAVE CHANGES</button>
      <button class="btn reset" id="btnReset"><i class="fa fa-rotate"></i> RESET</button>
    </div>

    <div class="note">
      <strong>User Restrictions:</strong> Select a user to manage their feature restrictions. Restrictions override role permissions (cascading RBAC).
    </div>

    <?php if(!$selected_user_id): ?>
      <div class="userbar" style="text-align:center;color:#6c757d">
        <i class="fa fa-user-lock" style="font-size:42px;opacity:.25"></i>
        <div>Select a user above to manage their feature restrictions</div>
      </div>
    <?php else: ?>
      <div class="userbar">
        <strong><i class="fa fa-user"></i> <?php echo htmlspecialchars($userMeta['full_name']); ?></strong>
        &nbsp; <span class="muted">Role:</span> <?php echo htmlspecialchars($userMeta['role_name']); ?>
        &nbsp; <span class="muted">User ID:</span> <?php echo htmlspecialchars($userMeta['id']); ?>
      </div>

      <?php
        // Group rows for rendering like the screenshots
        $byGroup = [];
        foreach($rows as $r){ $byGroup[$r['group']][] = $r; }
      ?>

      <?php foreach($byGroup as $gname => $items): ?>
        <div class="card">
          <div class="card-header">
            <i class="fa fa-folder-open" style="color:#2c974b;"></i>
            <strong><?php echo htmlspecialchars($gname); ?></strong>
            <span class="chip"><?php echo count($items); ?> available features</span>
            <div style="margin-left:auto;display:flex;gap:10px;align-items:center">
              <span class="muted">All</span>
              <label class="sw">
                <input type="checkbox" class="group-all" data-group="<?php echo htmlspecialchars($gname); ?>">
                <span class="knob"></span>
              </label>
            </div>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th style="min-width:260px">Feature</th>
                  <th>Role Permission</th>
                  <th>User Restriction</th>
                  <th>Final Access</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($items as $it): ?>
                  <tr class="feat-row" data-group="<?php echo htmlspecialchars($gname); ?>" data-label="<?php echo htmlspecialchars(strtolower($it['label'])); ?>">
                    <td><?php echo htmlspecialchars($it['label']); ?></td>
                    <td><?php echo $it['role_allowed'] ? '<span class="status ok"><i class="fa fa-check"></i> Granted</span>' : '<span class="status no"><i class="fa fa-xmark"></i> Denied</span>'; ?></td>
                    <td>
                      <label class="sw">
                        <input type="checkbox" class="restrict" data-feature-id="<?php echo (int)$it['feature_id']; ?>" <?php echo $it['restricted'] ? 'checked':''; ?>>
                        <span class="knob"></span>
                      </label>
                      <div class="muted" style="font-size:12px;margin-top:4px">All</div>
                    </td>
                    <td class="final">
                      <?php echo $it['final_allowed'] ? '<span class="status ok"><i class="fa fa-check"></i> Allowed</span>' : '<span class="status no"><i class="fa fa-xmark"></i> Blocked</span>'; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

<script>
  const $ = (s, c=document)=>c.querySelector(s);
  const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));

  function onUserChange(id){ location.href = '4user_restrictions.php' + (id?('?user='+id):''); }

  // Search filter
  const q = $('#q');
  if (q) q.addEventListener('input', e=>{
    const term = e.target.value.trim().toLowerCase();
    $$('.feat-row').forEach(tr=>{
      tr.classList.toggle('hidden', term && !tr.dataset.label.includes(term));
    });
  });

  // Group "All" toggles (turn ON = set all restriction switches ON for that group)
  $$('.group-all').forEach(sw=>{
    sw.addEventListener('change', e=>{
      const group = e.target.dataset.group;
      $$(`.feat-row[data-group="${CSS.escape(group)}"] .restrict`).forEach(cb=>{
        cb.checked = e.target.checked;
        updateFinal(cb);
      });
    });
  });

  // Update "Final Access" cell live when user toggles
  function updateFinal(cb){
    const row = cb.closest('tr');
    const roleGranted = row.children[1].textContent.toLowerCase().includes('granted');
    const restricted = cb.checked;
    const allowed = roleGranted ? (!restricted) : false;
    row.querySelector('.final').innerHTML = allowed
      ? '<span class="status ok"><i class="fa fa-check"></i> Allowed</span>'
      : '<span class="status no"><i class="fa fa-xmark"></i> Blocked</span>';
  }
  $$('.restrict').forEach(cb=>cb.addEventListener('change',()=>updateFinal(cb)));

  // RESET
  const br = $('#btnReset'); if (br) br.addEventListener('click', ()=>location.reload());

  // SAVE
  const bs = $('#btnSave');
  if (bs) bs.addEventListener('click', async ()=>{
    const userId = <?php echo (int)$selected_user_id; ?>;
    if (!userId){ alert('Please select a user first.'); return; }
    const rows = $$('.restrict').map(cb=>({
      feature_id: parseInt(cb.dataset.featureId),
      restricted: cb.checked ? 1 : 0
    }));
    const form = new FormData();
    form.append('save_restrictions', JSON.stringify({user_id:userId, rows}));
    const r = await fetch('4user_restrictions.php', {method:'POST', body:form});
    const j = await r.json().catch(()=>({ok:false}));
    alert(j.ok ? 'User restrictions saved.' : 'Save failed.');
  });
</script>
</body>
</html>
