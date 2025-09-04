<?php
include '1config.php';

// Kunin ID ng site mula sa URL
if (!isset($_GET['id'])) {
    header("Location: sites.php");
    exit();
}
$site_id = $_GET['id'];

// Kunin details ng site
$site = $conn->query("SELECT * FROM sites WHERE id = $site_id")->fetch_assoc();

// Kapag walang site
if (!$site) {
    die("Site not found!");
}

// Kung may update request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $owner = $_POST['owner'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];

    $sql = "UPDATE sites SET 
            name='$name', 
            owner='$owner', 
            region='$region', 
            province='$province', 
            city='$city', 
            barangay='$barangay' 
            WHERE id=$site_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: 2sites.php?updated=1");
        exit();
    } else {
        $error = "Error updating record: " . $conn->error;
    }
}

// Kunin lahat ng users para sa owner dropdown
$users = $conn->query("SELECT id, first_name, last_name FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Site - SmartTemp SYSTEM</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; margin:0; padding:0; }
        header { background: #28a745; color:white; padding:15px 30px; font-size:20px; font-weight:bold; }
        .container { width:600px; margin:30px auto; background:white; border-radius:10px; padding:20px 30px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
        h2 { margin-top:0; color:#333; }
        form label { display:block; margin:12px 0 6px; font-weight:bold; }
        form input, form select { width:100%; padding:8px 10px; border:1px solid #ccc; border-radius:6px; }
        .btn-container { margin-top:20px; display:flex; justify-content:space-between; }
        .btn { padding:10px 15px; border:none; border-radius:6px; cursor:pointer; font-size:14px; text-decoration:none; text-align:center; }
        .btn-save { background:#28a745; color:white; }
        .btn-save:hover { background:#218838; }
        .btn-cancel { background:#6c757d; color:white; }
        .btn-cancel:hover { background:#5a6268; }
        .error { color:red; margin-bottom:15px; }
    </style>
</head>
<body>

<header>SmartTemp SYSTEM</header>

<div class="container">
    <h2>Edit Site</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label for="name">Site Name</label>
        <input type="text" name="name" id="name" value="<?= $site['name'] ?>" required>

        <label for="owner">Owner</label>
        <select name="owner" id="owner" required>
            <option value="">-- Select Owner --</option>
            <?php while($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id']; ?>" <?= ($site['owner'] == $u['id']) ? "selected" : "" ?>>
                    <?= $u['first_name']." ".$u['last_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="region">Region</label>
        <input type="text" name="region" id="region" value="<?= $site['region'] ?>" required>

        <label for="province">Province</label>
        <input type="text" name="province" id="province" value="<?= $site['province'] ?>" required>

        <label for="city">City/Municipality</label>
        <input type="text" name="city" id="city" value="<?= $site['city'] ?>" required>

        <label for="barangay">Barangay</label>
        <input type="text" name="barangay" id="barangay" value="<?= $site['barangay'] ?>" required>

        <div class="btn-container">
            <button type="submit" class="btn btn-save">Update Site</button>
            <a href="2sites.php" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
