<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Update Inventory - DocuCare</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/inventory.css">
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>


<?php require('partials/sidebar.php'); ?>

<div class="content">

   <!-- Filter + Search + Add -->
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
                <th class="col-category">Category</th>
                <th class="col-stock">Stock</th>
                <th class="col-actions">Adjust</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($inventory)): ?>
                <?php foreach($inventory as $item): ?>
                <tr data-id="<?= $item['id'] ?>">
                  <td class="col-name"><?= htmlspecialchars($item['name']) ?></td>
                  <td class="col-category" style="text-align:center;">
                    <?= ucfirst(htmlspecialchars($item['category'])) ?>
                  </td>
                  <td class="col-stock" style="vertical-align: middle; text-align: right;">
                    <div style="text-align: right; padding-right: 10px;"> 
                        <span class="stock-value" onclick="InventoryApp.enableEdit(this)"><?= $item['stock'] ?></span>
                        <input type="number" class="stock-input" min="0" value="<?= $item['stock'] ?>" 
                               onblur="InventoryApp.saveEdit(this)" 
                               onkeydown="InventoryApp.handleKey(event, this)">
                        <input type="hidden" name="stocks[<?= $item['id'] ?>]" value="<?= $item['stock'] ?>">
                    </div>
                  </td>
                  <td class="col-actions" style="vertical-align: middle;">
                    <div style="display: inline-flex; margin: 0 auto; width: fit-content; gap: 4px;">
                      <button type="button" class="btn small" onclick="InventoryApp.decrement(this)">–</button>
                      <button type="button" class="btn small" onclick="InventoryApp.increment(this)">+</button>
                      
                      <button type="button" class="btn btn-delete" onclick="InventoryApp.deleteItem(this)" title="Delete item"> <i class="fa-solid fa-trash"></i></button>

                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <!-- Add new stock row -->
                <tr class="add-row" onclick="openModal()">
                  <td colspan="4" style="text-align:center; cursor:pointer; font-weight:700; color:#007acc;">
                    + Add New Stock
                  </td>
                </tr>
              <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No items found.</td></tr>
                <tr class="add-row" onclick="openModal()">
                  <td colspan="4" style="text-align:center; cursor:pointer; font-weight:700; color:#007acc;">
                    + Add New Stock
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
          <?php if ($totalPages > 1): ?>
            <div class="pagination" style="
                margin-top: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 1rem;
            ">
                <?php if ($page > 1): ?>
                    <a href="?page=inventory_update&category=<?= urlencode($categoryFilter) ?>&search=<?= urlencode($searchQuery) ?>&paging=<?= $page - 1 ?>" 
                      class="btn btn-outline pagination-btn">← Previous</a>
                <?php else: ?>
                    <button class="btn btn-outline pagination-btn" disabled>← Previous</button>
                <?php endif; ?>

                <span class="page-info">
                    Page <?= $page ?> of <?= $totalPages ?> 
                    (<?= $totalItems ?> items)
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=inventory_update&category=<?= urlencode($categoryFilter) ?>&search=<?= urlencode($searchQuery) ?>&paging=<?= $page + 1 ?>" 
                      class="btn btn-outline pagination-btn">Next →</a>
                <?php else: ?>
                    <button class="btn btn-outline pagination-btn" disabled>Next →</button>
                <?php endif; ?>
            </div>
          <?php endif; ?>
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
      <input type="text" name="name" placeholder="Item Name" required>
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="medicine">Medicine</option>
        <option value="equipment">Equipment</option>
      </select>
      <input type="number" name="new_item_stock" placeholder="Initial Stock" min="0" required>
      <div style="display:flex; gap:10px; justify-content:center; margin-top:8px;">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" style="background:#2e72a5; color:white;">Add Stocks</button>
      </div>
    </form>
  </div>
</div>


<script src="model/scripts/inventory_script.js"></script>


</body>
</html>