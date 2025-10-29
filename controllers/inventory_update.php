<?php
// controllers/inventory_update.php
require_once('model/databases/db_con.php');  // Your PDO connection (defines $db)
require_once('model/databases/inventorydb.php');

session_start();

// --- Filters ---
$categoryFilter = $_GET['category'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

// --- Fetch filtered inventory ---
$inventory = get_inventory($categoryFilter, $searchQuery);

// --- Handle Add Item ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new item
    if (isset($_POST['new_item_name'], $_POST['new_item_category'], $_POST['new_item_stock'])) {
        $name = trim($_POST['new_item_name']);
        $category = trim($_POST['new_item_category']);
        $stock = (int)$_POST['new_item_stock'];

        try {
            $id = add_inventory_item($name, $category, $stock);
            $_SESSION['message'] = "Item '$name' added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error adding item: " . htmlspecialchars($e->getMessage());
            $_SESSION['message_type'] = "error";
        }

        header("Location: index.php?page=inventory");
        exit;
    }

    // Update multiple stock values
    if (isset($_POST['stocks']) && is_array($_POST['stocks'])) {
        try {
            update_multiple_stocks($_POST['stocks']);
            $_SESSION['message'] = "Inventory updated successfully!";
            $_SESSION['message_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Failed to update inventory: " . htmlspecialchars($e->getMessage());
            $_SESSION['message_type'] = "error";
        }

        header("Location: index.php?page=inventory");
        exit;
    }
}

// --- Retrieve inventory items ---
$inventory = get_inventory($categoryFilter, $searchQuery);

// --- Retrieve stats (optional for sidebar display) ---
$stats = get_inventory_statistics();

require('view/inventory_update.view.php');
