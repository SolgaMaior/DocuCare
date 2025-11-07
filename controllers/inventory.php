<?php
// controllers/inventoryController.php
if (!CURRENT_USER_IS_ADMIN) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('model/databases/db_con.php');
require_once('model/databases/inventorydb.php');

// --- Input Handling ---
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'all';
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = filter_input(INPUT_GET, 'paging', FILTER_VALIDATE_INT) ?: 1;
$perPage = 7;

// --- Fetch Inventory Data ---
try {
    $inventory = get_inventory($categoryFilter, $searchQuery, $page, $perPage);
    $inventoryforChart = get_inventory_for_chart($categoryFilter, $searchQuery);
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

// Low-stock detection logic
$lowStockItems = [];
foreach ($inventory as $item) {
    if ($item['stock'] < 10) {
        $lowStockItems[] = $item['name'];
    }
}

//  Intelligent popup control
$previousLowStock = $_SESSION['previous_low_stock'] ?? [];
$currentLowStock = $lowStockItems;

$showLowStockPopup = false;

// Show popup if there's any new or changed low-stock item
if (!empty($currentLowStock)) {
    if ($currentLowStock !== $previousLowStock) {
        $showLowStockPopup = true;
        $_SESSION['previous_low_stock'] = $currentLowStock;
    }
} else {
    // Clear memory if everything is restocked
    unset($_SESSION['previous_low_stock']);
}

// --- Include View ---
require('view/inventory.view.php');
