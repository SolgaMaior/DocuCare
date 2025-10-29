<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Update Inventory - DocuCare</title>
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

<script>
const InventoryApp = {
  increment(btn) {
    const row = btn.closest('tr');
    const span = row.querySelector('.stock-value');
    const hidden = row.querySelector('input[type="hidden"]');
    let val = parseInt(span.textContent) || 0;
    span.textContent = ++val;
    hidden.value = val;
  },
  decrement(btn) {
    const row = btn.closest('tr');
    const span = row.querySelector('.stock-value');
    const hidden = row.querySelector('input[type="hidden"]');
    let val = parseInt(span.textContent) || 0;
    if (val > 0) val--;
    span.textContent = val;
    hidden.value = val;
  },
  enableEdit(span) {
    const input = span.nextElementSibling;
    span.style.display = 'none';
    input.style.display = 'inline-block';
    input.focus();
    input.select();
  },
  saveEdit(input) {
    const span = input.previousElementSibling;
    const hidden = input.parentElement.querySelector('input[type="hidden"]');
    let newVal = parseInt(input.value);
    if (isNaN(newVal) || newVal < 0) newVal = 0;
    span.textContent = newVal;
    hidden.value = newVal;
    input.style.display = 'none';
    span.style.display = 'inline';
  },
  handleKey(e, input) {
    if (e.key === 'Enter') input.blur();
    if (e.key === 'Escape') {
      const span = input.previousElementSibling;
      input.value = span.textContent;
      input.style.display = 'none';
      span.style.display = 'inline';
    }
  }
};

document.querySelector('#updateInventoryForm').addEventListener('submit', async (e) => {
  e.preventDefault(); // stop normal form reload

  const rows = document.querySelectorAll('tr[data-id]');
  const stocks = {};
  rows.forEach(r => {
    const id = r.dataset.id;
    const val = r.querySelector('input[type="hidden"]').value;
    stocks[id] = val;
  });

  const formData = new FormData();
  formData.append('action', 'update_stocks');
  for (const [id, val] of Object.entries(stocks)) {
    formData.append(`stocks[${id}]`, val);
  }

  try {
    const res = await fetch('controllers/api/inventory_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    showToast(data.message || data.error, data.success ? 'success' : 'error');
    if (data.success) window.location.href = 'index.php?page=inventory';
  } catch (err) {
    showToast('Connection failed. Please try again.', 'error');
  }


  const data = await res.json();
  showToast(data.message || data.error, data.success ? 'success' : 'error');
  if (data.success) window.location.href = 'index.php?page=inventory';

});

document.querySelector('#addModal form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  formData.append('action', 'add_item');

  try {
    const res = await fetch('controllers/api/inventory_api.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    showToast(data.message || data.error, data.success ? 'success' : 'error');
    if (data.success) window.location.href = 'index.php?page=inventory';

  } catch (err) {
    showToast('Connection failed. Please try again.', 'error');
  }


  const data = await res.json();
  showToast(data.message || data.error, data.success ? 'success' : 'error');
  if (data.success) window.location.href = 'index.php?page=inventory';
});

document.querySelector('#updateStocksBtn')?.addEventListener('click', () => {
  document.querySelector('#updateInventoryForm').dispatchEvent(new Event('submit'));
});

function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.textContent = message;
  toast.className = `toast ${type}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}


// Modal handling
function openModal() { document.getElementById('addModal').style.display = 'block'; }
function closeModal() { document.getElementById('addModal').style.display = 'none'; }
window.onclick = e => { if (e.target.id === 'addModal') closeModal(); };
</script>


</body>
</html>