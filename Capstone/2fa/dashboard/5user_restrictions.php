<?php
include '5config.php'; require_once '5rbac.php';
date_default_timezone_set('Asia/Manila');
$nowDate = date('l, F j, Y'); $nowTime = date('h:i A');

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_restrictions'])){
  $payload = json_decode($_POST['save_restrictions'], true);
  $uid = (int)($payload['user_id']??0); $rows = $payload['rows']??[];
  $ins = $conn->prepare("INSERT INTO user_restrictions(user_id,feature_id,restricted) VALUES(?,?,?)
                         ON DUPLICATE KEY UPDATE restricted=VALUES(restricted)");
  foreach($rows as $r){ $fid=(int)$r['feature_id']; $res=(int)$r['restricted']; $ins->bind_param("iii",$uid,$fid,$res); $ins->execute(); }
  header('Content-Type: application/json'); echo json_encode(['ok'=>true]); exit;
}

$users = $conn->query("SELECT u.id, u.full_name, u.username, r.id role_id, r.name role_name
                       FROM users u JOIN roles r ON r.id=u.role_id
                       ORDER BY u.full_name")->fetch_all(MYSQLI_ASSOC);
$uid = isset($_GET['user'])?(int)$_GET['user']:0;

$features = $conn->query("SELECT * FROM features ORDER BY group_name, sort_order, id")->fetch_all(MYSQLI_ASSOC);

$meta = $rows = null; $byGroup = [];
if ($uid){
  $meta = $conn->query("SELECT u.id,u.full_name,u.username,r.id role_id, r.name role_name
                        FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=$uid")->fetch_assoc();
  $perm = []; $rf=$conn->query("SELECT feature_id,allowed FROM role_features WHERE role_id=".(int)$meta['role_id']);
  while($p=$rf->fetch_assoc()){ $perm[(int)$p['feature_id']] = (int)$p['allowed']; }
  $restr = []; $ur=$conn->query("SELECT feature_id, restricted FROM user_restrictions WHERE user_id=$uid");
  while($u=$ur->fetch_assoc()){ $restr[(int)$u['feature_id']] = (int)$u['restricted']; }
  foreach($features as $f){
    $fid=(int)$f['id']; $role=(int)($perm[$fid]??0); $res=(int)($restr[$fid]??0);
    $rows[]=['group'=>$f['group_name'],'feature_id'=>$fid,'label'=>$f['label'],
             'role_allowed'=>$role,'restricted'=>$res,'final_allowed'=> ($role?(!$res):0)];
  }
  foreach($rows as $r){ $byGroup[$r['group']][]=$r; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Restrictions</title>
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

    /* Sidebar - Exactly matching role_matrix.php */
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
    
    .search-box input, .search-box select {
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

    /* User info bar */
    .user-info {
      background: #fff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .user-info strong {
      font-size: 16px;
    }
    
    .muted {
      color: var(--gray);
    }

    /* Grid layout */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    /* Card */
    .card {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
    
    .card-header .actions {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 0;
    }
    
    .chip {
      background: #e9ecef;
      border-radius: 12px;
      padding: 4px 10px;
      font-size: 12px;
      color: var(--gray);
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
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
    
    td:last-child, th:last-child {
      text-align: center;
    }

    /* Status badges */
    .status {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }
    
    .ok {
      background: #eaf7ec;
      color: var(--primary);
    }
    
    .no {
      background: #fde8e8;
      color: var(--danger);
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
    
    .no-data {
      text-align: center;
      padding: 40px 20px;
      color: var(--gray);
    }
    
    .no-data i {
      font-size: 48px;
      margin-bottom: 15px;
      color: #e9ecef;
    }
  </style>
</head>
<body>
  <!-- Sidebar - Exactly matching role_matrix.php -->
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
      <div><i class="fa fa-user-lock"></i> User Restrictions</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </div>

    <div class="container">
      <!-- Tabs -->
      <div class="tabs">
        <a class="tab" href="5role.php"><i class="fa fa-id-badge"></i> Roles Management</a>
        <a class="tab" href="5role_matrix.php"><i class="fa fa-table"></i> Role-Feature Matrix</a>
        <a class="tab active" href="5user_restrictions.php"><i class="fa fa-user-lock"></i> User Restrictions</a>
      </div>

      <!-- Actions -->
      <div class="actions">
        <div class="search-box">
          <i class="fas fa-user"></i>
          <select id="userSelect" onchange="onUserChange(this.value)">
            <option value="">Select a user...</option>
            <?php foreach($users as $u): ?>
              <option value="<?php echo (int)$u['id']; ?>" <?php echo $uid==$u['id']?'selected':''; ?>>
                <?php echo htmlspecialchars($u['full_name']); ?> (@<?php echo htmlspecialchars($u['username']); ?>) - <?php echo htmlspecialchars($u['role_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input id="q" placeholder="Search features...">
        </div>
        
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-save" id="btnSave"><i class="fa fa-floppy-disk"></i> SAVE</button>
          <button class="btn btn-reset" id="btnReset"><i class="fa fa-rotate"></i> RESET</button>
        </div>
      </div>

      <!-- User info bar -->
      <div class="user-info">
        <strong><i class="fa fa-user"></i> <?php echo $meta?htmlspecialchars($meta['full_name']):'No user selected'; ?></strong>
        <?php if($meta): ?>
          &nbsp; <span class="muted">Role:</span> <?php echo htmlspecialchars($meta['role_name']); ?>
          &nbsp; <span class="muted">User ID:</span> <?php echo (int)$meta['id']; ?>
        <?php endif; ?>
      </div>

      <?php if($meta): ?>
        <!-- Grid with columns & rows for easier scanning -->
        <div class="grid">
          <?php foreach($byGroup as $g=>$items): ?>
            <div class="card">
              <div class="card-header">
                <i class="fa fa-folder-open" style="color:#28a745"></i>
                <span><?php echo htmlspecialchars($g); ?></span>
                <span class="chip"><?php echo count($items); ?> features</span>
                <div class="actions">
                  <span class="muted">All</span>
                  <label class="sw"><input type="checkbox" class="group-all" data-group="<?php echo htmlspecialchars($g); ?>"><span class="knob"></span></label>
                </div>
              </div>
              <table>
                <thead>
                  <tr>
                    <th>Feature</th>
                    <th>Role Permission</th>
                    <th>User Restriction</th>
                    <th>Final Access</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($items as $it): ?>
                  <tr class="feat-row" data-group="<?php echo htmlspecialchars($g); ?>" data-label="<?php echo htmlspecialchars(strtolower($it['label'])); ?>">
                    <td><?php echo htmlspecialchars($it['label']); ?></td>
                    <td><?php echo $it['role_allowed']?'<span class="status ok"><i class="fa fa-check"></i> Granted</span>':'<span class="status no"><i class="fa fa-xmark"></i> Denied</span>'; ?></td>
                    <td>
                      <label class="sw"><input type="checkbox" class="restrict" data-feature-id="<?php echo (int)$it['feature_id']; ?>" <?php echo $it['restricted']?'checked':''; ?>><span class="knob"></span></label>
                    </td>
                    <td class="final">
                      <?php echo $it['final_allowed']?'<span class="status ok"><i class="fa fa-check"></i> Allowed</span>':'<span class="status no"><i class="fa fa-xmark"></i> Blocked</span>'; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="no-data">
          <i class="fa fa-user-lock"></i>
          <p>Select a user above to manage their feature restrictions</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
  const $=(s,c=document)=>c.querySelector(s), $$=(s,c=document)=>Array.from(c.querySelectorAll(s));
  function onUserChange(id){ location.href='5user_restrictions.php'+(id?('?user='+id):''); }
  $('#q').addEventListener('input',e=>{
    const k=e.target.value.trim().toLowerCase();
    $$('.feat-row').forEach(tr=>tr.classList.toggle('hidden', k && !tr.dataset.label.includes(k)));
  });
  $$('.group-all').forEach(sw=>{
    sw.addEventListener('change',e=>{
      const g=e.target.dataset.group;
      $$(`.feat-row[data-group="${CSS.escape(g)}"] .restrict`).forEach(cb=>{cb.checked=e.target.checked; updateFinal(cb);});
    });
  });
  function updateFinal(cb){
    const row=cb.closest('tr'); const roleGranted=row.children[1].textContent.toLowerCase().includes('granted');
    const restricted=cb.checked; const allowed= roleGranted ? (!restricted) : false;
    row.querySelector('.final').innerHTML = allowed ? '<span class="status ok"><i class="fa fa-check"></i> Allowed</span>' : '<span class="status no"><i class="fa fa-xmark"></i> Blocked</span>';
  }
  $$('.restrict').forEach(cb=>cb.addEventListener('change',()=>updateFinal(cb)));
  $('#btnReset').addEventListener('click',()=>location.reload());
  $('#btnSave').addEventListener('click',async ()=>{
    const uid=<?php echo (int)$uid; ?>; if(!uid){alert('Select a user first.');return;}
    const rows=$$('.restrict').map(cb=>({feature_id:+cb.dataset.featureId, restricted: cb.checked?1:0}));
    const fd=new FormData(); fd.append('save_restrictions', JSON.stringify({user_id:uid, rows}));
    const r=await fetch('5user_restrictions.php',{method:'POST',body:fd}); const j=await r.json().catch(()=>({ok:false}));
    alert(j.ok?'User restrictions saved.':'Save failed.');
  });
  </script>
</body>
</html>