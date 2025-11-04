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

// --- Fetch filtered inventory with pagination ---

$page = filter_input(INPUT_GET, 'paging', FILTER_VALIDATE_INT) ?: 1;
$perPage = 7;
try {
    $inventory = get_inventory($categoryFilter, $searchQuery, $page, $perPage);
    $totalItems = get_inventory_count($categoryFilter, $searchQuery);
    $totalPages = ceil($totalItems / $perPage);
    $stats = get_inventory_statistics();
} catch (PDOException $e) {
    $_SESSION['message'] = "Database Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    $inventory = [];
    $totalItems = 0;
    $totalPages = 0;
    $stats = ['total_items' => 0, 'in_stock' => 0, 'low_stock' => 0, 'out_stock' => 0];
}

require('view/inventory_update.view.php');