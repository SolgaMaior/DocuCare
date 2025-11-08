const PUROK_COORDS = {
  "Purok 1": [14.900031, 120.52316],
  "Purok 2": [14.903204, 120.513444],
  "Purok 3": [14.898851, 120.516532],
  "Purok 4": [14.904298, 120.50921],
  "Purok 5": [14.8963136, 120.5095971],
};

const CLUSTER_COLORS = [
  "#667eea", "#f5576c", "#4facfe", "#43e97b", "#fa709a",
];

let clusterData = {};
let mapInstance = null;
let markersLayer = null;
const currentDateRange = { start: null, end: null };

document.addEventListener("DOMContentLoaded", () => {
  initializeDateFilter();
  loadDashboardData();
});

function initializeDateFilter() {
  // Set default date range (last 30 days)
  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - 30);
  
  const startInput = document.getElementById('filter-start-date');
  const endInput = document.getElementById('filter-end-date');
  
  if (startInput && endInput) {
    startInput.value = formatDateForInput(startDate);
    endInput.value = formatDateForInput(endDate);
    
    currentDateRange.start = formatDateForInput(startDate);
    currentDateRange.end = formatDateForInput(endDate);
  }
}

function formatDateForInput(date) {
  return date.toISOString().split('T')[0];
}

function loadDashboardData(startDate = null, endDate = null) {
  // Build URL with date parameters
  let url = "controllers/get_dashboard_data.php";
  const params = [];
  
  if (startDate) params.push(`start_date=${startDate}`);
  if (endDate) params.push(`end_date=${endDate}`);
  
  if (params.length > 0) {
    url += '?' + params.join('&');
  }
  
  // Show loading state
  showLoadingState();
  
  fetch(url)
    .then((res) => {
      if (!res.ok) throw new Error("Network response was not ok");
      return res.json();
    })
    .then((data) => {
      console.log("Dashboard data loaded:", data);

      // Update stats cards
      if (data.stats) {
        document.getElementById("total-users").textContent = data.stats.total_users || "0";
        document.getElementById("total-illness").textContent = data.stats.total_illness_records || "0";
        document.getElementById("total-citizens").textContent = data.stats.total_citizens || "0";

        const pendingElem = document.getElementById("total-pending");
        if (pendingElem) {
          pendingElem.textContent = data.stats.total_pending_accounts || "0";
        }
      }

      // Initialize pie chart (only on first load)
      if (data.pie && data.pie.length > 0 && !window.citizenChartInstance) {
        initPieChart(data.pie);
      }

      // Initialize or update map with cluster data
      if (data.map && data.map.length > 0) {
        processClusterData(data.map);
        initEnhancedMap(data.map);
      } else {
        console.warn("No map data available");
      }
    })
    .catch((err) => {
      console.error("Failed to load dashboard data:", err);
      showError();
    });
}

function showLoadingState() {
  // Add loading spinner to map
  const mapEl = document.getElementById('map');
  if (mapEl && !mapEl.querySelector('.map-loading')) {
    const loader = document.createElement('div');
    loader.className = 'map-loading';
    loader.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);z-index:9999;"><i class="la la-spinner la-spin la-2x"></i><div>Loading map data...</div></div>';
    mapEl.appendChild(loader);
  }
}

function removeLoadingState() {
  const loader = document.querySelector('.map-loading');
  if (loader) loader.remove();
}

