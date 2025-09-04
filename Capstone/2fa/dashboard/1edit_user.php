<?php 
include '1config.php'; 

$id = $_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 900px;
      margin: 30px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    form {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    .form-group {
      display: flex;
      flex-direction: column;
    }
    label {
      margin-bottom: 6px;
      font-weight: bold;
      color: #555;
    }
    input, select {
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    button {
      grid-column: span 2;
      padding: 12px;
      background: #007bff;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      background: #0056b3;
    }
    .back-link {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      color: #007bff;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Edit User</h2>
  <form method="POST">
    <div class="form-group">
      <label>First Name</label>
      <input type="text" name="first_name" value="<?php echo $user['first_name']; ?>" required>
    </div>

    <div class="form-group">
      <label>Middle Name</label>
      <input type="text" name="middle_name" value="<?php echo $user['middle_name']; ?>">
    </div>

    <div class="form-group">
      <label>Last Name</label>
      <input type="text" name="last_name" value="<?php echo $user['last_name']; ?>" required>
    </div>

    <div class="form-group">
      <label>Extension</label>
      <input type="text" name="extension_name" value="<?php echo $user['extension_name']; ?>">
    </div>

    <div class="form-group">
      <label>Gender</label>
      <select name="gender" required>
        <option value="Male" <?php if($user['gender']=="Male") echo "selected"; ?>>Male</option>
        <option value="Female" <?php if($user['gender']=="Female") echo "selected"; ?>>Female</option>
        <option value="Other" <?php if($user['gender']=="Other") echo "selected"; ?>>Other</option>
      </select>
    </div>

    <div class="form-group">
      <label>Contact Number</label>
      <input type="text" name="contact_number" value="<?php echo $user['contact_number']; ?>">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
    </div>

    <div class="form-group">
      <label>Role</label>
      <input type="text" name="role" value="<?php echo $user['role']; ?>" required>
    </div>

    <div class="form-group">
      <label>Region</label>
      <input type="text" name="region" value="<?php echo $user['region']; ?>" required>
    </div>

    <div class="form-group">
      <label>Province</label>
      <input type="text" name="province" value="<?php echo $user['province']; ?>" required>
    </div>

    <div class="form-group">
      <label>City</label>
      <input type="text" name="city" value="<?php echo $user['city']; ?>" required>
    </div>

    <div class="form-group">
      <label>Barangay</label>
      <input type="text" name="barangay" value="<?php echo $user['barangay']; ?>" required>
    </div>

    <button type="submit" name="update">Update</button>
  </form>
  <a href="1index.php" class="back-link">← Back to Users</a>
</div>

<?php
if (isset($_POST['update'])) {
  $sql = "UPDATE users SET 
          first_name='".$_POST['first_name']."',
          middle_name='".$_POST['middle_name']."',
          last_name='".$_POST['last_name']."',
          extension_name='".$_POST['extension_name']."',
          gender='".$_POST['gender']."',
          contact_number='".$_POST['contact_number']."',
          email='".$_POST['email']."',
          role='".$_POST['role']."',
          region='".$_POST['region']."',
          province='".$_POST['province']."',
          city='".$_POST['city']."',
          barangay='".$_POST['barangay']."'
          WHERE id=$id";
  if ($conn->query($sql)) {
    echo "<script>alert('User updated successfully!'); window.location='1index.php';</script>";
  } else {
    echo "Error: " . $conn->error;
  }
}
?>

</body>
</html>
