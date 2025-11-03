<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="styles/dashboard.css">
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <title>Dashboard</title>
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  
  <!-- Bootstrap & Other CSS -->
  <link rel="stylesheet" href="resources/dashboardassets/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i">
  <link rel="stylesheet" href="resources/dashboardassets/css/ready.css">
  <link rel="stylesheet" href="resources/dashboardassets/css/demo.css">
  
  <!-- Preload center map tile -->
  <link rel="preload" as="image" href="https://a.tile.openstreetmap.org/14/13421/7468.png">
</head>

<body>
  <?php require('partials/sidebar.php'); ?>

  <div id="maincontainer">
    <div class="container-fluid">
      <h4 class="page-title">Dashboard</h4>
      <div class="row">
        <div class="col-md-3">
          <div class="card card-stats card-warning">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-users"></i>
                  </div>
                </div>
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Current Users</p>
                    <h4 class="card-title" id="total-users">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card card-stats card-success">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-bar-chart"></i>
                  </div>
                </div>
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Illness Count this Month</p>
                    <h4 class="card-title" id="total-illness">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card card-stats card-danger">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-newspaper-o"></i>
                  </div>
                </div>
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Documented Patients</p>
                    <h4 class="card-title" id="total-citizens">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php if (CURRENT_USER_IS_ADMIN): ?>
        <div class="col-md-3">
          <div class="card card-stats card-primary">
            <div class="card-body">
              <div class="row">
                <div class="col-5">
                  <div class="icon-big text-center">
                    <i class="la la-check-circle"></i>
                  </div>
                </div>
                
                <div class="col-7 d-flex align-items-center">
                  <div class="numbers">
                    <p class="card-category">Pending Accounts</p>
                    <h4 class="card-title" id="total-pending">
                      <span class="spinner-border spinner-border-sm"></span>
                    </h4>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Map and Chart Row -->
      <div class="row">
        <div class="col-md-9">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Map of the Barangay</h4>
              <p class="card-category">
                See the current status in your area
                <?php if (CURRENT_USER_IS_ADMIN): ?>
                  <button onclick="refreshClusters()" class="btn btn-sm btn-primary float-right h-8">
                    <i class="la la-refresh"></i> Refresh Clusters
                  </button>
                <?php endif; ?>
              </p>
            </div>
            <div class="card-body">
              <div class="mapcontainer">
                <div id="map" style="height:50vh; width: 100%; border-radius: 10px; border: 3px solid #4fa7e6;"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="card">
            <div class="card-header">
              <h4 class="card-title">Citizen Distribution</h4>
            </div>
            <div class="card-body">
              <canvas id="citizenChart" width="400" height="400"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Load JavaScript Libraries -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <!-- Dashboard Logic -->
  <script>
  document.addEventListener("DOMContentLoaded", () => {
    // Fetch all dashboard data at once
    fetch('controllers/get_dashboard_data.php')
      .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
      })
      .then(data => {
        console.log('Dashboard data loaded:', data); // Debug log
        
        // Update stats cards
        if (data.stats) {
          document.getElementById('total-users').textContent = data.stats.total_users || '0';
          document.getElementById('total-illness').textContent = data.stats.total_illness_records || '0';
          document.getElementById('total-citizens').textContent = data.stats.total_citizens || '0';
          
          const pendingElem = document.getElementById('total-pending');
          if (pendingElem) {
            pendingElem.textContent = data.stats.total_pending_accounts || '0';
          }
        }

        
        // Initialize pie chart
        if (data.pie && data.pie.length > 0) {
          initPieChart(data.pie);
        }
        
        // Initialize map with cached cluster data
        if (data.map && data.map.length > 0) {
          initMap(data.map);
        }
      })
      .catch(err => {
        console.error('Failed to load dashboard data:', err);
        showError();
      });
  });

  function initPieChart(pieData) {
    const labels = pieData.map(item => item.purokName);
    const values = pieData.map(item => item.citizen_count);
    
    new Chart(document.getElementById('citizenChart'), {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          label: 'Citizens per Purok',
          data: values,
          backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: {
            display: true,
            text: 'Citizen Distribution by Purok'
          }
        }
      }
    });
  }

  function initMap(purokData) {
    console.log('Initializing map with data:', purokData); // Debug log
    
    const map = L.map('map').setView([14.900420, 120.514117], 14.5);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    
    const purokCoords = {
      "Purok 1": [14.900031, 120.523160],
      "Purok 2": [14.903204, 120.513444],
      "Purok 3": [14.898851, 120.516532],
      "Purok 4": [14.904298, 120.509210],
      "Purok 5": [14.8963136, 120.5095971]
    };
    
    const barangayBoundary = [
      [14.909862, 120.514674],
      [14.901924, 120.529105],
      [14.895253, 120.524170],
      [14.895343, 120.517954],
      [14.893587, 120.513358],
      [14.887331, 120.507801],
      [14.888383, 120.507057],
      [14.892092, 120.508517],
      [14.894168, 120.503190],
      [14.895995, 120.502789],
      [14.901133, 120.506443],
      [14.905817, 120.505354],
      [14.906540, 120.507085],
      [14.907648, 120.505284],
      [14.909862, 120.514674],
    ];
    
    L.polygon(barangayBoundary, {
      color: 'blue',
      weight: 3,
      fillColor: 'blue',
      fillOpacity: 0.1
    }).addTo(map).bindPopup("Barangay Pinanggalingan");
    
    const clusterColors = ['#ffeda0', '#feb24c', '#fd8d3c', '#fc4e2a', '#e31a1c'];
    
    purokData.forEach(item => {
      const coord = purokCoords[item.purokName];
      if (!coord) {
        console.warn('No coordinates for:', item.purokName);
        return;
      }
      
      const total = Number(item.dengue || 0) + Number(item.measles || 0) + 
                    Number(item.flu || 0) + Number(item.allergies || 0) + 
                    Number(item.diarrhea || 0);
      // Handle cluster display and coloring
      let clusterLabel;
      let color;

      if (item.cluster === -1) {
        clusterLabel = "No cluster";
        color = "#ffffff"; // white for outliers
      } else {
        const clusterIndex = (item.cluster ?? 0) + 1; // start numbering at 1
        clusterLabel = clusterIndex;
        color = clusterColors[item.cluster % clusterColors.length];
      }

      const radius = Math.min(10 + total * 2, 40);

      const popupContent = `
        <b>${item.purokName}</b><br>
        Cluster: <b>${clusterLabel}</b><br><br>
        <table border="1" style="border-collapse: collapse; font-size: 13px;">
          <tr><td>Dengue</td><td>${item.dengue || 0}</td></tr>
          <tr><td>Measles</td><td>${item.measles || 0}</td></tr>
          <tr><td>Flu</td><td>${item.flu || 0}</td></tr>
          <tr><td>Allergies</td><td>${item.allergies || 0}</td></tr>
          <tr><td>Diarrhea</td><td>${item.diarrhea || 0}</td></tr>
          <tr><td><b>Total</b></td><td><b>${total}</b></td></tr>
        </table>
      `;
      
      L.circleMarker(coord, {
        color: color,
        fillColor: color,
        fillOpacity: 0.6 + Math.min(total / 10, 0.4),
        radius: radius
      }).bindPopup(popupContent).addTo(map);
    });
  }

  function showError() {
    ['total-users', 'total-illness', 'total-citizens', 'total-pending'].forEach(id => {
      const elem = document.getElementById(id);
      if (elem) elem.textContent = 'Error';
    });
  }

  function refreshClusters() {
    if (!confirm('This will refresh cluster data. Continue?')) return;
    
    fetch('controllers/refresh_clusters.php')
      .then(res => res.json())
      .then(data => {
        alert('Clusters refreshed successfully!');
        location.reload();
      })
      .catch(err => {
        console.error('Refresh failed:', err);
        alert('Failed to refresh clusters. Check console for details.');
      });
  }
  </script>

</body>
</html>