<?php
// controllers/inventoryController.php

if (!CURRENT_USER_IS_ADMIN) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

if (session_status() === PHP_SESSION_NONE) session_start();

require_once('model/databases/db_con.php');
require_once('model/databases/inventorydb.php');

// --- Input handling ---
$categoryFilter = trim($_GET['category'] ?? 'all');
$searchQuery    = trim($_GET['search'] ?? '');
$page           = filter_input(INPUT_GET, 'paging', FILTER_VALIDATE_INT) ?: 1;
$perPage        = 7;

// --- Fetch inventory with filters + pagination ---
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
$lowStockItems = array_filter($inventory, fn($item) => $item['stock'] < 50);
$lowStockItems = array_column($lowStockItems, 'name');

$_SESSION['previous_low_stock'] = $lowStockItems;



// --- Load view ---
require('view/inventory.view.php');
