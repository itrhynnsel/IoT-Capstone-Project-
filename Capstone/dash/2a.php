<?php 
// dashboard.php (merged version of 2a.php and 2b.php)
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Greenhouse B - Seaweed Cultivation</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f6fa; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; margin: 0; padding: 20px;">

  <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:100%; max-width:1100px; padding:20px; box-sizing:border-box;">
    <div style="font-weight:bold; font-size:16px; margin-bottom:15px;">Greenhouse B - Seaweed Cultivation</div>
    
    <div style="display:flex; gap:20px; align-items:center;">
      <div style="flex:1; display:flex; justify-content:center;">
        <img src="2seaweed.png" alt="Greenhouse" width="150">
      </div>
      <div style="flex:1; display:grid; grid-template-columns:1fr 1fr; gap:15px;">
        <div style="background:#f9f9f9; border-radius:10px; text-align:center; padding:10px; font-size:14px;">
          <img src="1temperature.png" alt="Temp" style="width:30px; height:30px; margin-bottom:5px;">
          <div style="color:#1c9dd9; font-weight:bold; font-size:18px;">50°C</div>
          <div>TEMPERATURE</div>
        </div>
        <div style="background:#f9f9f9; border-radius:10px; text-align:center; padding:10px; font-size:14px;">
          <img src="1humidity.png" alt="Humidity" style="width:30px; height:30px; margin-bottom:5px;">
          <div style="color:#f59e0b; font-weight:bold; font-size:18px;">58%</div>
          <div>HUMIDITY</div>
        </div>
        <div style="background:#f9f9f9; border-radius:10px; text-align:center; padding:10px; font-size:14px;">
          <img src="1ventilation.png" alt="Ventilation" style="width:30px; height:30px; margin-bottom:5px;">
          <div style="color:#16a34a; font-weight:bold; font-size:18px;">Open</div>
          <div>VENTILATION</div>
        </div>
        <div style="background:#f9f9f9; border-radius:10px; text-align:center; padding:10px; font-size:14px;">
          <img src="1irrigation.png" alt="Irrigation" style="width:30px; height:30px; margin-bottom:5px;">
          <div style="color:#9ca3af; font-weight:bold; font-size:18px;">Inactive</div>
          <div>IRRIGATION</div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <p style="margin-top:15px; font-size:14px; color:#444; line-height:1.5;">
      Unique <b>marine aquaculture facility</b> for sustainable seaweed production. Simulates ocean 
      conditions with controlled tidal systems. <i>Carbon sequestration research</i> exploring seaweed 
      role in climate solutions.
    </p>

    <div style="margin-top:15px; text-align:center;">
      <button onclick="toggleDetails()" style="border:1px solid #16a34a; background:white; color:#16a34a; font-weight:bold; padding:10px 20px; border-radius:8px; cursor:pointer; transition:0.3s;">VIEW MORE DETAILS</button>
    </div>

    <!-- Hidden details section -->
    <div id="details" style="display:none; margin-top:20px;">

      <!-- Statistics -->
      <div style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <h3 style="margin:0 0 15px; font-size:15px; font-weight:bold; color:#444;">📊 Statistics</h3>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:15px;">
          <div style="background:#f9f9f9; border-radius:12px; padding:15px; text-align:center;">
            <div style="font-size:22px; font-weight:bold; margin:5px 0;">2</div>
            <div style="font-size:13px; color:#777;">MCUs</div>
          </div>
          <div style="background:#f9f9f9; border-radius:12px; padding:15px; text-align:center;">
            <div style="font-size:22px; font-weight:bold; margin:5px 0;">12</div>
            <div style="font-size:13px; color:#777;">SENSORS</div>
          </div>
          <div style="background:#f9f9f9; border-radius:12px; padding:15px; text-align:center;">
            <div style="font-size:22px; font-weight:bold; margin:5px 0;">1650</div>
            <div style="font-size:13px; color:#777;">READINGS</div>
          </div>
          <div style="background:#f9f9f9; border-radius:12px; padding:15px; text-align:center;">
            <div style="font-size:22px; font-weight:bold; margin:5px 0;">29</div>
            <div style="font-size:13px; color:#777;">ACTIVITIES</div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <h3 style="margin:0 0 15px; font-size:15px; font-weight:bold; color:#444;">🔄 Recent Activities</h3>
        <div style="border-bottom:1px solid #eee; padding:12px 0;">
          <div style="font-weight:bold; font-size:14px;">Vent Open</div>
          <div style="font-size:12px; color:#888;">22 minutes ago</div>
          <div style="font-size:13px; color:#555;">High-velocity coastal wind simulation for crop testing</div>
        </div>
        <div style="border-bottom:1px solid #eee; padding:12px 0;">
          <div style="font-weight:bold; font-size:14px;">Irrigation Stop</div>
          <div style="font-size:12px; color:#888;">2 hours ago</div>
          <div style="font-size:13px; color:#555;">Wind stress irrigation protocol completed</div>
        </div>
        <div style="padding:12px 0;">
          <div style="font-weight:bold; font-size:14px;">Vent Close</div>
          <div style="font-size:12px; color:#888;">4 hours ago</div>
          <div style="font-size:13px; color:#555;">Controlled environment phase for plant recovery</div>
        </div>
      </div>

      <!-- Recent Sensor Readings -->
      <div style="background:#fff; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <h3 style="margin:0 0 15px; font-size:15px; font-weight:bold; color:#444;">📑 Recent Sensor Readings</h3>
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
          <tr><th style="text-align:left; padding:12px; border-bottom:1px solid #eee; font-size:12px; color:#666;">SENSOR</th>
              <th style="text-align:left; padding:12px; border-bottom:1px solid #eee; font-size:12px; color:#666;">VALUE</th>
              <th style="text-align:left; padding:12px; border-bottom:1px solid #eee; font-size:12px; color:#666;">TIME</th>
              <th style="text-align:left; padding:12px; border-bottom:1px solid #eee; font-size:12px; color:#666;">STATUS</th></tr>
          <tr>
            <td style="padding:12px; border-bottom:1px solid #eee;">🌡 Temperature Sensor</td>
            <td style="padding:12px; border-bottom:1px solid #eee;">31.8°C</td>
            <td style="padding:12px; border-bottom:1px solid #eee;">5 minutes ago</td>
            <td style="padding:12px; border-bottom:1px solid #eee;"><span style="background:#e6f9ed; color:#16a34a; font-weight:bold; padding:4px 10px; border-radius:15px; font-size:12px;">NORMAL</span></td>
          </tr>
          <tr>
            <td style="padding:12px; border-bottom:1px solid #eee;">💧 Humidity Sensor</td>
            <td style="padding:12px; border-bottom:1px solid #eee;">57.9%</td>
            <td style="padding:12px; border-bottom:1px solid #eee;">5 minutes ago</td>
            <td style="padding:12px; border-bottom:1px solid #eee;"><span style="background:#e6f9ed; color:#16a34a; font-weight:bold; padding:4px 10px; border-radius:15px; font-size:12px;">NORMAL</span></td>
          </tr>
          <tr>
            <td style="padding:12px;">🌡 Temperature Sensor</td>
            <td style="padding:12px;">32.4°C</td>
            <td style="padding:12px;">10 minutes ago</td>
            <td style="padding:12px;"><span style="background:#e6f9ed; color:#16a34a; font-weight:bold; padding:4px 10px; border-radius:15px; font-size:12px;">NORMAL</span></td>
          </tr>
        </table>
      </div>

      <!-- Charts -->
      <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
        <h3 style="margin:0 0 15px; font-size:15px; font-weight:bold; color:#444;">📉 Sensor Readings Charts</h3>
        <div style="margin-bottom:20px;">
          <div style="font-size:14px; margin-bottom:10px; font-weight:bold; color:#555;">🌡 Temperature (Threshold: 35°C)</div>
          <canvas id="tempChart" height="100"></canvas>
        </div>
        <div>
          <div style="font-size:14px; margin-bottom:10px; font-weight:bold; color:#555;">💧 Humidity (Threshold: 60%)</div>
          <canvas id="humChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>

