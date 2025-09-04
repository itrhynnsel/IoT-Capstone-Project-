  <?php include '1config.php'; ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <style>
      body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f9fafb;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
      }

      .form-container {
        background: #fff;
        width: 850px;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
      }

      .form-container h2 {
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: 600;
      }

      form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
      }

      .form-group {
        display: flex;
        flex-direction: column;
      }

      label {
        font-size: 14px;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
      }

      input, select {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border 0.2s;
      }

      input:focus, select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
      }

      .form-actions {
        grid-column: span 2;
        display: flex;
        justify-content: flex-end;
        margin-top: 15px;
      }

      button {
        padding: 10px 18px;
        background: #28a745;
        border: none;
        border-radius: 6px;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
      }

      button:hover {
        background: #218838;
      }
    </style>
  </head>
  <body>
    <div class="form-container">
      <h2>Add User</h2>
      <form method="POST">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" required>
        </div>
        <div class="form-group">
          <label>Middle Name</label>
          <input type="text" name="middle_name">
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" required>
        </div>
        <div class="form-group">
          <label>Extension Name</label>
          <input type="text" name="extension_name">
        </div>
        <div class="form-group">
          <label>Gender</label>
          <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact_number">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <option value="">Select Role</option>
            <option>Admin</option>
            <option>User</option>
            <option>Staff</option>
          </select>
        </div>

        <!-- Cascading Address Fields -->
        <div class="form-group">
          <label>Region</label>
          <select id="region" name="region" required>
            <option value="">Select Region</option>
          </select>
        </div>
        <div class="form-group">
          <label>Province</label>
          <select id="province" name="province" required>
            <option value="">Select Province</option>
          </select>
        </div>
        <div class="form-group">
          <label>City / Municipality</label>
          <select id="city" name="city" required>
            <option value="">Select City</option>
          </select>
        </div>
        <div class="form-group">
          <label>Barangay</label>
          <select id="barangay" name="barangay" required>
            <option value="">Select Barangay</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="submit" name="save">Create User</button>
        </div>
      </form>
    </div>

    <?php
    if (isset($_POST['save'])) {
      $sql = "INSERT INTO users
        (first_name, middle_name, last_name, extension_name, gender, contact_number, email, role, region, province, city, barangay) 
        VALUES (
          '".$_POST['first_name']."',
          '".$_POST['middle_name']."',
          '".$_POST['last_name']."',
          '".$_POST['extension_name']."',
          '".$_POST['gender']."',
          '".$_POST['contact_number']."',
          '".$_POST['email']."',
          '".$_POST['role']."',
          '".$_POST['region']."',
          '".$_POST['province']."',
          '".$_POST['city']."',
          '".$_POST['barangay']."'
        )";

      if ($conn->query($sql)) {
        echo "<script>alert('User added successfully!'); window.location='1index.php';</script>";
      } else {
        echo "Error: " . $conn->error;
      }
    }
    ?>

    <script>
      // Sample hierarchical data (pwede mo palitan galing sa DB or JSON)
      const data = {
        "Region I": {
          "Ilocos Norte": {
            "Laoag City": ["Barangay 1", "Barangay 2"],
            "Batac": ["Barangay A", "Barangay B"]
          },
          "Ilocos Sur": {
            "Vigan": ["Barangay X", "Barangay Y"]
          }
        },
        "NCR": {
          "Metro Manila": {
            "Quezon City": ["Barangay Commonwealth", "Barangay Batasan"],
            "Manila": ["Barangay 659", "Barangay 660"]
          }
        }
      };

      const regionSel = document.getElementById("region");
      const provinceSel = document.getElementById("province");
      const citySel = document.getElementById("city");
      const barangaySel = document.getElementById("barangay");

      // Load Regions
      for (let r in data) {
        regionSel.options[regionSel.options.length] = new Option(r, r);
      }

      regionSel.onchange = function() {
        provinceSel.length = 1;
        citySel.length = 1;
        barangaySel.length = 1;
        if (this.value !== "") {
          for (let p in data[this.value]) {
            provinceSel.options[provinceSel.options.length] = new Option(p, p);
          }
        }
      }

      provinceSel.onchange = function() {
        citySel.length = 1;
        barangaySel.length = 1;
        if (this.value !== "") {
          let cities = data[regionSel.value][this.value];
          for (let c in cities) {
            citySel.options[citySel.options.length] = new Option(c, c);
          }
        }
      }

      citySel.onchange = function() {
        barangaySel.length = 1;
        if (this.value !== "") {
          let brgys = data[regionSel.value][provinceSel.value][this.value];
          for (let i = 0; i < brgys.length; i++) {
            barangaySel.options[barangaySel.options.length] = new Option(brgys[i], brgys[i]);
          }
        }
      }
    </script>
  </body>
  </html>
