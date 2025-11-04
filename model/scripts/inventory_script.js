let pendingNewItems = [];

const InventoryApp = {
  increment(btn) {
    const row = btn.closest("tr");
    const span = row.querySelector(".stock-value");
    const hidden = row.querySelector('input[type="hidden"]');
    if (!span || !hidden) return;
    let val = parseInt(span.textContent) || 0;
    span.textContent = ++val;
    hidden.value = val;
  },
  decrement(btn) {
    const row = btn.closest("tr");
    const span = row.querySelector(".stock-value");
    const hidden = row.querySelector('input[type="hidden"]');
    if (!span || !hidden) return;
    let val = parseInt(span.textContent) || 0;
    if (val > 0) val--;
    span.textContent = val;
    hidden.value = val;
  },
  enableEdit(span) {
    const input = span.nextElementSibling;
    if (!input) return;
    span.style.display = "none";
    input.style.display = "inline-block";
    input.focus();
    input.select();
  },
  saveEdit(input) {
    const span = input.previousElementSibling;
    const hidden = input.parentElement.querySelector('input[type="hidden"]');
    if (!span || !hidden) return;
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
      if (!span) return;
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
      console.error("Delete error:", err);
      showToast("Connection failed. Please try again.", "error");
    }
  },
};

// Form submission handler - only attach if form exists
const updateForm = document.querySelector("#updateInventoryForm");
if (updateForm) {
  updateForm.addEventListener("submit", async (e) => {
    e.preventDefault();

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
      console.error("Add items error:", err);
      showToast(err.message || "Failed to add new items", "error");
      return;
    }

    // 2) Then send stock updates for existing items
    const rows = document.querySelectorAll("tr[data-id]");
    const stocks = {};
    
    rows.forEach((r) => {
      const id = r.dataset.id;
      // Skip if no ID or if it's a new item
      if (!id || String(id).startsWith("new-")) return;
      
      // Find the hidden input more reliably
      const hiddenInput = r.querySelector('.col-stock input[type="hidden"]');
      
      if (hiddenInput && hiddenInput.value !== null && hiddenInput.value !== undefined) {
        stocks[id] = hiddenInput.value;
      } else {
        console.warn(`No hidden input found for row with id: ${id}`);
      }
    });

    // Check if there are any stocks to update
    if (Object.keys(stocks).length === 0) {
      showToast("No items to update", "success");
      setTimeout(() => {
        window.location.href = "index.php?page=inventory";
      }, 1000);
      return;
    }

    console.log("Stocks to update:", stocks); // Debug log

    // Send stocks as JSON string
    const formData = new FormData();
    formData.append("action", "update_stocks");
    formData.append("stocks_json", JSON.stringify(stocks));

    try {
      const res = await fetch("controllers/api/inventory_api.php", {
        method: "POST",
        body: formData,
      });
      
      const contentType = res.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        const text = await res.text();
        console.error("Non-JSON response:", text);
        throw new Error("Server returned non-JSON response");
      }
      
      const data = await res.json();
      console.log("Update response:", data); // Debug log
      showToast(data.message || data.error, data.success ? "success" : "error");
      if (data.success) {
        setTimeout(() => {
          window.location.href = "index.php?page=inventory";
        }, 1000);
      }
    } catch (err) {
      console.error("Update stocks error:", err);
      showToast("Connection failed. Please try again.", "error");
    }
  });
}

// Modal form submission - only attach if modal exists
const modalForm = document.querySelector("#addModal form");
if (modalForm) {
  modalForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const form = e.target;
    
    // Get form fields - these should exist if form exists
    const nameInput = form.querySelector('[name="name"]');
    const categoryInput = form.querySelector('[name="category"]');
    const stockInput = form.querySelector('[name="new_item_stock"]');
    
    // Extra validation
    if (!nameInput || !categoryInput || !stockInput) {
      console.error("Form fields not found:", {
        name: !!nameInput,
        category: !!categoryInput,
        stock: !!stockInput
      });
      showToast("Form error. Please refresh the page.", "error");
      return;
    }
    
    const name = nameInput.value.trim();
    const category = categoryInput.value;
    const stockVal = parseInt(stockInput.value, 10) || 0;

    if (!name || !category) {
      showToast("Please fill out name and category", "error");
      return;
    }

    // Track pending new item
    const tempId = `new-${Date.now()}`;
    pendingNewItems.push({ name, category, stock: stockVal, tempId });

    // Insert a new row into the table before the add-row
    const tbody = document.querySelector(".inventory-table tbody");
    const addRow = tbody ? tbody.querySelector("tr.add-row") : null;
    
    if (!tbody) {
      console.error("Table body not found");
      showToast("Table not found. Please refresh the page.", "error");
      return;
    }
    
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
          <button type="button" class="btn btn-delete" onclick="InventoryApp.deleteItem(this)" title="Delete item"><i class="fa-solid fa-trash"></i></button>
        </div>
      </td>`;
    
    if (addRow) {
      tbody.insertBefore(tr, addRow);
    } else {
      tbody.appendChild(tr);
    }

    // Clear and close modal
    form.reset();
    closeModal();
    showToast("Added to table. Click Update Stocks to save.", "success");
  });
}

// Update button handler - only attach if button exists
const updateBtn = document.querySelector("#updateStocksBtn");
if (updateBtn) {
  updateBtn.addEventListener("click", () => {
    const form = document.querySelector("#updateInventoryForm");
    if (form) {
      form.dispatchEvent(new Event("submit"));
    }
  });
}

function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.textContent = message;
  toast.className = `toast ${type}`;
  
  // Add styles inline in case CSS isn't loaded
  Object.assign(toast.style, {
    position: 'fixed',
    bottom: '20px',
    right: '20px',
    padding: '1rem 1.5rem',
    borderRadius: '5px',
    color: 'white',
    backgroundColor: type === 'error' ? '#dc3545' : '#28a745',
    boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
    zIndex: '9999',
    animation: 'slideIn 0.3s ease-out'
  });
  
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// Modal handling functions
function openModal() {
  const modal = document.getElementById("addModal");
  if (modal) {
    modal.style.display = "block";
  }
}

function closeModal() {
  const modal = document.getElementById("addModal");
  if (modal) {
    modal.style.display = "none";
  }
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
  if (e.target.id === "addModal") {
    closeModal();
  }
});

// Simple HTML escaper for dynamic cells
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}