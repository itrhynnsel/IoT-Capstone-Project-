<?php
// 1a.php (merged with 1b.php content)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Greenhouse C - Wind-Resistant Testing</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f6fa;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      margin: 0;
      padding: 20px;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 900px;
      padding: 20px;
      box-sizing: border-box;
    }

    .header {
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 15px;
    }

    .content {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .greenhouse-img {
      flex: 1;
      display: flex;
      justify-content: center;
    }

    .details {
      flex: 1;
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

    .info-box img {
      width: 30px;
      height: 30px;
      margin-bottom: 5px;
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
      color: #16a34a;
      font-weight: bold;
      font-size: 18px;
    }

    .irrigation {
      color: #9ca3af;
      font-weight: bold;
      font-size: 18px;
    }

    .description {
      margin-top: 15px;
      font-size: 13px;
      line-height: 1.5;
    }

    .button {
      margin-top: 15px;
      text-align: center;
    }

    .btn {
      border: 1px solid #16a34a;
      background: white;
      color: #16a34a;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      transition: 0.3s;
      display: inline-block;
    }

    .btn:hover {
      background: #16a34a;
      color: white;
    }

    /* Hidden details section */
    #detailsSection {
      margin-top: 30px;
      display: none;
    }

    .section {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .section h3 {
      margin: 0 0 15px;
      font-size: 15px;
      font-weight: bold;
      color: #444;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .activity {
      border-bottom: 1px solid #eee;
      padding: 12px 0;
    }
    .activity:last-child {
      border-bottom: none;
    }
    .activity-title { font-weight: bold; font-size: 14px; }
    .activity-time { font-size: 12px; color: #888; }
    .activity-desc { font-size: 13px; color: #555; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
    th { font-size: 12px; color: #666; }
    .normal {
      background: #e6f9ed; color: #16a34a; font-weight: bold;
      padding: 4px 10px; border-radius: 15px; font-size: 12px;
    }

    .chart-box { margin-top: 20px; }
    .chart-title { font-size: 14px; margin-bottom: 10px; font-weight: bold; color: #555; }

    .stats {
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
    .stat-value { font-size: 22px; font-weight: bold; margin: 5px 0; }
    .stat-label { font-size: 13px; color: #777; }
  </style>
</head>
<body>

  <div class="card">
    <div class="header">Greenhouse C - Wind-Resistant Testing</div>
    
    <div class="content">
      <div class="greenhouse-img">
        <img src="1tomato.png" alt="Greenhouse" width="150">
      </div>
      <div class="details">
        <div class="info-box">
          <img src="temperature.png" alt="Temp">
          <div class="temperature">32°C</div>
          <div>TEMPERATURE</div>
        </div>
        <div class="info-box">
          <img src="humidity.png" alt="Humidity">
          <div class="humidity">58%</div>
          <div>HUMIDITY</div>
        </div>
        <div class="info-box">
          <img src="ventilation.png" alt="Ventilation">
          <div class="ventilation">Open</div>
          <div>VENTILATION</div>
        </div>
        <div class="info-box">
          <img src="irrigation.png" alt="Irrigation">
          <div class="irrigation">Inactive</div>
          <div>IRRIGATION</div>
        </div>
      </div>
    </div>

    <div class="description">
      <b>Wind resistance research facility</b> testing crop varieties under extreme coastal conditions. 
      Advanced ventilation systems simulate typhoon-level winds. 
      <i>Climate adaptation studies</i> for storm-resilient agriculture.
    </div>

    <div class="button">
      <button class="btn" onclick="toggleDetails()">VIEW MORE DETAILS</button>
    </div>

    <!-- Hidden expandable section -->
    <div id="detailsSection">

      <!-- Statistics -->
      <div class="section">
        <h3>📊 Statistics</h3>
        <div class="stats">
          <div class="stat-box"><div class="stat-value">2</div><div class="stat-label">MCUs</div></div>
          <div class="stat-box"><div class="stat-value">12</div><div class="stat-label">SENSORS</div></div>
          <div class="stat-box"><div class="stat-value">1650</div><div class="stat-label">READINGS</div></div>
          <div class="stat-box"><div class="stat-value">29</div><div class="stat-label">ACTIVITIES</div></div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="section">
        <h3>🔄 Recent Activities</h3>
        <div class="activity"><div class="activity-title">Vent Open</div><div class="activity-time">22 minutes ago</div><div class="activity-desc">High-velocity coastal wind simulation for crop testing</div></div>
        <div class="activity"><div class="activity-title">Irrigation Stop</div><div class="activity-time">2 hours ago</div><div class="activity-desc">Wind stress irrigation protocol completed</div></div>
        <div class="activity"><div class="activity-title">Vent Close</div><div class="activity-time">4 hours ago</div><div class="activity-desc">Controlled environment phase for plant recovery</div></div>
      </div>

      <!-- Sensor Readings -->
      <div class="section">
        <h3>📑 Recent Sensor Readings</h3>
        <table>
          <tr><th>SENSOR</th><th>VALUE</th><th>TIME</th><th>STATUS</th></tr>
          <tr><td>🌡 Temperature Sensor</td><td>31.8°C</td><td>5 minutes ago</td><td><span class="normal">NORMAL</span></td></tr>
          <tr><td>💧 Humidity Sensor</td><td>57.9%</td><td>5 minutes ago</td><td><span class="normal">NORMAL</span></td></tr>
          <tr><td>🌡 Temperature Sensor</td><td>32.4°C</td><td>10 minutes ago</td><td><span class="normal">NORMAL</span></td></tr>
        </table>
      </div>

      <!-- Charts -->
      <div class="section">
        <h3>📉 Sensor Readings Charts</h3>
        <div class="chart-box"><div class="chart-title">🌡 Temperature (Threshold: 35°C)</div><canvas id="tempChart" height="100"></canvas></div>
        <div class="chart-box"><div class="chart-title">💧 Humidity (Threshold: 60%)</div><canvas id="humChart" height="100"></canvas></div>
      </div>

    </div>
  </div>

  <script>
    function toggleDetails() {
      const section = document.getElementById("detailsSection");
      if (section.style.display === "none") {
        section.style.display = "block";
        loadCharts();
      } else {
        section.style.display = "none";
      }
    }

    let chartsLoaded = false;
    function loadCharts() {
      if (chartsLoaded) return;
      chartsLoaded = true;

      const ctx1 = document.getElementById('tempChart').getContext('2d');
      new Chart(ctx1, {
        type: 'line',
        data: {
          labels: ['10:51 PM','01:51 AM','04:51 AM','07:51 AM','10:51 AM','01:51 PM','04:51 PM','07:51 PM'],
          datasets: [{
            label: 'Temperature (°C)',
            data: [30, 28, 34, 31, 22, 29, 27, 33],
            borderColor: 'rgba(239,68,68,1)',
            backgroundColor: 'rgba(239,68,68,0.2)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(239,68,68,1)'
          },{
            label: 'Threshold',
            data: [35,35,35,35,35,35,35,35],
            borderColor: 'rgba(239,68,68,0.6)',
            borderDash: [5,5],
            fill: false,
            pointRadius: 0
          }]
        },
        options: { responsive: true, plugins:{legend:{position:'top'}} }
      });

      const ctx2 = document.getElementById('humChart').getContext('2d');
      new Chart(ctx2, {
        type: 'line',
        data: {
          labels: ['10:51 PM','01:51 AM','04:51 AM','07:51 AM','10:51 AM','01:51 PM','04:51 PM','07:51 PM'],
          datasets: [{
            label: 'Humidity (%)',
            data: [65, 60, 78, 72, 55, 70, 62, 74],
            borderColor: 'rgba(37,99,235,1)',
            backgroundColor: 'rgba(37,99,235,0.2)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: 'rgba(37,99,235,1)'
          },{
            label: 'Threshold',
            data: [60,60,60,60,60,60,60,60],
            borderColor: 'rgba(37,99,235,0.6)',
            borderDash: [5,5],
            fill: false,
            pointRadius: 0
          }]
        },
        options: { responsive: true, plugins:{legend:{position:'top'}} }
      });
    }
  </script>
</body>
</html>
