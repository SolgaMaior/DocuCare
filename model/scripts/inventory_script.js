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
};

document
  .querySelector("#updateInventoryForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault(); // stop normal form reload

    const rows = document.querySelectorAll("tr[data-id]");
    const stocks = {};
    rows.forEach((r) => {
      const id = r.dataset.id;
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
    const formData = new FormData(form);
    formData.append("action", "add_item");

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
