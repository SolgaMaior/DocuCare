<?php
// view/reports/inventory_report.php
?>

<!-- Inventory Statistics -->
<div class="stats-grid">
  <div class="stat-card">
    <h3>Total Items</h3>
    <p class="stat-number"><?= number_format($inventoryStats['total_items'] ?? 0) ?></p>
  </div>
  <div class="stat-card">
    <h3>Total Stock Units</h3>
    <p class="stat-number"><?= number_format($inventoryStats['total_stock'] ?? 0) ?></p>
  </div>
  <div class="stat-card success">
    <h3>In Stock</h3>
    <p class="stat-number"><?= number_format($inventoryStats['in_stock'] ?? 0) ?></p>
  </div>
  <div class="stat-card warning">
    <h3>Low Stock</h3>
    <p class="stat-number"><?= number_format($inventoryStats['low_stock'] ?? 0) ?></p>
  </div>
  <div class="stat-card danger">
    <h3>Out of Stock</h3>
    <p class="stat-number"><?= number_format($inventoryStats['out_stock'] ?? 0) ?></p>
  </div>
</div>

<!-- Status Distribution -->
<div class="report-section">
  <h2>Inventory Status Overview</h2>
  
  <div class="chart-grid">
    <div class="chart-container">
      <h3>Stock Status Distribution</h3>
      <canvas id="statusChart"></canvas>
    </div>
    
    <div class="chart-container">
      <h3>Category Distribution</h3>
      <canvas id="categoryChart"></canvas>
    </div>
  </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStockItems)): ?>
<div class="report-section alert-section">
  <h2>Low Stock Alert</h2>
  <p class="section-subtitle">Items requiring immediate attention</p>
  
  <table class="report-table">
    <thead>
      <tr>
        <th>Item Name</th>
        <th>Category</th>
        <th>Current Stock</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($lowStockItems as $item): ?>
        <tr class="<?= $item['status'] === 'out-stock' ? 'danger-row' : 'warning-row' ?>">
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= ucfirst(htmlspecialchars($item['category'])) ?></td>
          <td><?= number_format($item['stock']) ?></td>
          <td>
            <span class="status-badge <?= $item['status'] ?>">
              <?= $item['status'] === 'out-stock' ? 'Out of Stock' : 'Low Stock' ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="report-section success-section">
  <h2>✓ All Items Adequately Stocked</h2>
  <p>No items require immediate restocking.</p>
</div>
<?php endif; ?>

<!-- Recommendations -->
<div class="report-section">
  <h2>Recommendations</h2>
  <div class="recommendations-list">
    <?php if ($inventoryStats['out_stock'] > 0): ?>
      <div class="recommendation danger">
        <strong>Urgent:</strong> <?= $inventoryStats['out_stock'] ?> item(s) are completely out of stock. Immediate restocking required.
      </div>
    <?php endif; ?>
    
    <?php if ($inventoryStats['low_stock'] > 0): ?>
      <div class="recommendation warning">
        <strong>Warning:</strong> <?= $inventoryStats['low_stock'] ?> item(s) have low stock levels. Plan for restocking soon.
      </div>
    <?php endif; ?>
    
    <?php if ($inventoryStats['out_stock'] == 0 && $inventoryStats['low_stock'] == 0): ?>
      <div class="recommendation success">
        <strong>Good:</strong> All inventory items are adequately stocked. Continue regular monitoring.
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Summary Statistics -->
<div class="report-section">
  <h2>Category Breakdown</h2>
  <table class="report-table">
    <thead>
      <tr>
        <th>Category</th>
        <th>Number of Items</th>
        <th>Percentage</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Medicines</td>
        <td><?= number_format($inventoryStats['medicines'] ?? 0) ?></td>
        <td>
          <?php 
          $total = $inventoryStats['total_items'] ?? 1;
          echo round(($inventoryStats['medicines'] / $total) * 100, 1); 
          ?>%
        </td>
      </tr>
      <tr>
        <td>Equipment</td>
        <td><?= number_format($inventoryStats['equipment'] ?? 0) ?></td>
        <td>
          <?php echo round(($inventoryStats['equipment'] / $total) * 100, 1); ?>%
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
// Stock Status Chart
const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: ['In Stock', 'Low Stock', 'Out of Stock'],
    datasets: [{
      data: [
        <?= $inventoryStats['in_stock'] ?? 0 ?>,
        <?= $inventoryStats['low_stock'] ?? 0 ?>,
        <?= $inventoryStats['out_stock'] ?? 0 ?>
      ],
      backgroundColor: ['#27ae60', '#f39c12', '#e74c3c']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: true, text: 'Stock Status' }
    }
  }
});

// Category Distribution Chart
const categoryCtx = document.getElementById('categoryChart');
new Chart(categoryCtx, {
  type: 'pie',
  data: {
    labels: ['Medicines', 'Equipment'],
    datasets: [{
      data: [
        <?= $inventoryStats['medicines'] ?? 0 ?>,
        <?= $inventoryStats['equipment'] ?? 0 ?>
      ],
      backgroundColor: ['#3498db', '#9b59b6']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      title: { display: true, text: 'Inventory by Category' }
    }
  }
});
</script>