function processClusterData(mapData) {
  clusterData = {};

  mapData.forEach((item) => {
    const clusterId = item.cluster ?? 0;
    if (!clusterData[clusterId]) {
      clusterData[clusterId] = {
        puroks: [],
        diseases: { dengue: 0, measles: 0, flu: 0, allergies: 0, diarrhea: 0 },
        total: 0,
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

  Object.keys(clusterData).forEach((cId) => {
    clusterData[cId].total = Object.values(clusterData[cId].diseases).reduce((a, b) => a + b, 0);
  });

  console.log("Cluster data processed:", clusterData);
}

function initPieChart(pieData) {
  const labels = pieData.map((item) => item.purokName);
  const values = pieData.map((item) => item.citizen_count);

  window.citizenChartInstance = new Chart(document.getElementById("citizenChart"), {
    type: "pie",
    data: {
      labels: labels,
      datasets: [{
        label: "Citizens per Purok",
        data: values,
        backgroundColor: ["#ff6384", "#36a2eb", "#ffcd56", "#4bc0c0", "#9966ff"],
        borderWidth: 1,
      }],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: "bottom" },
        title: { display: true, text: "Citizen Distribution by Purok" },
      },
    },
  });
}

function initEnhancedMap(purokData) {
  console.log("Initializing enhanced map with data:", purokData);

  if (!mapInstance) {
    mapInstance = L.map("map").setView([14.90042, 120.514117], 14.5);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
    }).addTo(mapInstance);

    const barangayBoundary = [
      [14.909862, 120.514674], [14.901924, 120.529105], [14.895253, 120.52417],
      [14.895343, 120.517954], [14.893587, 120.513358], [14.887331, 120.507801],
      [14.888383, 120.507057], [14.892092, 120.508517], [14.894168, 120.50319],
      [14.895995, 120.502789], [14.901133, 120.506443], [14.905817, 120.505354],
      [14.90654, 120.507085], [14.907648, 120.505284], [14.909862, 120.514674],
    ];

    L.polygon(barangayBoundary, {
      color: "#4fa7e6", weight: 3, fillColor: "#4fa7e6", fillOpacity: 0.1,
    }).addTo(mapInstance).bindPopup("<b>Barangay Santiago</b>");

    markersLayer = L.layerGroup().addTo(mapInstance);
  } else {
    markersLayer.clearLayers();
  }

  purokData.forEach((item) => {
    const purokName = item.purokName || item.purok;
    const coord = PUROK_COORDS[purokName];

    if (!coord) {
      console.warn("No coordinates for:", purokName);
      return;
    }

    const total = Number(item.dengue || 0) + Number(item.measles || 0) + 
                  Number(item.flu || 0) + Number(item.allergies || 0) + 
                  Number(item.diarrhea || 0);

    const clusterId = item.cluster ?? 0;
    let clusterLabel, color;

    if (clusterId === -1) {
      clusterLabel = "Outlier";
      color = "#95a5a6";
    } else {
      clusterLabel = `Cluster ${parseInt(clusterId) + 1}`;
      color = CLUSTER_COLORS[clusterId % CLUSTER_COLORS.length];
    }

    const severity = item.severity || (total > 50 ? "high" : total > 20 ? "medium" : "low");
    const severityColor = severity === "high" ? "#fee;color:#c00" : 
                          severity === "medium" ? "#ffeaa7;color:#d63031" : "#dfe6e9;color:#636e72";
    const severityBadge = `<span style="background:${severityColor};padding:2px 6px;border-radius:3px;font-size:10px;font-weight:600;text-transform:uppercase;">${severity} RISK</span>`;

    const radius = Math.max(15, Math.min(10 + total * 1.5, 50));

    const diseases = {
      Dengue: item.dengue || 0, Measles: item.measles || 0, Flu: item.flu || 0,
      Allergies: item.allergies || 0, Diarrhea: item.diarrhea || 0,
    };
    const dominantDisease = Object.entries(diseases).reduce((a, b) => a[1] > b[1] ? a : b)[0];
    const dominantCount = diseases[dominantDisease];

    const popupContent = `
      <div style="min-width:240px;">
        <div style="background:linear-gradient(135deg, ${color} 0%, ${color}dd 100%);color:white;padding:12px;margin:-5px -5px -5px -5px;border-radius:15px 15px 0 0;">
          <h4 style="margin:0;font-size:16px;font-weight:600;">${purokName}</h4>
          <div style="font-size:12px;opacity:0.9;margin-top:4px;">${clusterLabel} • ${severityBadge}</div>
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
      </div>`;

    const marker = L.circleMarker(coord, {
      color: "#fff", weight: 3, fillColor: color,
      fillOpacity: 0.7 + Math.min(total / 100, 0.3), radius: radius,
    }).bindPopup(popupContent);

    marker.purokData = { purokName, clusterId, total };
    markersLayer.addLayer(marker);

    L.marker(coord, {
      icon: L.divIcon({
        className: "purok-label",
        html: `<div style="color:white;font-weight:bold;text-shadow:2px 2px 4px rgba(0,0,0,0.8);font-size:12px;white-space:nowrap;">${purokName}</div>`,
        iconSize: [100, 20], iconAnchor: [50, -radius - 5],
      }),
    }).addTo(markersLayer);
  });

  removeLoadingState();
  addClusterLegendToMap();
}

