<?php
// final merged: odashboard.php + 3a.php + 2a.php + 1a.php  (green theme, scrolling layout)
date_default_timezone_set("Asia/Manila");
$nowDate = date("F j, Y");
$nowTime = date("h:i A");

// OpenWeather API Integration
$apiKey = ""; // Palitan ng iyong aktwal na API key
$city = "Echague";
$country = "PH";
$url = "https://api.openweathermap.org/data/2.5/weather?q={$city},{$country}&units=metric&appid={$apiKey}";
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q={$city},{$country}&units=metric&appid={$apiKey}";

// Initialize weather data with default values
$weatherData = [
    'temp' => '29',
    'description' => 'Breezy',
    'icon' => 'fa-wind',
    'humidity' => '72',
    'wind' => '5.5'
];

$forecastData = [];

// Function to get weather icon based on OpenWeather condition codes
function getWeatherIcon($code) {
    $icons = [
        // Thunderstorm
        '200' => 'fa-cloud-bolt', '201' => 'fa-cloud-bolt', '202' => 'fa-cloud-bolt', 
        '210' => 'fa-bolt', '211' => 'fa-bolt', '212' => 'fa-bolt', 
        '221' => 'fa-bolt', '230' => 'fa-cloud-bolt', '231' => 'fa-cloud-bolt', 
        '232' => 'fa-cloud-bolt',
        
        // Drizzle
        '300' => 'fa-cloud-rain', '301' => 'fa-cloud-rain', '302' => 'fa-cloud-rain',
        '310' => 'fa-cloud-rain', '311' => 'fa-cloud-rain', '312' => 'fa-cloud-rain',
        '313' => 'fa-cloud-rain', '314' => 'fa-cloud-rain', '321' => 'fa-cloud-rain',
        
        // Rain
        '500' => 'fa-cloud-showers-heavy', '501' => 'fa-cloud-showers-heavy',
        '502' => 'fa-cloud-showers-heavy', '503' => 'fa-cloud-showers-heavy',
        '504' => 'fa-cloud-showers-heavy', '511' => 'fa-snowflake',
        '520' => 'fa-cloud-showers-heavy', '521' => 'fa-cloud-showers-heavy',
        '522' => 'fa-cloud-showers-heavy', '531' => 'fa-cloud-showers-heavy',
        
        // Snow
        '600' => 'fa-snowflake', '601' => 'fa-snowflake', '602' => 'fa-snowflake',
        '611' => 'fa-snowflake', '612' => 'fa-snowflake', '613' => 'fa-snowflake',
        '615' => 'fa-snowflake', '616' => 'fa-snowflake', '620' => 'fa-snowflake',
        '621' => 'fa-snowflake', '622' => 'fa-snowflake',
        
        // Atmosphere
        '701' => 'fa-smog', '711' => 'fa-smog', '721' => 'fa-smog',
        '731' => 'fa-smog', '741' => 'fa-smog', '751' => 'fa-smog',
        '761' => 'fa-smog', '762' => 'fa-smog', '771' => 'fa-wind',
        '781' => 'fa-tornado',
        
        // Clear
        '800' => 'fa-sun',
        
        // Clouds
        '801' => 'fa-cloud-sun', '802' => 'fa-cloud', '803' => 'fa-cloud',
        '804' => 'fa-cloud'
    ];
    
    return isset($icons[$code]) ? $icons[$code] : 'fa-cloud';
}

// Function to get daily forecast from 5-day data
function getDailyForecast($forecastList) {
    $dailyForecast = [];
    $datesProcessed = [];
    
    foreach ($forecastList as $item) {
        $date = date('Y-m-d', $item['dt']);
        
        // Only take one forecast per day (around midday)
        if (!in_array($date, $datesProcessed) && date('H', $item['dt']) >= 11 && date('H', $item['dt']) <= 14) {
            $dailyForecast[] = [
                'date' => $date,
                'temp' => round($item['main']['temp']),
                'description' => $item['weather'][0]['description'],
                'icon' => getWeatherIcon($item['weather'][0]['id']),
                'day' => date('D', $item['dt'])
            ];
            $datesProcessed[] = $date;
            
            // We need 5 days forecast
            if (count($dailyForecast) >= 5) {
                break;
            }
        }
    }
    
    return $dailyForecast;
}

