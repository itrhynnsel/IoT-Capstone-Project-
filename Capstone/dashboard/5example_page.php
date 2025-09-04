<?php
require_once '5rbac.php';
session_start();
// For demo, pretend logged in user is ID 3 (dev.juan). Replace with your real auth.
$_SESSION['user_id'] = 3;
$me = $_SESSION['user_id'];

$canAddSensor   = can($me, 'sensors.add');
$canPurgeUsers  = can($me, 'archives.purge_users');
?>
<!doctype html><html><head><meta charset="utf-8"><title>Example</title></head><body>
<h3>Example Feature Buttons</h3>
<?php if ($canAddSensor): ?>
  <button>Add Sensor</button>
<?php else: ?>
  <em>No permission: sensors.add</em>
<?php endif; ?>
<br>
<?php if ($canPurgeUsers): ?>
  <button style="color:#fff;background:#dc3545">Purge Users</button>
<?php else: ?>
  <em>No permission: archives.purge_users</em>
<?php endif; ?>
</body></html>

