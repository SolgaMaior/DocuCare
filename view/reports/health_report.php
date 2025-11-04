<?php
// view/reports/health_report.php
?>

<!-- Health Statistics -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>Total Cases</h3>
    <p class="stat-number"><?= number_format(array_sum(array_column($illnessStats ?? [], 'case_count'))) ?></p>
  </div>
  <div class="stat-card">
    <h3>Total Appointments</h3>
    <p class="stat-number"><?= number_format($appointmentStats['total_appointments'] ?? 0) ?></p>
  </div>
  <div class="stat-card warning">
    <h3>Pending</h3>
    <p class="stat-number"><?= number_format($appointmentStats['pending'] ?? 0) ?></p>
  </div>
  <div class="stat-card success">
    <h3>Completed</h3>
    <p class="stat-number"><?= number_format($appointmentStats['completed'] ?? 0) ?></p>
  </div>
</div>

<!-- Illness Distribution -->
<div class="report-section">
  <h2>Disease/Illness Distribution</h2>
  
  <div class="chart-container">
    <canvas id="illnessChart"></canvas>
  </div>
  
  <table class="report-table">
    <thead>
      <tr>
        <th>Rank</th>
        <th>Illness Name</th>
        <th>Number of Cases</th>
        <th>Purok</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($illnessStats)): ?>
        <?php foreach ($illnessStats as $index => $illness): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($illness['illness_name']) ?></td>
            <td><?= number_format($illness['case_count']) ?></td>
            <td><?= htmlspecialchars($illness['purokName']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">No illness records found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Illness Trends -->
<?php if (!empty($illnessTrends)): ?>
<div class="report-section">
  <h2>Illness Trends (Last 6 Months)</h2>
  
  <div class="chart-container">
    <canvas id="trendsChart"></canvas>
  </div>
  
  <table class="report-table">
    <thead>
      <tr>
        <th>Month</th>
        <th>Illness</th>
        <th>Cases</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($illnessTrends as $trend): ?>
        <tr>
          <td><?= date('F Y', strtotime($trend['month'] . '-01')) ?></td>
          <td><?= htmlspecialchars($trend['illness_name']) ?></td>
          <td><?= number_format($trend['count']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- Appointment Status -->
<div class="report-section">
  <h2>Appointment Statistics</h2>
  
  <div class="chart-grid">
    <div class="chart-container">
      <h3>Appointment Status Distribution</h3>
      <canvas id="appointmentChart"></canvas>
    </div>
    
    <div class="stats-list">
      <div class="stat-item">
        <span class="stat-label">Pending:</span>
        <span class="stat-value"><?= number_format($appointmentStats['pending'] ?? 0) ?></span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Approved:</span>
        <span class="stat-value"><?= number_format($appointmentStats['approved'] ?? 0) ?></span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Completed:</span>
        <span class="stat-value"><?= number_format($appointmentStats['completed'] ?? 0) ?></span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Cancelled:</span>
        <span class="stat-value"><?= number_format($appointmentStats['cancelled'] ?? 0) ?></span>
      </div>
    </div>
  </div>
</div>

<script>
// Illness Distribution Chart
const illnessCtx = document.getElementById('illnessChart');
const illnessData = <?= json_encode($illnessStats ?? []) ?>;
const topIllnesses = illnessData.slice(0, 8);

new Chart(illnessCtx, {
  type: 'bar',
  data: {
    labels: topIllnesses.map(i => i.illness_name),
    datasets: [{
      label: 'Number of Cases',
      data: topIllnesses.map(i => i.case_count),
      backgroundColor: '#e74c3c'
    }]
  },
  options: {
    responsive: true,
    indexAxis: 'y',
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'Top 8 Illnesses' }
    },
    scales: {
      x: { beginAtZero: true }
    }
  }
});

// Illness Trends Chart
<?php if (!empty($illnessTrends)): ?>
const trendsCtx = document.getElementById('trendsChart');
const trendsData = <?= json_encode($illnessTrends) ?>;

// Group by month
const monthlyData = {};
trendsData.forEach(item => {
  if (!monthlyData[item.month]) {
    monthlyData[item.month] = 0;
  }
  monthlyData[item.month] += parseInt(item.count);
});

const months = Object.keys(monthlyData).sort();
const counts = months.map(m => monthlyData[m]);

new Chart(trendsCtx, {
  type: 'line',
  data: {
    labels: months.map(m => {
      const date = new Date(m + '-01');
      return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    }),
    datasets: [{
      label: 'Total Cases',
      data: counts,
      borderColor: '#3498db',
      backgroundColor: 'rgba(52, 152, 219, 0.1)',
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: true }
    },
    scales: {
      y: { beginAtZero: true }
    }
  }
});
<?php endif; ?>

// Appointment Status Chart
const appointmentCtx = document.getElementById('appointmentChart');
new Chart(appointmentCtx, {
  type: 'pie',
  data: {
    labels: ['Pending', 'Approved', 'Completed', 'Cancelled'],
    datasets: [{
      data: [
        <?= $appointmentStats['pending'] ?? 0 ?>,
        <?= $appointmentStats['approved'] ?? 0 ?>,
        <?= $appointmentStats['completed'] ?? 0 ?>,
        <?= $appointmentStats['cancelled'] ?? 0 ?>
      ],
      backgroundColor: ['#f39c12', '#3498db', '#27ae60', '#e74c3c']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});
</script>