<script>
function toggleDetails(){
  const d = document.getElementById("details");
  if(d.style.display === "none"){ 
    d.style.display = "block"; 
  } else { 
    d.style.display = "none"; 
  }
}

// Draw charts after expand
function initCharts(){
  if(window.chartsLoaded) return; // prevent redraw
  window.chartsLoaded = true;

  const ctx1 = document.getElementById('tempChart').getContext('2d');
  new Chart(ctx1,{
    type:'line',
    data:{labels:['10:51 PM','01:51 AM','04:51 AM','07:51 AM','10:51 AM','01:51 PM','04:51 PM','07:51 PM'],
      datasets:[{label:'Temperature (°C)', data:[30,28,34,31,22,29,27,33],
        borderColor:'rgba(239,68,68,1)', backgroundColor:'rgba(239,68,68,0.2)', fill:true, tension:0.4, pointBackgroundColor:'rgba(239,68,68,1)'},
        {label:'Threshold', data:[35,35,35,35,35,35,35,35], borderColor:'rgba(239,68,68,0.6)', borderDash:[5,5], fill:false, pointRadius:0}]},
    options:{responsive:true, plugins:{legend:{position:'top'}}}
  });

  const ctx2 = document.getElementById('humChart').getContext('2d');
  new Chart(ctx2,{
    type:'line',
    data:{labels:['10:51 PM','01:51 AM','04:51 AM','07:51 AM','10:51 AM','01:51 PM','04:51 PM','07:51 PM'],
      datasets:[{label:'Humidity (%)', data:[65,60,78,72,55,70,62,74],
        borderColor:'rgba(37,99,235,1)', backgroundColor:'rgba(37,99,235,0.2)', fill:true, tension:0.4, pointBackgroundColor:'rgba(37,99,235,1)'},
        {label:'Threshold', data:[60,60,60,60,60,60,60,60], borderColor:'rgba(37,99,235,0.6)', borderDash:[5,5], fill:false, pointRadius:0}]},
    options:{responsive:true, plugins:{legend:{position:'top'}}}
  });
}

document.querySelector("button").addEventListener("click", ()=>setTimeout(initCharts,200));
</script>
</body>
</html>
