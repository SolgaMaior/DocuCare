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

    <!-- filter bar -->
    <div class="filter-bar">
      <form method="GET" action="index.php" class="filter-form" id="filterForm" style="display: flex; gap: 0.5rem; align-items: center;">
        <input type="hidden" name="page" value="<?= $_GET['page'] ?? 'inventory' ?>">

        <label for="category" style="font-weight: bold;">Filter:</label>
        <select name="category" id="category" onchange="this.form.submit()">
          <option value="all" <?= $categoryFilter == 'all' ? 'selected' : '' ?>>All Items</option>
          <option value="medicine" <?= $categoryFilter == 'medicine' ? 'selected' : '' ?>>Medicines</option>
          <option value="equipment" <?= $categoryFilter == 'equipment' ? 'selected' : '' ?>>Equipment</option>
        </select>

        <input 
          type="text" 
          name="search" 
          placeholder="Search item..." 
          value="<?= htmlspecialchars($searchQuery) ?>"
          id="searchInput"
          style="flex:1; padding: 0.4rem; border-radius: 6px; border: 1px solid #ccc;"
          pattern="[A-Za-z ]+"
          oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '');"
        >

        <button type="submit" class="btn btn-outline" style="height: 2.5rem;">Search</button>
        <?php if (!empty($searchQuery)): ?>
          <button type="button" class="btn btn-outline" id="clearSearchBtn" style="height: 2.5rem;">Clear</button>
        <?php else: ?>
          <button type="button" class="btn btn-outline" id="clearSearchBtn" style="height: 2.5rem; display: none;">Clear</button>
        <?php endif; ?>
        <script>
          document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("searchInput");
            const clearBtn = document.getElementById("clearSearchBtn");
            const filterForm = document.getElementById("filterForm");

            if (!searchInput || !filterForm) return;

            // Clear button functionality
            if (clearBtn) {
              clearBtn.addEventListener("click", (e) => {
                e.preventDefault();

                // Get current page value from URL
                const urlParams = new URLSearchParams(window.location.search);
                const currentPage = urlParams.get('page') || 'inventory';
                const currentCategory = document.getElementById('category').value;

                // Redirect to page without search parameter
                window.location.href = `index.php?page=${currentPage}&category=${currentCategory}`;
              });
            }

            // Submit on Enter key
            searchInput.addEventListener("keypress", (e) => {
              if (e.key === "Enter") {
                e.preventDefault();
                filterForm.submit();
              }
            });
          });
        </script>
      </form>
    </div>



    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-<?= $_SESSION['message_type'] ?? 'success' ?>">
        <?= htmlspecialchars($_SESSION['message']) ?>
      </div>
      <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="card inventory-card">
      <div class="inventory-main">
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

  // Auto-hide
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