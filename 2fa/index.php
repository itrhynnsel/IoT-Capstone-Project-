<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Enabled Environmental Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="logo">
                <img src="greenhouse.jpeg" alt="Logo" width="100">
            </div>
            <h2>IoT ENABLED ENVIRONMENTAL MANAGEMENT</h2>
            <p>WEB INTEGRATED TEMPERATURE MONITORING SYSTEM</p>
            <form action="login.php" method="POST">
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
    </script>
    <script>
document.querySelector("form").addEventListener("submit", function(e) {
    e.preventDefault(); // prevent reload
    let formData = new FormData(this);

    fetch("login.php", {
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
    window.location.href = "dashboard/index1.php"; // proceed to system
}
</script>

</body>
</html>
