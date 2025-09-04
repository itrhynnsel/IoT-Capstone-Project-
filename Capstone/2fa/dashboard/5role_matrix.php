<?php
include '5config.php';
require_once '5rbac.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y'); 
$nowTime = date('h:i A');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_matrix'])) {
  $payload = json_decode($_POST['save_matrix'], true);
  $upd = $conn->prepare("UPDATE role_features SET allowed=? WHERE role_id=? AND feature_id=?");
  foreach($payload as $r){ 
    $a=(int)$r['allowed']; 
    $rid=(int)$r['role_id']; 
    $fid=(int)$r['feature_id']; 
    $upd->bind_param("iii",$a,$rid,$fid); 
    $upd->execute(); 
  }
  header('Content-Type: application/json'); 
  echo json_encode(['ok'=>true]); 
  exit;
}

$roles = $conn->query("SELECT * FROM roles ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$feats = $conn->query("SELECT * FROM features ORDER BY group_name, sort_order, id")->fetch_all(MYSQLI_ASSOC);

$allowed = [];
$res = $conn->query("SELECT role_id,feature_id,allowed FROM role_features");
while($r=$res->fetch_assoc()){ 
  $allowed[(int)$r['feature_id']][(int)$r['role_id']] = (int)$r['allowed']; 
}

$groups = [];
foreach($feats as $f){ $groups[$f['group_name']][]=$f; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Role-Feature Matrix</title>
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

    /* Sidebar - Exactly matching role.php */
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

    .btn-save {
      background: var(--primary);
      color: #fff;
    }
    
    .btn-reset {
      background: var(--secondary);
      color: #000;
    }

    /* Cards */
    .card {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    
    .card-header {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      background: #f9fbfc;
      border-bottom: 1px solid #f0f0f0;
      font-weight: 600;
    }
    
    .chip {
      background: #e9ecef;
      border-radius: 12px;
      padding: 4px 10px;
      font-size: 12px;
      color: var(--gray);
    }

    /* Table */
    .table-container {
      overflow-x: auto;
    }
    
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
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
      position: sticky;
      top: 0;
    }

    tr:last-child td {
      border-bottom: none;
    }
    
    td:first-child {
      text-align: left;
      font-weight: 500;
    }

    /* Role header */
    .role-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 5px;
    }
    
    .role-name {
      font-weight: 700;
    }
    
    .dev-badge {
      font-size: 12px;
      color: var(--primary);
      background: #eaf7ec;
      padding: 2px 8px;
      border-radius: 12px;
    }
    
    .all-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #eaf7ec;
      color: var(--primary);
      border-radius: 12px;
      padding: 4px 10px;
      font-size: 12px;
      font-weight: 500;
      margin-top: 5px;
    }

    /* Switch */
    .sw {
      position: relative;
      display: inline-block;
      width: 44px;
      height: 24px;
    }
    
    .sw input {
      display: none;
    }
    
    .knob {
      position: absolute;
      inset: 0;
      background: #dee2e6;
      border-radius: 999px;
      transition: 0.2s;
    }
    
    .knob:before {
      content: "";
      position: absolute;
      height: 18px;
      width: 18px;
      left: 3px;
      top: 3px;
      background: #fff;
      border-radius: 50%;
      transition: 0.2s;
      box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    
    .sw input:checked + .knob {
      background: var(--primary);
    }
    
    .sw input:checked + .knob:before {
      transform: translateX(20px);
    }

    .hidden {
      display: none !important;
    }
  </style>
</head>
<body>
  <!-- Sidebar - Exactly matching role.php -->
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
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="index.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="index.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="topbar">
      <div><i class="fa fa-table"></i> Role-Feature Matrix</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <!-- Tabs -->
      <div class="tabs">
        <a class="tab" href="5role.php"><i class="fa fa-id-badge"></i> Roles Management</a>
        <a class="tab active" href="5role_matrix.php"><i class="fa fa-table"></i> Role-Feature Matrix</a>
        <a class="tab" href="5user_restrictions.php"><i class="fa fa-user-lock"></i> User Restrictions</a>
      </div>

      <!-- Actions -->
      <div class="actions">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input id="q" placeholder="Search features...">
        </div>
        
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-save" id="btnSave"><i class="fa fa-floppy-disk"></i> SAVE CHANGES</button>
          <button class="btn btn-reset" id="btnReset"><i class="fa fa-rotate"></i> RESET</button>
        </div>
      </div>

      <!-- Feature Groups -->
      <?php foreach($groups as $g=>$items): ?>
      <div class="card feature-group" data-group="<?php echo htmlspecialchars($g); ?>">
        <div class="card-header">
          <i class="fa fa-folder-open" style="color:#28a745"></i>
          <span><?php echo htmlspecialchars($g); ?></span>
          <span class="chip"><?php echo count($items); ?> features</span>
        </div>
        
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th style="min-width: 220px">Feature</th>
                <?php foreach($roles as $r): ?>
                  <th>
                    <div class="role-header">
                      <div class="role-name"><?php echo htmlspecialchars($r['name']); ?></div>
                      <?php if((int)$r['is_developer']===1): ?>
                        <span class="dev-badge">Developer</span>
                      <?php endif; ?>
                      <div class="all-pill">
                        All
                        <label class="sw">
                          <input type="checkbox" class="all-role" data-role="<?php echo (int)$r['id']; ?>" data-group="<?php echo htmlspecialchars($g); ?>">
                          <span class="knob"></span>
                        </label>
                      </div>
                    </div>
                  </th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach($items as $f): ?>
                <tr class="feat-row" data-label="<?php echo htmlspecialchars(strtolower($f['label'])); ?>">
                  <td><?php echo htmlspecialchars($f['label']); ?></td>
                  <?php foreach($roles as $r):
                    $rid=(int)$r['id']; $fid=(int)$f['id']; $on = (int)($allowed[$fid][$rid] ?? 0); ?>
                    <td>
                      <label class="sw">
                        <input type="checkbox" class="cell" data-role="<?php echo $rid; ?>" data-feature="<?php echo $fid; ?>" <?php echo $on?'checked':''; ?>>
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
  </div>

  <script>
  const $=(s,c=document)=>c.querySelector(s), $$=(s,c=document)=>Array.from(c.querySelectorAll(s));

  $('#q').addEventListener('input',e=>{
    const k=e.target.value.trim().toLowerCase();
    $$('.feat-row').forEach(tr=>tr.classList.toggle('hidden', k && !tr.dataset.label.includes(k)));
  });

  $$('.all-role').forEach(sw=>{
    sw.addEventListener('change',e=>{
      const role=e.target.dataset.role, group=e.target.dataset.group;
      const card=document.querySelector(`.feature-group[data-group="${CSS.escape(group)}"]`);
      $$(`.cell[data-role="${role}"]`,card).forEach(cb=>cb.checked=e.target.checked);
    });
  });

  $('#btnReset').addEventListener('click',()=>location.reload());

  $('#btnSave').addEventListener('click',async ()=>{
    const payload=$$('.cell').map(cb=>({role_id:+cb.dataset.role, feature_id:+cb.dataset.feature, allowed: cb.checked?1:0}));
    const fd=new FormData(); fd.append('save_matrix', JSON.stringify(payload));
    const r=await fetch('5role_matrix.php',{method:'POST',body:fd}); 
    const j=await r.json().catch(()=>({ok:false}));
    alert(j.ok?'Permissions saved.':'Save failed.');
  });
  </script>
</body>
</html>