// Get current weather data
if ($apiKey !== "YOUR_API_KEY_HERE") {
    $json = @file_get_contents($url);
    if ($json !== FALSE) {
        $data = json_decode($json, true);
        
        if (isset($data['main']) && isset($data['weather'][0])) {
            $weatherData = [
                'temp' => round($data['main']['temp']),
                'description' => ucfirst($data['weather'][0]['description']),
                'icon' => getWeatherIcon($data['weather'][0]['id']),
                'humidity' => $data['main']['humidity'],
                'wind' => round($data['wind']['speed'], 1)
            ];
        }
    }
    
    // Get 5-day forecast data
    $forecastJson = @file_get_contents($forecastUrl);
    if ($forecastJson !== FALSE) {
        $forecastDataFull = json_decode($forecastJson, true);
        
        if (isset($forecastDataFull['list'])) {
            $forecastData = getDailyForecast($forecastDataFull['list']);
        }
    }
}

// If we couldn't get forecast data, use default values
if (empty($forecastData)) {
    $forecastData = [
        ['day' => 'Today', 'temp' => 29, 'description' => 'Windy', 'icon' => 'fa-wind'],
        ['day' => 'Tomorrow', 'temp' => 27, 'description' => 'Partly Cloudy', 'icon' => 'fa-cloud-sun'],
        ['day' => date('D', strtotime('+2 days')), 'temp' => 31, 'description' => 'Light Rain', 'icon' => 'fa-cloud-showers-heavy'],
        ['day' => date('D', strtotime('+3 days')), 'temp' => 28, 'description' => 'Overcast', 'icon' => 'fa-cloud'],
        ['day' => date('D', strtotime('+4 days')), 'temp' => 30, 'description' => 'Sunny', 'icon' => 'fa-sun']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>SmartTemp SYSTEM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=&callback=initMap" async defer></script>
  <style>
   
  
    :root {
      --primary: #28a745;
      --secondary: #ffc107;
      --danger: #dc3545;
      --info: #17a2b8;
      --dark: #343a40;
      --light: #f8f9fa;
      --gray: #6c757d;
      --sidebar-width: 250px;
      --card-bg: #ffffff;
      --shadow: 0 4px 12px rgba(0,0,0,.08);
      --radius: 14px;
    }
    
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f6f9;
      color: #212529;
      display: flex;
    }

    /* Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      background: #fff;
      border-right: 1px solid #e0e0e0;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
    }

    .profile {
      text-align: center;
      padding: 20px;
      border-bottom: 1px solid #e0e0e0;
    }

    .profile img {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
    }

    .profile h3 {
      font-size: 16px;
      margin-bottom: 4px;
      color: #212529;
    }

    .profile p {
      font-size: 14px;
      color: var(--gray);
      margin: 0;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin-top: 10px;
    }

    .sidebar ul li a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      color: #212529;
      text-decoration: none;
      transition: 0.3s;
      font-size: 15px;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
      background: #d4f5df;
      color: var(--primary);
      font-weight: 600;
      border-radius: 6px;
    }

    .sidebar ul li a i {
      width: 20px;
      text-align: center;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background: var(--primary);
      color: #fff;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
      font-weight: 500;
    }

    .content-wrapper {
      flex: 1;
      overflow-y: auto;
    }

    .container {
      padding: 20px;
    }

    /* Breadcrumb */
    .breadcrumb {
      font-size: 14px;
      margin-bottom: 15px;
    }

    .breadcrumb a {
      color: var(--primary);
      text-decoration: none;
      margin-right: 5px;
    }

    .breadcrumb span {
      color: var(--gray);
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .tab {
      padding: 10px 16px;
      border-radius: 6px;
      background: #f8f9fa;
      color: var(--gray);
      text-decoration: none;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.3s;
    }

    .tab.active {
      background: #d4f5df;
      color: var(--primary);
      font-weight: 500;
    }

    /* Site Select */
    .site-select {
      background: #fff;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .site-select h4 {
      font-size: 14px;
      margin-bottom: 8px;
      color: var(--gray);
    }

    .site-select .site-name {
      background: #e8f5e9;
      color: var(--primary);
      padding: 10px 15px;
      border-radius: 6px;
      font-weight: 500;
    }

    /* Cards */
    .cards {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-bottom: 20px;
    }

    @media (max-width: 1200px) {
      .cards {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .cards {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .card h4 {
      font-size: 14px;
      color: var(--gray);
      margin-bottom: 8px;
    }

    .card .value {
      font-size: 28px;
      font-weight: 700;
      color: var(--dark);
    }

    /* Panels */
    .panel {
      background: #fff;
      border-radius: 8px;
      margin-bottom: 20px;
      overflow: hidden;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .panel header {
      background: #f8f9fa;
      padding: 15px 20px;
      font-size: 15px;
      font-weight: 600;
      color: var(--dark);
      border-bottom: 1px solid #e0e0e0;
    }

    .panel .content {
      padding: 20px;
    }

    /* Map */
    #mapDashboard {
      height: 400px;
      width: 100%;
      border-radius: 6px;
    }

    /* Weather */
  

    @media (max-width: 900px) {
      .weather {
        grid-template-columns: 1fr;
      }
    }

    .temp {
      font-size: 48px;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 10px;
    }

    .label {
      font-size: 14px;
      color: var(--gray);
      margin-bottom: 5px;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }

    th {
      background: #f8f9fa;
      color: var(--gray);
      font-weight: 600;
      font-size: 14px;
    }

    .status-online {
      color: var(--primary);
      font-weight: 500;
    }

    .status-offline {
      color: var(--danger);
      font-weight: 500;
    }

    /* Additional styles for the greenhouse sections */
    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 8px 2px 14px;
    }
    
    .section-header .name {
      font-size: 18px;
      font-weight: bold;
      color: var(--primary);
    }
    
    .pill {
      background: var(--light);
      color: var(--primary);
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }
    
    .info-box {
      background: #f9f9f9;
      border-radius: 10px;
      text-align: center;
      padding: 10px;
      font-size: 14px;
    }
    
    .temperature {
      color: #1c9dd9;
      font-weight: bold;
      font-size: 18px;
    }
    
    .humidity {
      color: #f59e0b;
      font-weight: bold;
      font-size: 18px;
    }
    
    .ventilation {
      color: var(--primary);
      font-weight: bold;
      font-size: 18px;
    }
    
    .irrigation {
      color: var(--gray);
      font-weight: bold;
      font-size: 18px;
    }

    .desc {
      margin-top: 12px;
      font-size: 14px;
      color: #374151;
      line-height: 1.6;
    }

    .btn {
      border: 1px solid var(--primary);
      background: #fff;
      color: var(--primary);
      font-weight: bold;
      padding: 10px 18px;
      border-radius: 10px;
      cursor: pointer;
      transition: .25s;
      display: inline-block;
      text-decoration: none;
      font-size: 14px;
    }
    
    .btn:hover {
      background: var(--primary);
      color: #fff;
    }

    .details-wrap {
      display: none;
      margin-top: 18px;
    }
    
    .section-box {
      background: #fff;
      border-radius: 12px;
      padding: 18px;
      margin-bottom: 18px;
      box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }
    
    .stats-4 {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
    }
    
    .stat-box {
      background: #f9f9f9;
      border-radius: 12px;
      padding: 15px;
      text-align: center;
    }
    
    .stat-value {
      font-size: 22px;
      font-weight: bold;
      margin: 5px 0;
    }
    
    .stat-label {
      font-size: 13px;
      color: #777;
    }

    .activity {
      border-bottom: 1px solid #eee;
      padding: 12px 0;
    }
    
    .activity:last-child {
      border-bottom: none;
    }
    
    .activity-title {
      font-weight: bold;
      font-size: 14px;
    }
    
    .activity-time {
      font-size: 12px;
      color: #888;
    }
    
    .activity-desc {
      font-size: 13px;
      color: #555;
    }
    
    .badge-normal {
      background: #e6f9ed;
      color: var(--primary);
      font-weight: bold;
      padding: 4px 10px;
      border-radius: 14px;
      font-size: 12px;
    }

    /* New styles for the map and weather sections */
    .farm-overview {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .farm-card {
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .farm-card h2 {
      font-size: 20px;
      margin-bottom: 10px;
      color: var(--primary);
    }
    
    .farm-stats {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-top: 15px;
    }
    
    .farm-stat {
      background: #e8f5e9;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
    }
    
    .farm-stat strong {
      display: block;
      font-size: 24px;
      color: var(--primary);
      margin-bottom: 5px;
    }
    
    .farm-stat span {
      font-size: 14px;
      color: var(--gray);
    }
    
    .weather-forecast {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    
    .forecast-day {
      text-align: center;
      flex: 1;
      padding: 10px;
    }
    
    .forecast-day:not(:last-child) {
      border-right: 1px solid #eee;
    }
    
    .forecast-day strong {
      display: block;
      font-size: 14px;
      margin-bottom: 10px;
    }
    
    .forecast-temp {
      font-size: 18px;
      font-weight: bold;
      color: var(--primary);
      margin-bottom: 5px;
    }
    
    .forecast-desc {
      font-size: 12px;
      color: var(--gray);
    }

    /* Responsive */
    @media (max-width: 1100px) {
      .stats-4 {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .farm-overview {
        grid-template-columns: 1fr;
      }
    }
    
    @media (max-width: 768px) {
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .weather-forecast {
        flex-wrap: wrap;
      }
      
      .forecast-day {
        flex: 0 0 33.333%;
      }
    }

    .forecast-icon {
      font-size: 28px;
      margin: 8px 0;
      color: #f59e0b; /* default sun color */
      animation: pulse 2s infinite;
    }

    .forecast-day:nth-child(1) .forecast-icon { color: #60a5fa; }   /* windy - blue */
    .forecast-day:nth-child(2) .forecast-icon { color: #fbbf24; }   /* partly cloudy - yellow */
    .forecast-day:nth-child(3) .forecast-icon { color: #3b82f6; }   /* rain - deep blue */
    .forecast-day:nth-child(4) .forecast-icon { color: #6b7280; }   /* overcast - gray */
    .forecast-day:nth-child(5) .forecast-icon { color: #f59e0b; }   /* sunny - orange */

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.2); opacity: 0.8; }
    }

    /* NEW STYLES FOR IMPROVED IMAGE DISPLAY */
    .greenhouse-image-container {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f8f9fa;
      border-radius: 12px;
      padding: 15px;
      height: 220px;
      overflow: hidden;
    }
    
    .greenhouse-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      border-radius: 8px;
    }
    
    .greenhouse-content {
      display: flex;
      gap: 20px;
      align-items: stretch;
    }
    
    @media (max-width: 900px) {
      .greenhouse-content {
        flex-direction: column;
      }
      
      .greenhouse-image-container {
        height: 180px;
      }
    }


  </style>
  
</head>
<body>
  <div class="sidebar">
    <div class="profile">
      <img src="rhynnsel.png" alt="User">
      <h3>Juan D. Cruz Jr.</h3>
      <p>Developer</p>
    </div>

    <ul>
      <li><a href="dashboard.php" class="active"><i class="fa fa-gauge"></i> Dashboard</a></li>
      <li><a href="1index.php"><i class="fa fa-users"></i> Users</a></li>
      <li><a href="2sites.php"><i class="fa fa-building"></i> Sites</a></li>
      <li><a href="3archives.php"><i class="fa fa-box-archive"></i> Archives</a></li>
      <li><a href="5role.php"><i class="fa fa-id-badge"></i> Roles</a></li>
      <li><a href="index.php?page=greenhouse"><i class="fa fa-leaf"></i> Greenhouse Activity Types</a></li>
      <li><a href="index.php?page=sensors"><i class="fa fa-microchip"></i> System Sensors</a></li>
      <li><a href="index.php?page=features"><i class="fa fa-layer-group"></i> Pages & Features</a></li>
    </ul>
  </div>
  
  <div class="main-content">
    <header class="topbar">
      <div>🌱 Dashboard</div>
      <div><?php echo $nowDate; ?> • <?php echo $nowTime; ?></div>
    </header>
    
    <div class="content-wrapper">
      <div class="container">
        <!-- Breadcrumb + Tabs -->
        <div class="breadcrumb">
          <a href="#">Home</a> / <span>Dashboard</span>
        </div>
        
        <div class="tabs">
          <div class="tab active"><i class="fa fa-home"></i> SINGLE SITE</div>
          <div class="tab"><i class="fa fa-map"></i> ALL SITES</div>
        </div>
        
        <div class="site-select">
          <h4>Select Site</h4>
          <div class="site-name">Coastal Farms Inc.</div>
        </div>
        
        <!-- KPI CARDS -->
        <section class="cards">
          <div class="card">
            <h4>Total Areas</h4>
            <div class="value">3</div>
          </div>
          <div class="card">
            <h4>Active Stations</h4>
            <div class="value">6</div>
          </div>
          <div class="card">
            <h4>Avg Temperature</h4>
            <div class="value">28.7°C</div>
          </div>
          <div class="card">
            <h4>Alerts</h4>
            <div class="value">0</div>
          </div>
        </section>
        
        <!-- MAP & FARM OVERVIEW -->
        <section class="panel">
          <header>📍 Coastal Farms Inc.</header>
          <div class="content">
            <div class="farm-overview">
              <div class="farm-card">
                <h2>LOCATION</h2>
                <p>PM8J+X4X, San Fabian, Echague, Isabela</p>
                <div id="mapDashboard"></div>
              </div>
              
              <div class="farm-card">
                <h2>FARM STATISTICS</h2>
                <div class="farm-stats">
                  <div class="farm-stat">
                    <strong>6</strong>
                    <span>GREENHOUSES</span>
                  </div>
                  <div class="farm-stat">
                    <strong>10</strong>
                    <span>MICROCONTROLLER UNITS</span>
                  </div>
                  <div class="farm-stat">
                    <strong>24</strong>
                    <span>SENSORS</span>
                  </div>
                  <div class="farm-stat">
                    <strong>4</strong>
                    <span>TECHNICIANS</span>
                  </div>
                </div>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">
                  <p style="font-weight: bold; color: var(--primary); margin-bottom: 10px;">Green Valley Farm, Laguna, Philippines</p>
                  <div style="display: flex; align-items: center;">
                    <span style="font-size: 24px; font-weight: bold; color: var(--primary); margin-right: 10px;"><?php echo $weatherData['temp']; ?>°C</span>
                    <span><?php echo $weatherData['description']; ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        
        <!-- WEATHER FORECAST -->
        <section class="panel">
          <header>☁ Weather Forecast</header>
          <div class="content">
            <div class="weather-forecast">
              <?php foreach ($forecastData as $index => $forecast): ?>
              <div class="forecast-day">
                <strong><?php echo $index === 0 ? 'TODAY' : ($index === 1 ? 'TOMORROW' : $forecast['day']); ?></strong>
                <div class="forecast-temp"><?php echo $forecast['temp']; ?>°C</div>
                <div class="forecast-icon"><i class="fa-solid <?php echo $forecast['icon']; ?>"></i></div>
                <div class="forecast-desc"><?php echo $forecast['description']; ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </section>

        <!-- ===================== GREENHOUSE A ===================== -->
        <section class="panel" style="margin-top:28px">
          <header>Greenhouse A – Salt-Tolerant Crops</header>
          <div class="content">
            <div style="display:flex; gap:20px; align-items:center">
              <div style="flex:1; display:flex; justify-content:center">
                <img src="2seaweed.png" alt="Greenhouse A" width="150">
              </div>
              <div class="info-grid" style="flex:1">
                <div class="info-box">
                  <img src="1temperature.png" alt="Temp">
                  <div class="temperature">29°C</div>
                  <div>TEMPERATURE</div>
                </div>
                <div class="info-box">
                  <img src="1humidity.png" alt="Humidity">
                  <div class="humidity">72%</div>
                  <div>HUMIDITY</div>
                </div>
                <div class="info-box">
                  <img src="1ventilation.png" alt="Ventilation">
                  <div class="ventilation">Open</div>
                  <div>VENTILATION</div>
                </div>
                <div class="info-box">
                  <img src="1irrigation.png" alt="Irrigation">
                  <div class="irrigation">Inactive</div>
                  <div>IRRIGATION</div>
                </div>
              </div>
            </div>

            <p class="desc">
              Innovative facility for <b>salt-tolerant crop research</b> including quinoa and specialized vegetables.
              Uses desalinated seawater irrigation systems. <i>Coastal agriculture pioneer</i> developing
              climate-resilient farming methods.
            </p>

            <div style="text-align:center; margin-top:12px">
              <button class="btn" id="btnA">VIEW MORE DETAILS</button>
            </div>

            <div class="details-wrap" id="detailsA">
              <div class="section-box">
                <h3>📊 Statistics</h3>
                <div class="stats-4">
                  <div class="stat-box"><div class="stat-value">2</div><div class="stat-label">MCUs</div></div>
                  <div class="stat-box"><div class="stat-value">12</div><div class="stat-label">SENSORS</div></div>
                  <div class="stat-box"><div class="stat-value">1650</div><div class="stat-label">READINGS</div></div>
                  <div class="stat-box"><div class="stat-value">29</div><div class="stat-label">ACTIVITIES</div></div>
                </div>
              </div>

              <div class="section-box">
                <h3>🔄 Recent Activities</h3>
                <div class="activity"><div class="activity-title">Vent Open</div><div class="activity-time">22 minutes ago</div><div class="activity-desc">High-velocity coastal wind simulation for crop testing</div></div>
                <div class="activity"><div class="activity-title">Irrigation Stop</div><div class="activity-time">2 hours ago</div><div class="activity-desc">Wind stress irrigation protocol completed</div></div>
                <div class="activity"><div class="activity-title">Vent Close</div><div class="activity-time">4 hours ago</div><div class="activity-desc">Controlled environment phase for plant recovery</div></div>
              </div>

              <div class="section-box">
                <h3>📑 Recent Sensor Readings</h3>
                <table>
                  <tr><th>SENSOR</th><th>VALUE</th><th>TIME</th><th>STATUS</th></tr>
                  <tr><td>🌡 Temperature Sensor</td><td>31.8°C</td><td>5 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                  <tr><td>💧 Humidity Sensor</td><td>57.9%</td><td>5 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                  <tr><td>🌡 Temperature Sensor</td><td>32.4°C</td><td>10 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                </table>
              </div>

              <div class="section-box">
                <h3>📉 Sensor Readings Charts</h3>
                <div style="margin-bottom:16px">
                  <div style="font-weight:bold; color:#555; margin-bottom:8px">🌡 Temperature (Threshold: 35°C)</div>
                  <canvas id="tempChartA" height="100"></canvas>
                </div>
                <div>
                  <div style="font-weight:bold; color:#555; margin-bottom:8px">💧 Humidity (Threshold: 60%)</div>
                  <canvas id="humChartA" height="100"></canvas>
                </div>
              </div>
            </div>
          </div>
        </section>

         <!-- ===================== GREENHOUSE B ===================== -->
        <section class="panel" style="margin-top:28px">
          <header>Greenhouse B – Seaweed Cultivation</header>
          <div class="content">
            <div class="greenhouse-content">
              <div class="greenhouse-image-container">
                <img src="2seaweed.png" alt="Greenhouse B" class="greenhouse-image">
              </div>
              <div class="info-grid" style="flex:1">
                <div class="info-box">
                  <img src="1temperature.png" alt="Temp">
                  <div class="temperature">25°C</div>
                  <div>TEMPERATURE</div>
                </div>
                <div class="info-box">
                  <img src="1humidity.png" alt="Humidity">
                  <div class="humidity">95%</div>
                  <div>HUMIDITY</div>
                </div>
                <div class="info-box">
                  <img src="1ventilation.png" alt="Ventilation">
                  <div class="ventilation">Closed</div>
                  <div>VENTILATION</div>
                   </div>
                <div class="info-box">
                  <img src="1irrigation.png" alt="Irrigation">
                  <div class="irrigation">Inactive</div>
                  <div>IRRIGATION</div>
                </div>
              </div>
            </div>

            <p class="desc">
              <b>Wind resistance research facility</b> testing crop varieties under extreme coastal conditions.
              Advanced ventilation systems simulate typhoon-level winds.
              <i>Climate adaptation studies</i> for storm-resilient agriculture.
            </p>

            <div style="text-align:center; margin-top:12px">
              <button class="btn" id="btnC">VIEW MORE DETAILS</button>
            </div>

            <div class="details-wrap" id="detailsC">
              <div class="section-box">
                <h3>📊 Statistics</h3>
                <div class="stats-4">
                  <div class="stat-box"><div class="stat-value">2</div><div class="stat-label">MCUs</div></div>
                  <div class="stat-box"><div class="stat-value">12</div><div class="stat-label">SENSORS</div></div>
                  <div class="stat-box"><div class="stat-value">1650</div><div class="stat-label">READINGS</div></div>
                  <div class="stat-box"><div class="stat-value">29</div><div class="stat-label">ACTIVITIES</div></div>
                </div>
              </div>

              <div class="section-box">
                <h3>🔄 Recent Activities</h3>
                <div class="activity"><div class="activity-title">Vent Open</div><div class="activity-time">22 minutes ago</div><div class="activity-desc">High-velocity coastal wind simulation for crop testing</div></div>
                <div class="activity"><div class="activity-title">Irrigation Stop</div><div class="activity-time">2 hours ago</div><div class="activity-desc">Wind stress irrigation protocol completed</div></div>
                <div class="activity"><div class="activity-title">Vent Close</div><div class="activity-time">4 hours ago</div><div class="activity-desc">Controlled environment phase for plant recovery</div></div>
              </div>

              <div class="section-box">
                <h3>📑 Recent Sensor Readings</h3>
                <table>
                  <tr><th>SENSOR</th><th>VALUE</th><th>TIME</th><th>STATUS</th></tr>
                  <tr><td>🌡 Temperature Sensor</td><td>31.8°C</td><td>5 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                  <tr><td>💧 Humidity Sensor</td><td>57.9%</td><td>5 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                  <tr><td>🌡 Temperature Sensor</td><td>32.4°C</td><td>10 minutes ago</td><td><span class="badge-normal">NORMAL</span></td></tr>
                </table>
              </div>

              <div class="section-box">
                <h3>📉 Sensor Readings Charts</h3>
                <div style="margin-bottom:16px">
                  <div style="font-weight:bold; color:#555; margin-bottom:8px">🌡 Temperature (Threshold: 35°C)</div>
                  <canvas id="tempChartC" height="100"></canvas>
                </div>
                <div>
                  <div style="font-weight:bold; color:#555; margin-bottom:8px">💧 Humidity (Threshold: 60%)</div>
                  <canvas id="humChartC" height="100"></canvas>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- ===================== SCRIPTS ===================== -->
  <script>
    // Google Map
    function initMap() {
      const location = { lat: 16.71747379767142, lng: 121.68037554724444 };
      const map = new google.maps.Map(document.getElementById("mapDashboard"), {
        zoom: 14,
        center: location,
        mapTypeId: 'satellite'
      });
      new google.maps.Marker({ position: location, map: map });
    }

    // Toggle + Charts: A, B, C
    const labels8 = ['10:51 PM', '01:51 AM', '04:51 AM', '07:51 AM', '10:51 AM', '01:51 PM', '04:51 PM', '07:51 PM'];
    const tempSeries = [30, 28, 34, 31, 22, 29, 27, 33];
    const humSeries = [65, 60, 78, 72, 55, 70, 62, 74];
    
    const makeLine = (ctx, label, data, color, fillColor, thresh) => {
      return new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels8,
          datasets: [
            { 
              label: label, 
              data: data, 
              borderColor: color, 
              backgroundColor: fillColor, 
              fill: true, 
              tension: .4, 
              pointBackgroundColor: color 
            },
            { 
              label: 'Threshold', 
              data: new Array(labels8.length).fill(thresh), 
              borderColor: color.replace('1)', '.6)'), 
              borderDash: [5, 5], 
              fill: false, 
              pointRadius: 0 
            }
          ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } } }
      });
    };

    // --- Greenhouse A
    (function() {
      const details = document.getElementById('detailsA');
      document.getElementById('btnA').addEventListener('click', () => {
        details.style.display = (details.style.display === 'block') ? 'none' : 'block';
        if (details.style.display === 'block' && !window._chartsA) {
          makeLine(
            document.getElementById('tempChartA').getContext('2d'),
            'Temperature (°C)', tempSeries, 'rgba(239,68,68,1)', 'rgba(239,68,68,.2)', 35
          );
          makeLine(
            document.getElementById('humChartA').getContext('2d'),
            'Humidity (%)', humSeries, 'rgba(37,99,235,1)', 'rgba(37,99,235,.2)', 60
          );
          window._chartsA = true;
        }
      });
    })();

    // --- Greenhouse B
    (function() {
      const details = document.getElementById('detailsB');
      document.getElementById('btnB').addEventListener('click', () => {
        details.style.display = (details.style.display === 'block') ? 'none' : 'block';
        if (details.style.display === 'block' && !window._chartsB) {
          makeLine(
            document.getElementById('tempChartB').getContext('2d'),
            'Temperature (°C)', tempSeries, 'rgba(239,68,68,1)', 'rgba(239,68,68,.2)', 35
          );
          makeLine(
            document.getElementById('humChartB').getContext('2d'),
            'Humidity (%)', humSeries, 'rgba(37,99,235,1)', 'rgba(37,99,235,.2)', 60
          );
          window._chartsB = true;
        }
      });
    })();

    // --- Greenhouse C
    (function() {
      const details = document.getElementById('detailsC');
      document.getElementById('btnC').addEventListener('click', () => {
        details.style.display = (details.style.display === 'block') ? 'none' : 'block';
        if (details.style.display === 'block' && !window._chartsC) {
          makeLine(
            document.getElementById('tempChartC').getContext('2d'),
            'Temperature (°C)', tempSeries, 'rgba(239,68,68,1)', 'rgba(239,68,68,.2)', 35
          );
          makeLine(
            document.getElementById('humChartC').getContext('2d'),
            'Humidity (%)', humSeries, 'rgba(37,99,235,1)', 'rgba(37,99,235,.2)', 60
          );
          window._chartsC = true;
        }
      });
    })();

    // Add interactivity to tabs
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
  </script>
</body>
</html>