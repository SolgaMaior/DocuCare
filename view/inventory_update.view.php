<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Update Inventory - DocuCare</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/inventory.css">
  <link rel="stylesheet" href="styles/record.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>


<?php require('partials/sidebar.php'); ?>

<div class="content">

   <!-- Filter + Search + Add -->
    <div class="filter-bar">
      <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="inventory">
        
        <select name="category" onchange="this.form.submit()">
          <option value="all" <?= $categoryFilter == 'all' ? 'selected' : '' ?>>All Items</option>
          <option value="medicine" <?= $categoryFilter == 'medicine' ? 'selected' : '' ?>>Medicines</option>
          <option value="equipment" <?= $categoryFilter == 'equipment' ? 'selected' : '' ?>>Equipment</option>
        </select>
        
        <input type="text" name="search" placeholder="Search item..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Search</button>
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
      <!-- Inventory Table -->
      <div class="inventory-table-wrap">
        <form method="POST" id="updateInventoryForm">
          <table class="inventory-table">
            <thead>
              <tr>
                <th class="col-name">Name</th>
                <th class="col-stock" style="width: 120px; text-align: right; padding-left: 30px;">Stock</th>
                <th class="col-actions" style="width: 110px; text-align: center; padding-right: 30px;">Adjust</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($inventory)): ?>
                <?php foreach($inventory as $item): ?>
                <tr data-id="<?= $item['id'] ?>">
                  <td class="col-name"><?= htmlspecialchars($item['name']) ?></td>
                  <td class="col-stock" style="vertical-align: middle;">
                    <div style="text-align: right; padding-right: 10px;"> 
                        <span class="stock-value" onclick="InventoryApp.enableEdit(this)"><?= $item['stock'] ?></span>
                        <input type="number" class="stock-input" min="0" value="<?= $item['stock'] ?>" 
                               onblur="InventoryApp.saveEdit(this)" 
                               onkeydown="InventoryApp.handleKey(event, this)">
                        <input type="hidden" name="stocks[<?= $item['id'] ?>]" value="<?= $item['stock'] ?>">
                    </div>
                  </td>
                  <td class="col-actions" style="vertical-align: middle;">
                    <div style="display: inline-flex; margin: 0 auto; width: fit-content;">
                      <button type="button" class="btn small" onclick="InventoryApp.decrement(this)">–</button>
                      <button type="button" class="btn small" onclick="InventoryApp.increment(this)">+</button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="3" style="text-align:center;">No items found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </form>
      </div>

      <!-- Right-hand stats column -->
      <aside class="inventory-stats">
        <div class="stats-card">
          <h4>Update Inventory</h4>
          <div class="stats-actions">
            <button type="button" class="btn btn-primary" id="updateStocksBtn" style="height: 2.5rem;">Update Stocks</button>
            <button type="button" class="btn btn-outline" onclick="window.location.href='index.php?page=inventory'">Cancel</button>
          </div>
        </div>
      </aside>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>Add New Item</h3>
    <form method="POST">
      <input type="text" name="new_item_name" placeholder="Item Name" required>
      <select name="new_item_category" required>
        <option value="">Select Category</option>
        <option value="medicine">Medicine</option>
        <option value="equipment">Equipment</option>
      </select>
      <input type="number" name="new_item_stock" placeholder="Initial Stock" min="0" required>
      <button type="submit" style="background:#2e72a5; color:white;">Add Item</button>
    </form>
  </div>
</div>

<script src="model/scripts/inventory_script.js"></script>


</body>
</html>