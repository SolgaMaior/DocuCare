<?php
// view/reports/citizens_report.php
?>

<!-- Statistics Summary -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>Total Citizens</h3>
    <p class="stat-number"><?= number_format($citizenStats['total_citizens'] ?? 0) ?></p>
  </div>
  <div class="stat-card">
    <h3>Male</h3>
    <p class="stat-number"><?= number_format($citizenStats['male_count'] ?? 0) ?></p>
  </div>
  <div class="stat-card">
    <h3>Female</h3>
    <p class="stat-number"><?= number_format($citizenStats['female_count'] ?? 0) ?></p>
  </div>
  <div class="stat-card">
    <h3>Seniors</h3>
    <p class="stat-number"><?= number_format($citizenStats['seniors'] ?? 0) ?></p>
  </div>
</div>

<!-- Demographics Charts -->
<div class="report-section">
  <h2>Demographic Analysis</h2>
  
  <div class="chart-grid">
    <div class="chart-container">
      <h3>Civil Status Distribution</h3>
      <canvas id="civilStatusChart"></canvas>
    </div>
    
    <div class="chart-container">
      <h3>Population by Purok</h3>
      <canvas id="purokChart"></canvas>
    </div>
  </div>
</div>

<!-- Citizens List -->
<div class="report-section">
  <h2>Detailed Citizens List</h2>
  <table class="report-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Age</th>
        <th>Sex</th>
        <th>Civil Status</th>
        <th>Occupation</th>
        <th>Purok</th>
        <th>Contact</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($citizensList)): ?>
        <?php foreach ($citizensList as $index => $citizen): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($citizen['full_name']) ?></td>
            <td><?= $citizen['age'] ?></td>
            <td><?= htmlspecialchars($citizen['sex'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($citizen['civilstatus'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($citizen['occupation'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($citizen['purokName'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($citizen['contactnum'] ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">No citizens found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <!-- <p class="table-note">Showing up to 100 records. For complete list, export to CSV.</p> -->
</div>

<!-- Summary by Purok -->
<div class="report-section">
  <h2>Population Distribution by Purok</h2>
  <table class="report-table">
    <thead>
      <tr>
        <th>Purok</th>
        <th>Population</th>
        <th>Percentage</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $total = $citizenStats['total_citizens'] ?? 1;
      if (!empty($citizensByPurok)): 
      ?>
        <?php foreach ($citizensByPurok as $purok): ?>
          <tr>
            <td><?= htmlspecialchars($purok['purokName']) ?></td>
            <td><?= number_format($purok['citizen_count']) ?></td>
            <td><?= round(($purok['citizen_count'] / $total) * 100, 1) ?>%</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="3">No data available</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
// Civil Status Chart
const civilStatusCtx = document.getElementById('civilStatusChart');
new Chart(civilStatusCtx, {
  type: 'doughnut',
  data: {
    labels: ['Single', 'Married', 'Widowed'],
    datasets: [{
      data: [
        <?= $citizenStats['single_count'] ?? 0 ?>, 
        <?= $citizenStats['married_count'] ?? 0 ?>, 
        <?= $citizenStats['widowed_count'] ?? 0 ?>
      ],
      backgroundColor: ['#3498db', '#2ecc71', '#95a5a6']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// Purok Distribution Chart
const purokCtx = document.getElementById('purokChart');
new Chart(purokCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($citizensByPurok ?? [], 'purokName')) ?>,
    datasets: [{
      label: 'Number of Citizens',
      data: <?= json_encode(array_column($citizensByPurok ?? [], 'citizen_count')) ?>,
      backgroundColor: '#3498db'
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
