<script defer>
document.addEventListener("DOMContentLoaded", () => {
  // Fetch all dashboard data at once
  fetch('controllers/get_dashboard_data.php')
    .then(res => res.json())
    .then(data => {
      // Update stats cards
      document.getElementById('total-users').textContent = data.stats.total_users;
      document.getElementById('total-illness').textContent = data.stats.total_illness_records;
      document.getElementById('total-citizens').textContent = data.stats.total_citizens;
      document.getElementById('total-pending').textContent = data.stats.total_pending_accounts;
      
      // Initialize pie chart
      initPieChart(data.pie);
      
      // Initialize map
      initMap(data.map);
    })
    .catch(err => {
      console.error('Failed to load dashboard data:', err);
      // Show error message to user
      document.getElementById('total-users').textContent = 'Error';
      document.getElementById('total-illness').textContent = 'Error';
      document.getElementById('total-citizens').textContent = 'Error';
      document.getElementById('total-pending').textContent = 'Error';
    });
});

// Pie Chart Function
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
        backgroundColor: [
          '#ff6384',
          '#36a2eb',
          '#ffcd56',
          '#4bc0c0',
          '#9966ff'
        ],
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

// Map Function
function initMap(purokData) {
  // Initialize the map
  const map = L.map('map').setView([14.900420, 120.514117], 14.5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  
  // Coordinates
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
  
  // Add markers
  purokData.forEach(item => {
    const coord = purokCoords[item.purokName];
    if (!coord) return;
    
    const total = Number(item.dengue) + Number(item.measles) + Number(item.flu) +
                  Number(item.allergies) + Number(item.diarrhea);
    const color = clusterColors[item.cluster % clusterColors.length];
    const radius = Math.min(10 + total * 2, 40);
    
    const popupContent = `
      <b>${item.purokName}</b><br>
      Cluster: <b>${item.cluster}</b><br><br>
      <table border="1" style="border-collapse: collapse; font-size: 13px;">
        <tr><td>Dengue</td><td>${item.dengue}</td></tr>
        <tr><td>Measles</td><td>${item.measles}</td></tr>
        <tr><td>Flu</td><td>${item.flu}</td></tr>
        <tr><td>Allergies</td><td>${item.allergies}</td></tr>
        <tr><td>Diarrhea</td><td>${item.diarrhea}</td></tr>
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
</script>