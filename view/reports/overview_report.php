<?php
// view/reports/overview_report.php
?>

<!-- Summary Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>Total Citizens</h3>
    <p class="stat-number"><?= number_format($citizenStats['total_citizens'] ?? 0) ?></p>
    <p class="stat-detail">Registered in system</p>
  </div>
  
  <div class="stat-card">
    <h3>Health Cases</h3>
    <p class="stat-number"><?= number_format(count($illnessStats ?? [])) ?></p>
    <p class="stat-detail">Illness records</p>
  </div>
  
  <div class="stat-card">
    <h3>Appointments</h3>
    <p class="stat-number"><?= number_format($appointmentStats['total_appointments'] ?? 0) ?></p>
    <p class="stat-detail">Total scheduled</p>
  </div>
  
  <div class="stat-card">
    <h3>Inventory Items</h3>
    <p class="stat-number"><?= number_format($inventoryStats['total_items'] ?? 0) ?></p>
    <p class="stat-detail">In stock</p>
  </div>
</div>

<!-- Demographics Section -->
<div class="report-section">
  <h2>Demographics Overview</h2>
  
  <div class="chart-grid">
    <div class="chart-container">
      <h3>Gender Distribution</h3>
      <canvas id="genderChart"></canvas>
    </div>
    
    <div class="chart-container">
      <h3>Age Groups</h3>
      <canvas id="ageChart"></canvas>
    </div>
  </div>
  
  <table class="report-table">
    <thead>
      <tr>
        <th>Category</th>
        <th>Count</th>
        <th>Percentage</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Male</td>
        <td><?= number_format($citizenStats['male_count'] ?? 0) ?></td>
        <td><?= $citizenStats['total_citizens'] > 0 ? round(($citizenStats['male_count'] / $citizenStats['total_citizens']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td>Female</td>
        <td><?= number_format($citizenStats['female_count'] ?? 0) ?></td>
        <td><?= $citizenStats['total_citizens'] > 0 ? round(($citizenStats['female_count'] / $citizenStats['total_citizens']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td>Minors (0-17)</td>
        <td><?= number_format($citizenStats['minors'] ?? 0) ?></td>
        <td><?= $citizenStats['total_citizens'] > 0 ? round(($citizenStats['minors'] / $citizenStats['total_citizens']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td>Adults (18-59)</td>
        <td><?= number_format($citizenStats['adults'] ?? 0) ?></td>
        <td><?= $citizenStats['total_citizens'] > 0 ? round(($citizenStats['adults'] / $citizenStats['total_citizens']) * 100, 1) : 0 ?>%</td>
      </tr>
      <tr>
        <td>Seniors (60+)</td>
        <td><?= number_format($citizenStats['seniors'] ?? 0) ?></td>
        <td><?= $citizenStats['total_citizens'] > 0 ? round(($citizenStats['seniors'] / $citizenStats['total_citizens']) * 100, 1) : 0 ?>%</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Citizens by Purok -->
<div class="report-section">
  <h2>Citizens by Purok</h2>
  <table class="report-table">
    <thead>
      <tr>
        <th>Purok</th>
        <th>Number of Citizens</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($citizensByPurok)): ?>
        <?php foreach ($citizensByPurok as $purok): ?>
          <tr>
            <td><?= htmlspecialchars($purok['purokName']) ?></td>
            <td><?= number_format($purok['citizen_count']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="2">No data available</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Health Cases -->
<div class="report-section">
  <h2>Top Health Cases</h2>
  <table class="report-table">
    <thead>
      <tr>
        <th>Illness</th>
        <th>Cases</th>
        <th>Purok</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($illnessDetailedStats)): ?>
        <?php 
        $topCases = array_slice($illnessDetailedStats, 0, 10);
        foreach ($topCases as $illness): 
        ?>
          <tr>
            <td><?= htmlspecialchars($illness['illness_name']) ?></td>
            <td><?= number_format($illness['case_count']) ?></td>
            <td><?= htmlspecialchars($illness['purokName']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3">No health cases recorded</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Inventory Status -->
<div class="report-section">
  <h2>Inventory Status</h2>
  <div class="stats-grid">
    <div class="stat-card">
      <h4>In Stock</h4>
      <p class="stat-number"><?= $inventoryStats['in_stock'] ?? 0 ?></p>
    </div>
    <div class="stat-card warning">
      <h4>Low Stock</h4>
      <p class="stat-number"><?= $inventoryStats['low_stock'] ?? 0 ?></p>
    </div>
    <div class="stat-card danger">
      <h4>Out of Stock</h4>
      <p class="stat-number"><?= $inventoryStats['out_stock'] ?? 0 ?></p>
    </div>
  </div>
</div>

<script>
// Gender Distribution Chart
const genderCtx = document.getElementById('genderChart');
new Chart(genderCtx, {
  type: 'pie',
  data: {
    labels: ['Male', 'Female'],
    datasets: [{
      data: [<?= $citizenStats['male_count'] ?? 0 ?>, <?= $citizenStats['female_count'] ?? 0 ?>],
      backgroundColor: ['#3498db', '#e74c3c']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Age Distribution Chart
const ageCtx = document.getElementById('ageChart');
new Chart(ageCtx, {
  type: 'bar',
  data: {
    labels: ['Minors (0-17)', 'Adults (18-59)', 'Seniors (60+)'],
    datasets: [{
      label: 'Number of Citizens',
      data: [<?= $citizenStats['minors'] ?? 0 ?>, <?= $citizenStats['adults'] ?? 0 ?>, <?= $citizenStats['seniors'] ?? 0 ?>],
      backgroundColor: ['#f39c12', '#27ae60', '#8e44ad']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true }
    }
  }
});
</script>