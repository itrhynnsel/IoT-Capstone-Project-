<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Developer Access Warning</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f6fa;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0;
    }
    .warning-box {
      width: 500px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .header {
      background: #ffcc00;
      padding: 12px 20px;
      font-weight: 600;
      color: #333;
      border-bottom: 1px solid #e0e0e0;
    }
    .critical {
      background: #fff5e6;
      padding: 15px 20px;
      border-left: 5px solid #ff9900;
      color: #333;
      font-size: 14px;
    }
    .welcome {
      background: #f9f9f9;
      padding: 15px 20px;
      border-left: 5px solid #ffcc00;
    }
    .welcome h3 {
      margin: 0 0 8px 0;
      color: #e69138;
    }
    .welcome ul {
      margin: 0;
      padding-left: 18px;
      font-size: 14px;
      color: #444;
    }
    .danger {
      background: #ffe6e6;
      color: #b30000;
      padding: 15px 20px;
      font-size: 14px;
      border-left: 5px solid #cc0000;
    }
    .footer {
      text-align: center;
      padding: 15px;
      background: #fff;
      border-top: 1px solid #eee;
    }
    .footer button {
      background: #ffcc00;
      border: none;
      padding: 10px 25px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 14px;
    }
    .footer button:hover {
      background: #e6b800;
    }
  </style>
</head>
<body>
  <div class="warning-box">
    <div class="header">⚠️ Developer Access Warning</div>

    <div class="critical">
      <strong>Critical System Access Detected</strong><br>
      You are logging in with developer privileges.
    </div>

    <div class="welcome">
      <h3>👋 Welcome, <?php echo $_SESSION['username']; ?></h3>
      <p>Developer users have elevated privileges including:</p>
      <ul>
        <li>Full system administration access</li>
        <li>Database and configuration management</li>
        <li>User role and permission modifications</li>
        <li>System-wide settings control</li>
      </ul>
    </div>

    <div class="danger">
      ❗ Use with extreme caution. Actions performed with this account can affect the entire system.
    </div>

    <div class="footer">
      <form action="system.php" method="post">
        <button type="submit">⚡ I UNDERSTAND, PROCEED</button>
      </form>
    </div>
  </div>
</body>
</html>
