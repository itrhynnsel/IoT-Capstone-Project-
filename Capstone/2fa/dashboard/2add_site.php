<?php
include '2config.php';

// Kung may submission ng form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $owner = $_POST['owner'];
    $region = $_POST['region'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $sql = "INSERT INTO sites (name, owner, region, province, city, barangay, latitude, longitude) 
            VALUES ('$name', '$owner', '$region', '$province', '$city', '$barangay', '$latitude', '$longitude')";

    if ($conn->query($sql) === TRUE) {
        header("Location: 2sites.php?success=1");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Kunin lahat ng users para sa owner dropdown
$users = $conn->query("SELECT id, first_name, last_name FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Site - SmartTemp SYSTEM</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        header {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            font-size: 20px;
            font-weight: bold;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            border-radius: 12px;
            padding: 25px 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            font-size: 22px;
        }
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 25px;
        }
        form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
            font-size: 14px;
        }
        form input, form select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.2s;
        }
        form input:focus, form select:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 3px rgba(40,167,69,0.4);
        }
        .full-width {
            grid-column: span 2;
        }
        .btn-container {
            grid-column: span 2;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 10px;
        }
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .btn-save:hover {
            background: #218838;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            text-decoration: none;
            text-align: center;
            line-height: 34px;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
        .error {
            color: red;
            grid-column: span 2;
        }
        #map {
            width: 100%;
            height: 350px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<header>SmartTemp SYSTEM</header>

<div class="container">
    <h2>Add New Site</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <div>
            <label for="name">Site Name *</label>
            <input type="text" name="name" id="name" required>
        </div>

        <div>
            <label for="owner">Assign To User *</label>
            <select name="owner" id="owner" required>
                <option value="">-- Select Owner --</option>
                <?php while($u = $users->fetch_assoc()): ?>
                    <option value="<?= $u['id']; ?>"><?= $u['first_name']." ".$u['last_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="region">Region *</label>
            <select name="region" id="region" required>
                <option value="">Select Region</option>
                <option value="Region I">Region I</option>
                <option value="Region II">Region II</option>
                <option value="NCR">NCR</option>
                <option value="CAR">CAR</option>
                <!-- Pwede dagdagan dito -->
            </select>
        </div>

        <div>
            <label for="province">Province *</label>
            <input type="text" name="province" id="province" required>
        </div>

        <div>
            <label for="city">City/Municipality *</label>
            <input type="text" name="city" id="city" required>
        </div>

        <div>
            <label for="barangay">Barangay *</label>
            <input type="text" name="barangay" id="barangay" required>
        </div>

        <div>
            <label for="latitude">Latitude *</label>
            <input type="text" name="latitude" id="latitude" value="16.6111" required>
        </div>

        <div>
            <label for="longitude">Longitude *</label>
            <input type="text" name="longitude" id="longitude" value="121.7211" required>
        </div>

        <div id="map" class="full-width"></div>

        <div class="btn-container">
            <button type="submit" class="btn btn-save">Save Site</button>
            <a href="2sites.php" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialize map
    var lat = parseFloat(document.getElementById("latitude").value) || 16.6111;
    var lng = parseFloat(document.getElementById("longitude").value) || 121.7211;

    var map = L.map('map').setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([lat, lng], {draggable:true}).addTo(map);

    marker.on('dragend', function(e) {
        var position = marker.getLatLng();
        document.getElementById("latitude").value = position.lat.toFixed(6);
        document.getElementById("longitude").value = position.lng.toFixed(6);
    });
</script>

</body>
</html>
