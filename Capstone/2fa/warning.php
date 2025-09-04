<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Developer Access Warning</title>
  <link rel="stylesheet" href="style.css"> <!-- Link mo sa CSS -->
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f8f9fa;
    }
    .warning-box {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 420px;
      text-align: center;
      border-left: 6px solid #ffcc00;
    }
    h3 {
      color: #ff9900;
      margin-bottom: 10px;
    }
    h4 {
      margin: 10px 0;
    }
    ul {
      text-align: left;
      margin: 15px 0;
      padding-left: 20px;
    }
    .danger {
      color: red;
      font-size: 14px;
      margin-top: 15px;
      font-weight: bold;
    }
    .proceed-btn {
      display: inline-block;
      margin-top: 20px;
      background: #ffcc00;
      color: #000;
      font-weight: bold;
      padding: 12px 20px;
      border-radius: 8px;
      text-decoration: none;
      transition: 0.3s;
    }
    .proceed-btn:hover {
      background: #ffb400;
    }
  </style>
</head>
<body>
  <div class="warning-box">
    <h3>⚠️ Developer Access Warning</h3>
    <p><strong>Critical System Access Detected</strong><br>
    You are logging in with developer privileges.</p>

    <h4>👋 Welcome, admin</h4>
    <p>Developer users have elevated privileges including:</p>
    <ul>
      <li>Full system administration access</li>
      <li>Database and configuration management</li>
      <li>User role and permission modifications</li>
      <li>System-wide settings control</li>
    </ul>

    <p class="danger">❗ Use with extreme caution. Actions performed with this account can affect the entire system.</p>

    <!-- Redirect button -->
    <a href="dashboard/dashboard.php" class="proceed-btn">⚡ I UNDERSTAND, PROCEED</a>
  </div>
</body>
</html>
