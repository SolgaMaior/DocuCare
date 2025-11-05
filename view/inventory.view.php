<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Inventory - DocuCare</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/inventory.css">
  <link rel="stylesheet" href="styles/record.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <?php require('partials/sidebar.php'); ?>

  <div class="content">

    <!-- FILTER + SEARCH BAR -->
    <div class="filter-bar">
      <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="<?= $_GET['page'] ?? 'inventory' ?>">
        <!-- Removed paging from here - let it reset to 1 when filtering -->
        
        <select name="category" onchange="this.form.submit()">
          <option value="all" <?= $categoryFilter == 'all' ? 'selected' : '' ?>>All Items</option>
          <option value="medicine" <?= $categoryFilter == 'medicine' ? 'selected' : '' ?>>Medicines</option>
          <option value="equipment" <?= $categoryFilter == 'equipment' ? 'selected' : '' ?>>Equipment</option>
        </select>
        
        <input type="text" name="search" placeholder="Search item..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Search</button>
      </form>
      <?php if ($_GET['page'] === 'inventory_update'): ?>
      <button class="add-btn" onclick="openModal()">+ Add Item</button>
      <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?= $_SESSION['message_type'] ?? 'success' ?>">
        <?= htmlspecialchars($_SESSION['message']) ?>
      </div>
      <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card inventory-card">
      <div class="inventory-main">
        <!-- Inventory Table -->
        <div class="inventory-table-wrap">
          <table class="inventory-table" id="inventoryTable">
            <thead>
              <tr>
                <th class="col-name">Name</th>
                <th class="col-stock" style="text-align: center;">Stock</th>
                <th class="col-status" style="text-align:center;">Status</th>
                <th class="col-category" style="text-align:center;">Category</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($inventory)): ?>
                <?php foreach ($inventory as $item): ?>
                  <?php
                    $statusClass = '';
                    $statusText = '';
                    if ($item['stock'] > 10) {
                      $statusClass = 'in-stock';
                      $statusText = 'In-stock';
                    } elseif ($item['stock'] > 0) {
                      $statusClass = 'low-stock';
                      $statusText = 'Low-stock';
                    } else {
                      $statusClass = 'out-stock';
                      $statusText = 'Out of Stock';
                    }
                  ?>
                  <tr>
                    <td class="col-name"><?= htmlspecialchars($item['name']) ?></td>
                    <td class="col-stock" style="text-align:center;"><?= htmlspecialchars($item['stock']) ?></td>
                    <td class="col-status" style="text-align:center;">
                      <span class="status-pill <?= $statusClass ?>"><?= $statusText ?></span>
                    </td>
                    <td class="col-category" style="text-align:center;">
                      <?= ucfirst(htmlspecialchars($item['category'])) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No items found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <?php if ($totalPages > 1): ?>
            <div class="pagination" >
                <?php if ($page > 1): ?>
                    <a href="?page=inventory&category=<?= urlencode($categoryFilter) ?>&search=<?= urlencode($searchQuery) ?>&paging=<?= $page - 1 ?>" 
                      class="btn btn-outline pagination-btn">← Previous</a>
                <?php else: ?>
                    <button class="btn btn-outline pagination-btn" disabled>← Previous</button>
                <?php endif; ?>

                <span class="page-info">
                    Page <?= $page ?> of <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=inventory&category=<?= urlencode($categoryFilter) ?>&search=<?= urlencode($searchQuery) ?>&paging=<?= $page + 1 ?>" 
                      class="btn btn-outline pagination-btn">Next →</a>
                <?php else: ?>
                    <button class="btn btn-outline pagination-btn" disabled>Next →</button>
                <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right column: statistics -->
        <aside class="inventory-stats">
          <div class="stats-card">
            <h4>Inventory Chart</h4>
            <canvas id="inventoryChart" width="300" height="300"></canvas>
            <div class="stats-actions">
              <button id="updateStocksBtn" class="btn btn-primary" style="height: 2.5rem;">Update Stocks</button>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const ctx = document.getElementById("inventoryChart");
      const inventory = <?php echo json_encode($inventoryforChart); ?>;
      const labels = inventory.map(item => item.name);
      const data = inventory.map(item => parseInt(item.stock));

      const backgroundColors = labels.map(() => {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        return `rgba(${r}, ${g}, ${b}, 0.7)`;
      });

      if (labels.length > 0) {
        new Chart(ctx, {
          type: "pie",
          data: {
            labels: labels,
            datasets: [{ data: data, backgroundColor: backgroundColors }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: "bottom" },
              title: { display: true, text: "Stock Quantities per Item" }
            }
          }
        });
      } else {
        ctx.parentNode.innerHTML = "<p style='text-align:center;'>No data to display.</p>";
      }

      document.getElementById("updateStocksBtn").addEventListener("click", function () {
        window.location.href = "index.php?page=inventory_update";
      });
    });
  </script>

  <?php if (!empty($lowStockItems) && $showLowStockPopup): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const lowStockList = <?php echo json_encode($lowStockItems); ?>;
  const popup = document.createElement("div");
  popup.style.position = "fixed";
  popup.style.top = "20px";
  popup.style.right = "20px";
  popup.style.background = "#f44336";
  popup.style.color = "#fff";
  popup.style.padding = "15px";
  popup.style.borderRadius = "10px";
  popup.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
  popup.style.zIndex = "9999";
  popup.style.maxWidth = "350px";
  popup.style.fontFamily = "Arial, sans-serif";
  popup.style.lineHeight = "1.5";
  popup.innerHTML = `
    <strong>⚠ Low Stock Alert!</strong><br>
    The following items are running low:<br>
    <ul style="margin: 10px 0 0 15px; padding: 0;">
      ${lowStockList.map(item => `<li>${item}</li>`).join('')}
    </ul>
  `;

  document.body.appendChild(popup);

  // Auto-hide after 8 seconds
  setTimeout(() => {
    popup.style.transition = "opacity 0.5s";
    popup.style.opacity = "0";
    setTimeout(() => popup.remove(), 500);
  }, 8000);
});
</script>
<?php endif; ?>

</body>
</html>