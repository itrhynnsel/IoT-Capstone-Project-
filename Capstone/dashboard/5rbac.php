<?php
// rbac.php
require_once '5config.php';

function rbac_bootstrap(mysqli $conn){
  // roles
  $conn->query("CREATE TABLE IF NOT EXISTS roles(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    is_developer TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )");

  // features catalog
  $conn->query("CREATE TABLE IF NOT EXISTS features(
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(80) NOT NULL,
    code VARCHAR(120) NOT NULL UNIQUE,
    label VARCHAR(120) NOT NULL,
    sort_order INT DEFAULT 0
  )");

  // matrix (role → feature allowed)
  $conn->query("CREATE TABLE IF NOT EXISTS role_features(
    role_id INT NOT NULL,
    feature_id INT NOT NULL,
    allowed TINYINT(1) DEFAULT 0,
    PRIMARY KEY(role_id, feature_id)
  )");

  // users (simple)
  $conn->query("CREATE TABLE IF NOT EXISTS users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // per-user overrides
  $conn->query("CREATE TABLE IF NOT EXISTS user_restrictions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    feature_id INT NOT NULL,
    restricted TINYINT(1) DEFAULT 0,
    UNIQUE KEY uq_user_feat(user_id, feature_id)
  )");

  // seed roles if empty
  $rc = $conn->query("SELECT COUNT(*) c FROM roles")->fetch_assoc()['c'] ?? 0;
  if ((int)$rc===0){
    $conn->query("INSERT INTO roles(name,description,is_developer) VALUES
     ('Admin','Full CRUD on devices, users, thresholds, and settings.',0),
     ('Developer','Special access to modify process of the system.',1),
     ('Super Admin','Full access to the system.',0),
     ('Super Visors','View live/historical data and generate reports; cannot modify device settings.',0),
     ('Technician','View live/historical data, acknowledge alerts, but cannot modify system configuration.',0)");
  }

  // seed features
  $fc = $conn->query("SELECT COUNT(*) c FROM features")->fetch_assoc()['c'] ?? 0;
  if ((int)$fc===0){
    $ins = $conn->prepare("INSERT INTO features(group_name,code,label,sort_order) VALUES(?,?,?,?)");
    $rows = [
      ['Archives','archives.select_page','Select Page',0],
      ['Archives','archives.purge_users','Purge Users',1],
      ['Sensors','sensors.add','Add Sensor',0],
      ['Sensors','sensors.delete','Delete Sensor',1],
      ['Sensors','sensors.edit','Edit Sensor',2],
      ['Sensors','sensors.view_all','View All Sensors',3],
    ];
    foreach($rows as $r){ $ins->bind_param("sssi",$r[0],$r[1],$r[2],$r[3]); $ins->execute(); }
  }

  // ensure role_features coverage
  $roles = $conn->query("SELECT id FROM roles")->fetch_all(MYSQLI_ASSOC);
  $feats = $conn->query("SELECT id FROM features")->fetch_all(MYSQLI_ASSOC);
  $ins = $conn->prepare("INSERT IGNORE INTO role_features(role_id,feature_id,allowed) VALUES(?,?,0)");
  foreach($roles as $r){ foreach($feats as $f){ $ins->bind_param("ii",$r['id'],$f['id']); $ins->execute(); } }

  // seed users if empty (passwords are NULL; not used here)
  $uc = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0;
  if ((int)$uc===0){
    $ids = []; $rs = $conn->query("SELECT id,name FROM roles");
    while($r=$rs->fetch_assoc()){ $ids[$r['name']] = (int)$r['id']; }
    $ins = $conn->prepare("INSERT INTO users(full_name,username,role_id) VALUES (?,?,?)");
    $data = [
      ['Carla F. Shields Daria Roberts','qodixupany',$ids['Admin']],
      ['Deirdre A. Best Mark Fuller','hohokomy',$ids['Super Admin']],
      ['Juan D. Cruz Jr.','dev.juan',$ids['Developer']],
    ];
    foreach($data as $u){ $ins->bind_param("ssi",$u[0],$u[1],$u[2]); $ins->execute(); }
  }
}
rbac_bootstrap($conn);

// returns true/false if user can access a feature code
function can($userId, $featureCode){
  global $conn;
  $userId = (int)$userId;
  $code = $conn->real_escape_string($featureCode);

  // fetch role + feature id
  $sql = "SELECT u.role_id r, f.id fid
          FROM users u, features f
          WHERE u.id=$userId AND f.code='$code' LIMIT 1";
  $row = $conn->query($sql)->fetch_assoc();
  if (!$row) return false;
  $roleId = (int)$row['r']; $fid = (int)$row['fid'];

  // role permission
  $rp = $conn->query("SELECT allowed FROM role_features WHERE role_id=$roleId AND feature_id=$fid")
             ->fetch_assoc()['allowed'] ?? 0;

  if (!$rp) return false; // role already denies

  // user restriction (override)
  $ur = $conn->query("SELECT restricted FROM user_restrictions WHERE user_id=$userId AND feature_id=$fid")
             ->fetch_assoc()['restricted'] ?? 0;

  return $rp && !$ur;
}
