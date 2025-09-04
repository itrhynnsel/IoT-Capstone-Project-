<?php
session_start();
include '1config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // same pa rin, di ko gagalawin

    $query = "SELECT * FROM info WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $_SESSION['username'] = $username;
        echo "success";  // ✅ tell JS login is valid
        exit;
    } else {
        echo "error";    // ❌ invalid login
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IoT Enabled Environmental Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: url('.0green.jpg') no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .container {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .login-box {
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
        width: 350px;
        text-align: center;
    }
    .login-box h2 {
        font-size: 18px;
        color: #2b8a3e;
        font-weight: 700;
        margin: 10px 0 5px;
    }
    .login-box p {
        font-size: 12px;
        color: #666;
        margin-bottom: 20px;
    }
    .input-box {
        position: relative;
        margin: 15px 0;
    }
    .input-box i {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #2b8a3e;
    }
    .input-box input {
        width: 100%;
        padding: 10px 40px;
        border: 1px solid #ccc;
        border-radius: 8px;
        outline: none;
        font-size: 14px;
        box-sizing: border-box;
    }
    .btn {
        width: 100%;
        padding: 12px;
        border: none;
        background: #2b8a3e;
        color: white;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }
    .btn:hover {
        background: #267a36;
    }
    footer {
        margin-top: 20px;
        font-size: 12px;
        color: #777;
    }
    /* Modal background */
    .modal {
      display: none; /* hidden by default */
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    /* Warning Box */
    .warning-box {
      width: 500px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      overflow: hidden;
      font-family: 'Poppins', sans-serif;
    }
    .warning-box .header {
      background: #ffcc00;
      padding: 12px 20px;
      font-weight: 600;
      color: #333;
    }
    .critical {
      background: #fff5e6;
      padding: 15px 20px;
      border-left: 5px solid #ff9900;
    }
    .welcome {
      background: #f9f9f9;
      padding: 15px 20px;
      border-left: 5px solid #ffcc00;
    }
    .welcome h3 { color: #e69138; margin: 0 0 8px 0; }
    .welcome ul { margin: 0; padding-left: 18px; }
    .danger {
      background: #ffe6e6;
      color: #b30000;
      padding: 15px 20px;
      border-left: 5px solid #cc0000;
    }
    .footer {
      text-align: center;
      padding: 15px;
    }
    .footer button {
      background: #ffcc00;
      border: none;
      padding: 10px 25px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    .footer button:hover {
      background: #e6b800;
    }
  </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <img src=".0greenhouse.jpeg" alt="Logo" width="100">
            </div>
            <h2>IoT ENABLED ENVIRONMENTAL MANAGEMENT</h2>
            <p>WEB INTEGRATED TEMPERATURE MONITORING SYSTEM</p>
            <form id="loginForm" method="POST">
                <label>Username</label>
                <div class="input-box">
                    <i class="fa fa-user"></i>
                    <input type="text" name="username" placeholder="Enter Username" required>
                </div>
                <label>Password</label>
                <div class="input-box">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="Enter Password" required>
                    <i class="fa fa-eye toggle-password"></i>
                </div>
                <button type="submit" class="btn"><i class="fa fa-sign-in-alt"></i> LOGIN</button>
            </form>
            <footer>
                <p>© Capstone Project 2025</p>
            </footer>
        </div>
    </div>

    <!-- Modal for Developer Warning -->
    <div id="warningModal" class="modal">
      <div class="warning-box">
        <div class="header">⚠️ Developer Access Warning</div>
        <div class="critical">
          <strong>Critical System Access Detected</strong><br>
          You are logging in with developer privileges.
        </div>
        <div class="welcome">
          <h3>👋 Welcome, <span id="userName"></span></h3>
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
          <button onclick="proceed()">⚡ I UNDERSTAND, PROCEED</button>
        </div>
      </div>
    </div>

<script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function () {
        let passwordField = document.querySelector('input[name="password"]');
        if (passwordField.type === "password") {
            passwordField.type = "text";
            this.classList.remove("fa-eye");
            this.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            this.classList.remove("fa-eye-slash");
            this.classList.add("fa-eye");
        }
    });

    // Form submission via fetch
    document.getElementById("loginForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        fetch(".0index.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "success") {
                // Show modal
                document.getElementById("userName").innerText = formData.get("username");
                document.getElementById("warningModal").style.display = "flex";
            } else {
                alert("❌ Invalid username or password!");
            }
        });
    });

    function proceed() {
        window.location.href = "0dashboard.php";
    }
</script>
</body>
</html>
