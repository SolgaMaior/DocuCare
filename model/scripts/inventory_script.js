let pendingNewItems = [];

const InventoryApp = {
  increment(btn) {
    const row = btn.closest("tr");
    const span = row.querySelector(".stock-value");
    const hidden = row.querySelector('input[type="hidden"]');
    let val = parseInt(span.textContent) || 0;
    span.textContent = ++val;
    hidden.value = val;
  },
  decrement(btn) {
    const row = btn.closest("tr");
    const span = row.querySelector(".stock-value");
    const hidden = row.querySelector('input[type="hidden"]');
    let val = parseInt(span.textContent) || 0;
    if (val > 0) val--;
    span.textContent = val;
    hidden.value = val;
  },
  enableEdit(span) {
    const input = span.nextElementSibling;
    span.style.display = "none";
    input.style.display = "inline-block";
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
    input.style.display = "none";
    span.style.display = "inline";
  },
  handleKey(e, input) {
    if (e.key === "Enter") input.blur();
    if (e.key === "Escape") {
      const span = input.previousElementSibling;
      input.value = span.textContent;
      input.style.display = "none";
      span.style.display = "inline";
    }
  },
  async deleteItem(btn) {
    const row = btn.closest("tr");
    const itemId = row.getAttribute("data-id");
    
    if (!itemId) return;
    
    // Confirm deletion
    if (!confirm("Are you sure you want to delete this item?")) {
      return;
    }
    
    // If it's a new item (starts with "new-"), just remove from table and pendingNewItems
    if (String(itemId).startsWith("new-")) {
      row.remove();
      pendingNewItems = pendingNewItems.filter(item => item.tempId !== itemId);
      showToast("Item removed from table", "success");
      return;
    }
    
    // For existing items, delete via API
    try {
      const formData = new FormData();
      formData.append("action", "delete_item");
      formData.append("id", itemId);
      
      const res = await fetch("controllers/api/inventory_api.php", {
        method: "POST",
        body: formData,
      });
      
      const data = await res.json();
      
      if (data.success) {
        row.remove();
        showToast(data.message || "Item deleted successfully", "success");
      } else {
        showToast(data.error || "Failed to delete item", "error");
      }
    } catch (err) {
      showToast("Connection failed. Please try again.", "error");
    }
  },
};

document
  .querySelector("#updateInventoryForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault(); // stop normal form reload

    // 1) Persist any newly added items first
    try {
      for (const item of pendingNewItems) {
        const fd = new FormData();
        fd.append("action", "add_item");
        fd.append("name", item.name);
        fd.append("category", item.category);
        fd.append("stock", String(item.stock));
        const addRes = await fetch("controllers/api/inventory_api.php", {
          method: "POST",
          body: fd,
        });
        const addData = await addRes.json();
        if (!addData.success) throw new Error(addData.error || "Failed to add item");
      }
      // Clear pending after success
      pendingNewItems = [];
    } catch (err) {
      showToast(err.message || "Failed to add new items", "error");
      return;
    }

    // 2) Then send stock updates for existing items
    const rows = document.querySelectorAll("tr[data-id]");
    const stocks = {};
    rows.forEach((r) => {
      const id = r.dataset.id;
      if (!id || String(id).startsWith("new-")) return; // skip temporary rows
      const val = r.querySelector('input[type="hidden"]').value;
      stocks[id] = val;
    });

    const formData = new FormData();
    formData.append("action", "update_stocks");
    for (const [id, val] of Object.entries(stocks)) {
      formData.append(`stocks[${id}]`, val);
    }

    try {
      const res = await fetch("controllers/api/inventory_api.php", {
        method: "POST",
        body: formData,
      });
      const data = await res.json();
      showToast(data.message || data.error, data.success ? "success" : "error");
      if (data.success) window.location.href = "index.php?page=inventory";
    } catch (err) {
      showToast("Connection failed. Please try again.", "error");
    }
  });

document
  .querySelector("#addModal form")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    const form = e.target;
    const name = form.querySelector('[name="new_item_name"]').value.trim();
    const category = form.querySelector('[name="new_item_category"]').value;
    const stockVal = parseInt(form.querySelector('[name="new_item_stock"]').value, 10) || 0;

    if (!name || !category) {
      showToast("Please fill out name and category", "error");
      return;
    }

    // Track pending new item
    const tempId = `new-${Date.now()}`;
    pendingNewItems.push({ name, category, stock: stockVal, tempId });

    // Insert a new row into the table before the add-row
    const tbody = document.querySelector(".inventory-table tbody");
    const addRow = tbody.querySelector("tr.add-row");
    const tr = document.createElement("tr");
    tr.setAttribute("data-id", tempId);
    tr.innerHTML = `
      <td class="col-name">${escapeHtml(name)}</td>
      <td class="col-category" style="text-align:center;">${escapeHtml(category.charAt(0).toUpperCase() + category.slice(1))}</td>
      <td class="col-stock" style="vertical-align: middle; text-align: right;">
        <div style="text-align: right; padding-right: 10px;">
          <span class="stock-value" onclick="InventoryApp.enableEdit(this)">${stockVal}</span>
          <input type="number" class="stock-input" min="0" value="${stockVal}"
                 onblur="InventoryApp.saveEdit(this)"
                 onkeydown="InventoryApp.handleKey(event, this)">
          <input type="hidden" value="${stockVal}">
        </div>
      </td>
      <td class="col-actions" style="vertical-align: middle;">
        <div style="display: inline-flex; margin: 0 auto; width: fit-content; gap: 4px;">
          <button type="button" class="btn small" onclick="InventoryApp.decrement(this)">–</button>
          <button type="button" class="btn small" onclick="InventoryApp.increment(this)">+</button>
          <button type="button" class="btn btn-delete" onclick="InventoryApp.deleteItem(this)" title="Delete item">×</button>
        </div>
      </td>`;
    if (addRow) tbody.insertBefore(tr, addRow);
    else tbody.appendChild(tr);

    // Clear and close modal
    form.reset();
    closeModal();
    showToast("Added to table. Click Update Stocks to save.", "success");
  });

document.querySelector("#updateStocksBtn")?.addEventListener("click", () => {
  document
    .querySelector("#updateInventoryForm")
    .dispatchEvent(new Event("submit"));
});

function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.textContent = message;
  toast.className = `toast ${type}`;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// Modal handling
function openModal() {
  document.getElementById("addModal").style.display = "block";
}
function closeModal() {
  document.getElementById("addModal").style.display = "none";
}
window.onclick = (e) => {
  if (e.target.id === "addModal") closeModal();
};

// Simple HTML escaper for dynamic cells
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

