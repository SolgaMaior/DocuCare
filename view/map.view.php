<?php
require_once('model/databases/map_data.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Purok Illness Clusters</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <style>
    #map { height: 650px; width: 1000px; margin-top: 15px;}
    body { font-family: Arial, sans-serif; text-align: center; background: #fafafa; display: flex; justify-content: center; align-items: center; flex-direction: column; }
    h2 { color: #333; margin-top: 20px; }
  </style>
</head>
<body>
  <div id="map">

  <script>
    // --- Step 4: Initialize map
    var map = L.map('map').setView([14.900420, 120.514117], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // --- Step 5: Purok coordinates (edit if needed)
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

    // Draw polygon
    L.polygon(barangayBoundary, {
    color: 'blue',       // border color
    weight: 3,           // border thickness
    fillColor: 'blue',   // fill color
    fillOpacity: 0.1     // transparency
    }).addTo(map)
    .bindPopup("Barangay Pinanggalingan");

    // --- Step 6: Color palette for clusters
    const clusterColors = [
        '#ffeda0', // light yellow
        '#feb24c', // orange
        '#fd8d3c', // dark orange
        '#fc4e2a', // red-orange
        '#e31a1c', // red
    ];


    // --- Step 7: Data from PHP
    const purokData = <?php echo json_encode($merged); ?>;

    // --- Step 8: Add markers
    purokData.forEach(item => {
      const coord = purokCoords[item.purokName];
      if (!coord) return;

      // total illness density (sum of all illnesses)
      const total = 
        Number(item.dengue) + 
        Number(item.measles) + 
        Number(item.flu) + 
        Number(item.allergies) + 
        Number(item.diarrhea);

      // choose cluster color
      const color = clusterColors[item.cluster % clusterColors.length];

      // scale radius by density
      const radius = Math.min(10 + total * 2, 40); // limit to avoid too large circles

      // popup details
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

      // create marker
      L.circleMarker(coord, {
        color: color,
        fillColor: color,
        fillOpacity: 0.6 + Math.min(total / 10, 0.4), // higher density → more opaque
        radius: radius
      })
      .bindPopup(popupContent)
      .addTo(map);
    });
  </script>
  </div>
</body>
</html>
