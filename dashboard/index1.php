<?php
// Detect kung anong page ang i-oopen (default: dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<?php include 'header.php'; ?>
<body>
  <?php include 'sidebar.php'; ?>
  <?php include 'topbar.php'; ?>

  <div class="main">
    <?php
    if ($page == 'dashboard') {
        // ======= DASHBOARD CONTENT (yung binigay mong code) =======
        ?>
        <!-- Greenhouse Section -->
        <div class="grid">
          <div class="card greenhouse">
            <h3>Greenhouse A - Tomatoes</h3>
            <img src="tomato.jpg" alt="Tomatoes">
            <div class="stats">
              <div><i class="fa fa-temperature-half"></i> 26°C <br><small>Temperature</small></div>
              <div><i class="fa fa-droplet"></i> 68% <br><small>Humidity</small></div>
              <div><i class="fa fa-fan"></i> Open <br><small>Ventilation</small></div>
              <div><i class="fa fa-water"></i> Inactive <br><small>Irrigation</small></div>
            </div>
            <p>This greenhouse specializes in <b>cherry tomato production</b> with optimal growing conditions.</p>
            <button class="details">View More Details</button>
          </div>

          <div class="card greenhouse">
            <h3>Greenhouse B - Lettuce</h3>
            <img src="lettuce.jpg" alt="Lettuce">
            <div class="stats">
              <div><i class="fa fa-temperature-half"></i> 24°C <br><small>Temperature</small></div>
              <div><i class="fa fa-droplet"></i> 85% <br><small>Humidity</small></div>
              <div><i class="fa fa-fan"></i> Closed <br><small>Ventilation</small></div>
              <div><i class="fa fa-water"></i> Active <br><small>Irrigation</small></div>
            </div>
            <p>Dedicated to <b>hydroponic lettuce cultivation</b> using advanced nutrient film technique.</p>
            <button class="details">View More Details</button>
          </div>
        </div>

        <!-- Farm Section -->
        <div class="grid">
          <div class="card farm">
            <h3>Green Valley Farm</h3>
            <p><b>Location:</b> 123 Agricultural Road, Laguna, Philippines</p>
            <img src="map.jpg" alt="Farm Map" class="map">
            <div class="farm-stats">
              <div>8 Greenhouses</div>
              <div>12 Microcontrollers</div>
              <div>36 Sensors</div>
              <div>5 Technicians</div>
            </div>
          </div>

          <div class="card weather">
            <h3>Weather</h3>
            <p>Green Valley Farm, Laguna, Philippines</p>
            <h1>28°C</h1>
            <p>Partly Cloudy</p>
            <div class="forecast">
              <span>Today 28°C</span>
              <span>Tomorrow 30°C</span>
              <span>Day 3 26°C</span>
              <span>Day 4 29°C</span>
              <span>Day 5 31°C</span>
            </div>
          </div>
        </div>
        <?php
        // ======= END OF DASHBOARD =======
    } elseif ($page == 'users') {
        include 'pages/users.php';
    } elseif ($page == 'sites') {
        include 'pages/sites.php';
    } elseif ($page == 'archives') {
        include 'pages/archives.php';
    } elseif ($page == 'roles') {
        include 'pages/roles.php';
    } elseif ($page == 'greenhouse') {
        include 'pages/greenhouse.php';
    } elseif ($page == 'sensors') {
        include 'pages/sensors.php';
    } elseif ($page == 'features') {
        include 'pages/features.php';
    } else {
        echo "<h2>404 - Page not found</h2>";
    }
    ?>
  </div>

  <?php include 'footer.php'; ?>
</body>