function addClusterLegendToMap() {
  const existingLegend = document.querySelector(".info.legend");
  if (existingLegend) existingLegend.remove();

  const legend = L.control({ position: "bottomright" });

  legend.onAdd = function () {
    const div = L.DomUtil.create("div", "info legend");
    div.style.cssText = "background:white;padding:15px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);max-width:220px;font-size:13px;";

    let html = '<h4 style="margin:0 0 10px 0;font-size:14px;border-bottom:2px solid #4fa7e6;padding-bottom:8px;">Illness Clusters</h4>';

    const sortedClusters = Object.keys(clusterData).sort((a, b) => {
      if (a == -1) return 1;
      if (b == -1) return -1;
      return a - b;
    });

    sortedClusters.forEach((clusterId) => {
      const data = clusterData[clusterId];
      const color = clusterId == -1 ? "#95a5a6" : CLUSTER_COLORS[clusterId % CLUSTER_COLORS.length];
      const label = clusterId == -1 ? "Outliers" : `Cluster ${parseInt(clusterId) + 1}`;

      const dominant = Object.entries(data.diseases)
        .filter(([_, count]) => count > 0)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 2)
        .map(([name, count]) => `${name} (${count})`)
        .join(", ");

      const dominantText = dominant || "No cases";

      html += `
        <div style="display:flex;align-items:center;margin:8px 0;cursor:pointer;" 
            onmouseover="this.style.background='#f8f9fa'" 
            onmouseout="this.style.background='transparent'"
            onclick="highlightCluster(${clusterId})">
          <div style="width:24px;height:24px;background:${color};border-radius:4px;margin-right:10px;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.3);flex-shrink:0;"></div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:12px;color:#333;">${label}</div>
            <div style="font-size:10px;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${dominantText}">${dominantText}</div>
            <div style="font-size:10px;color:#999;">${data.puroks.join(", ")}</div>
          </div>
        </div>`;
    });

    div.innerHTML = html;
    return div;
  };

  legend.addTo(mapInstance);
}

function highlightCluster(clusterId) {
  if (!markersLayer) return;

  markersLayer.eachLayer((layer) => {
    if (layer.purokData) {
      if (layer.purokData.clusterId == clusterId) {
        layer.setStyle({ weight: 5, color: "#ffd700" });
        if (!layer.isPopupOpen()) layer.openPopup();
      } else {
        layer.setStyle({ weight: 3, color: "#fff" });
        layer.closePopup();
      }
    }
  });
}

function showError() {
  ["total-users", "total-illness", "total-citizens", "total-pending"].forEach((id) => {
    const elem = document.getElementById(id);
    if (elem) elem.textContent = "Error";
  });
}

function refreshClusters() {
  if (!confirm("This will refresh cluster data and may take a few seconds. Continue?")) return;

  const btn = event.target.closest("button");
  if (!btn) return;

  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<i class="la la-spinner la-spin"></i> Refreshing...';
  btn.disabled = true;

  fetch("controllers/refresh_clusters.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        alert("Clusters refreshed successfully!");
        applyDateFilter(); // Reload with current filter
      } else {
        alert("Error: " + (data.error || "Unknown error"));
      }
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    })
    .catch((err) => {
      console.error("Refresh failed:", err);
      alert("Failed to refresh clusters. Check console for details.");
      btn.innerHTML = originalHtml;
      btn.disabled = false;
    });
}

function applyDateFilter() {
  const startInput = document.getElementById('filter-start-date');
  const endInput = document.getElementById('filter-end-date');
  
  if (!startInput || !endInput) return;
  
  const startDate = startInput.value;
  const endDate = endInput.value;
  
  if (!startDate || !endDate) {
    alert('Please select both start and end dates');
    return;
  }
  
  if (new Date(startDate) > new Date(endDate)) {
    alert('Start date must be before end date');
    return;
  }
  
  currentDateRange.start = startDate;
  currentDateRange.end = endDate;
  
  const filterInfo = document.getElementById('date-filter-info');
  if (filterInfo) {
    filterInfo.textContent = `Showing data from ${startDate} to ${endDate}`;
  }
  
  // Reload data with new date range
  loadDashboardData(startDate, endDate);
}

function resetDateFilter() {
  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - 30);
  
  const startInput = document.getElementById('filter-start-date');
  const endInput = document.getElementById('filter-end-date');
  
  if (startInput && endInput) {
    startInput.value = formatDateForInput(startDate);
    endInput.value = formatDateForInput(endDate);
  }
  
  currentDateRange.start = formatDateForInput(startDate);
  currentDateRange.end = formatDateForInput(endDate);
  
  const filterInfo = document.getElementById('date-filter-info');
  if (filterInfo) {
    filterInfo.textContent = 'Showing last 30 days data';
  }
  
  loadDashboardData(currentDateRange.start, currentDateRange.end);
}

function setQuickDateRange(days) {
  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(startDate.getDate() - days);
  
  const startInput = document.getElementById('filter-start-date');
  const endInput = document.getElementById('filter-end-date');
  
  if (startInput && endInput) {
    startInput.value = formatDateForInput(startDate);
    endInput.value = formatDateForInput(endDate);
  }
  
  applyDateFilter();
}

window.highlightCluster = highlightCluster;
window.refreshClusters = refreshClusters;
window.applyDateFilter = applyDateFilter;
window.resetDateFilter = resetDateFilter;
window.setQuickDateRange = setQuickDateRange;