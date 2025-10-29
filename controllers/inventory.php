<?php
// controllers/inventoryController.php
session_start();
require_once('model/databases/db_con.php');  // Your PDO connection (defines $db)
require_once('model/databases/inventorydb.php');

// --- Input Handling ---
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

// --- Fetch Inventory Data ---
try {
    $inventory = get_inventory($categoryFilter, $searchQuery);
    $stats = get_inventory_statistics();
} catch (PDOException $e) {
    $_SESSION['message'] = "Database Error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    $inventory = [];
    $stats = ['total_items' => 0, 'in_stock' => 0, 'low_stock' => 0, 'out_stock' => 0];
}

// --- Include View ---
require('view/inventory.view.php');
