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


  const PUROK_COORDS = {
    "Purok 1": [14.900031, 120.523160],
    "Purok 2": [14.903204, 120.513444],
    "Purok 3": [14.898851, 120.516532],
    "Purok 4": [14.904298, 120.509210],
    "Purok 5": [14.8963136, 120.5095971]
  };

  const CLUSTER_COLORS = [
    '#667eea', // Purple - Cluster 0
    '#f5576c', // Red - Cluster 1
    '#4facfe', // Blue - Cluster 2
    '#43e97b', // Green - Cluster 3
    '#fa709a'  // Pink - Cluster 4
  ];

  let clusterData = {};
  let mapInstance = null;
  let markersLayer = null;

  document.addEventListener("DOMContentLoaded", () => {
    // Fetch all dashboard data at once
    fetch('controllers/get_dashboard_data.php')
      .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
      })
      .then(data => {
        console.log('Dashboard data loaded:', data);
        
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
        
        // Initialize enhanced map with cluster data
        if (data.map && data.map.length > 0) {
          processClusterData(data.map);
          initEnhancedMap(data.map);
        } else {
          console.warn('No map data available');
        }
      })
      .catch(err => {
        console.error('Failed to load dashboard data:', err);
        showError();
      });
  });

  function processClusterData(mapData) {
    clusterData = {};
    
    mapData.forEach(item => {
      const clusterId = item.cluster ?? 0;
      if (!clusterData[clusterId]) {
        clusterData[clusterId] = {
          puroks: [],
          diseases: { dengue: 0, measles: 0, flu: 0, allergies: 0, diarrhea: 0 },
          total: 0
        };
      }
      
      const purokName = item.purokName || item.purok;
      clusterData[clusterId].puroks.push(purokName);
      clusterData[clusterId].diseases.dengue += parseInt(item.dengue || 0);
      clusterData[clusterId].diseases.measles += parseInt(item.measles || 0);
      clusterData[clusterId].diseases.flu += parseInt(item.flu || 0);
      clusterData[clusterId].diseases.allergies += parseInt(item.allergies || 0);
      clusterData[clusterId].diseases.diarrhea += parseInt(item.diarrhea || 0);
    });

    // Calculate totals
    Object.keys(clusterData).forEach(cId => {
      clusterData[cId].total = Object.values(clusterData[cId].diseases).reduce((a, b) => a + b, 0);
    });

    console.log('Cluster data processed:', clusterData);
  }

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

  function initEnhancedMap(purokData) {
    console.log('Initializing enhanced map with data:', purokData);
    
    // Initialize map if not already done
    if (!mapInstance) {
      mapInstance = L.map('map').setView([14.900420, 120.514117], 14.5);
      
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(mapInstance);
      
      // Add barangay boundary
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
        color: '#4fa7e6',
        weight: 3,
        fillColor: '#4fa7e6',
        fillOpacity: 0.1
      }).addTo(mapInstance).bindPopup("<b>Barangay Pinanggalingan</b>");
      
      markersLayer = L.layerGroup().addTo(mapInstance);
    } else {
      // Clear existing markers
      markersLayer.clearLayers();
    }
    
    purokData.forEach(item => {
      const purokName = item.purokName || item.purok;
      const coord = PUROK_COORDS[purokName];
      
      if (!coord) {
        console.warn('No coordinates for:', purokName);
        return;
      }
      
      const total = Number(item.dengue || 0) + Number(item.measles || 0) + 
                    Number(item.flu || 0) + Number(item.allergies || 0) + 
                    Number(item.diarrhea || 0);
      
      // Enhanced cluster display
      const clusterId = item.cluster ?? 0;
      let clusterLabel, color;
      
      if (clusterId === -1) {
        clusterLabel = "Outlier";
        color = "#95a5a6"; // Gray for outliers
      } else {
        clusterLabel = `Cluster ${parseInt(clusterId) + 1}`;
        color = CLUSTER_COLORS[clusterId % CLUSTER_COLORS.length];
      }
      
      // Determine severity
      const severity = item.severity || (total > 50 ? 'high' : total > 20 ? 'medium' : 'low');
      const severityColor = severity === 'high' ? '#fee;color:#c00' : 
                            severity === 'medium' ? '#ffeaa7;color:#d63031' : '#dfe6e9;color:#636e72';
      const severityBadge = `<span style="background:${severityColor};padding:2px 6px;border-radius:3px;font-size:10px;font-weight:600;text-transform:uppercase;">${severity} RISK</span>`;
      
      const radius = Math.max(15, Math.min(10 + total * 1.5, 50));
      
      // Find dominant disease
      const diseases = { 
        Dengue: item.dengue || 0, 
        Measles: item.measles || 0, 
        Flu: item.flu || 0, 
        Allergies: item.allergies || 0, 
        Diarrhea: item.diarrhea || 0 
      };
      const dominantDisease = Object.entries(diseases).reduce((a, b) => a[1] > b[1] ? a : b)[0];
      const dominantCount = diseases[dominantDisease];
      
      const popupContent = `
        <div style="min-width:240px;">
          <div style="background:linear-gradient(135deg, ${color} 0%, ${color}dd 100%);color:white;padding:12px;margin:-10px -10px 10px -10px;border-radius:6px 6px 0 0;">
            <h4 style="margin:0;font-size:16px;font-weight:600;">${purokName}</h4>
            <div style="font-size:12px;opacity:0.9;margin-top:4px;">
              ${clusterLabel} • ${severityBadge}
            </div>
          </div>
          ${dominantCount > 0 ? `<div style="margin:10px 0;padding:8px;background:#f8f9fa;border-radius:4px;">
            <strong style="color:#333;">Dominant: ${dominantDisease}</strong>
            <span style="float:right;font-weight:600;color:${color};">${dominantCount} cases</span>
          </div>` : ''}
          <table style="width:100%;border-collapse:collapse;margin-top:10px;">
            <tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;color:#666;font-size:13px;">Dengue</td><td style="padding:6px 8px;text-align:right;font-weight:600;font-size:13px;">${item.dengue || 0}</td></tr>
            <tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;color:#666;font-size:13px;">Measles</td><td style="padding:6px 8px;text-align:right;font-weight:600;font-size:13px;">${item.measles || 0}</td></tr>
            <tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;color:#666;font-size:13px;">Flu</td><td style="padding:6px 8px;text-align:right;font-weight:600;font-size:13px;">${item.flu || 0}</td></tr>
            <tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;color:#666;font-size:13px;">Allergies</td><td style="padding:6px 8px;text-align:right;font-weight:600;font-size:13px;">${item.allergies || 0}</td></tr>
            <tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;color:#666;font-size:13px;">Diarrhea</td><td style="padding:6px 8px;text-align:right;font-weight:600;font-size:13px;">${item.diarrhea || 0}</td></tr>
            <tr><td style="padding:6px 8px;font-weight:bold;font-size:13px;">Total Cases</td><td style="padding:6px 8px;text-align:right;font-weight:bold;font-size:13px;">${total}</td></tr>
          </table>
        </div>
      `;
      
      const marker = L.circleMarker(coord, {
        color: '#fff',
        weight: 3,
        fillColor: color,
        fillOpacity: 0.7 + Math.min(total / 100, 0.3),
        radius: radius
      }).bindPopup(popupContent);
      
      marker.purokData = { purokName, clusterId, total };
      markersLayer.addLayer(marker);
      
      // Add purok label
      L.marker(coord, {
        icon: L.divIcon({
          className: 'purok-label',
          html: `<div style="color:white;font-weight:bold;text-shadow:2px 2px 4px rgba(0,0,0,0.8);font-size:12px;white-space:nowrap;">${purokName}</div>`,
          iconSize: [100, 20],
          iconAnchor: [50, -radius - 5]
        })
      }).addTo(markersLayer);
    });
    
    // Add cluster legend to map
    addClusterLegendToMap();
  }

  function addClusterLegendToMap() {
    // Remove existing legend if any
    const existingLegend = document.querySelector('.info.legend');
    if (existingLegend) {
      existingLegend.remove();
    }
    
    const legend = L.control({ position: 'bottomright' });
    
    legend.onAdd = function() {
      const div = L.DomUtil.create('div', 'info legend');
      div.style.background = 'white';
      div.style.padding = '15px';
      div.style.borderRadius = '8px';
      div.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
      div.style.maxWidth = '220px';
      div.style.fontSize = '13px';
      
      let html = '<h4 style="margin:0 0 10px 0;font-size:14px;border-bottom:2px solid #4fa7e6;padding-bottom:8px;">Disease Clusters</h4>';
      
      const sortedClusters = Object.keys(clusterData).sort((a, b) => {
        if (a == -1) return 1; // Put outliers last
        if (b == -1) return -1;
        return a - b;
      });
      
      sortedClusters.forEach(clusterId => {
        const data = clusterData[clusterId];
        const color = clusterId == -1 ? '#95a5a6' : CLUSTER_COLORS[clusterId % CLUSTER_COLORS.length];
        const label = clusterId == -1 ? 'Outliers' : `Cluster ${parseInt(clusterId) + 1}`;
        
        // Find top 2 dominant diseases
        const dominant = Object.entries(data.diseases)
          .filter(([_, count]) => count > 0)
          .sort((a, b) => b[1] - a[1])
          .slice(0, 2)
          .map(([name, count]) => `${name} (${count})`)
          .join(', ');
        
        const dominantText = dominant || 'No cases';
        
        html += `
          <div style="display:flex;align-items:center;margin:8px 0;cursor:pointer;" 
              onmouseover="this.style.background='#f8f9fa'" 
              onmouseout="this.style.background='transparent'"
              onclick="highlightCluster(${clusterId})">
            <div style="width:24px;height:24px;background:${color};border-radius:4px;margin-right:10px;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);flex-shrink:0;"></div>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:12px;color:#333;">${label}</div>
              <div style="font-size:10px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${dominantText}">${dominantText}</div>
              <div style="font-size:10px;color:#999;">${data.puroks.join(', ')}</div>
            </div>
          </div>
        `;
      });
      
      div.innerHTML = html;
      return div;
    };
    
    legend.addTo(mapInstance);
  }

  function highlightCluster(clusterId) {
    if (!markersLayer) return;
    
    markersLayer.eachLayer(layer => {
      if (layer.purokData) {
        if (layer.purokData.clusterId == clusterId) {
          layer.setStyle({ weight: 5, color: '#ffd700' });
          if (!layer.isPopupOpen()) {
            layer.openPopup();
          }
        } else {
          layer.setStyle({ weight: 3, color: '#fff' });
          layer.closePopup();
        }
      }
    });
  }

  function showError() {
    ['total-users', 'total-illness', 'total-citizens', 'total-pending'].forEach(id => {
      const elem = document.getElementById(id);
      if (elem) elem.textContent = 'Error';
    });
  }

  function refreshClusters() {
    if (!confirm('This will refresh cluster data and may take a few seconds. Continue?')) return;
    
    const btn = event.target.closest('button');
    if (!btn) return;
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="la la-spinner la-spin"></i> Refreshing...';
    btn.disabled = true;
    
    fetch('controllers/refresh_clusters.php')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('✓ Clusters refreshed successfully!');
          location.reload();
        } else {
          alert('✗ Error: ' + (data.error || 'Unknown error'));
          btn.innerHTML = originalHtml;
          btn.disabled = false;
        }
      })
      .catch(err => {
        console.error('Refresh failed:', err);
        alert('✗ Failed to refresh clusters. Check console for details.');
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      });
  }

  // Make highlightCluster available globally for onclick in legend
  window.highlightCluster = highlightCluster;
  </script>

</body>
</html>