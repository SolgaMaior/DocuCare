<?php
// controllers/api/inventory_api.php
header('Content-Type: application/json');
session_start();

require_once('../../model/databases/db_con.php');  // Your PDO connection (defines $db)
require_once('../../model/databases/inventorydb.php');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update_stocks':
            if (!isset($_POST['stocks']) || !is_array($_POST['stocks'])) {
                throw new Exception("Invalid stock data");
            }
            update_multiple_stocks($_POST['stocks']);
            echo json_encode(['success' => true, 'message' => 'Stocks updated successfully!']);
            break;

        case 'add_item':
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $stock = (int)($_POST['stock'] ?? 0);

            if ($name === '' || $category === '') {
                throw new Exception("Incomplete item details");
            }

            add_inventory_item($name, $category, $stock);
            echo json_encode(['success' => true, 'message' => 'Item added successfully!']);
            break;

        case 'delete_item':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("Invalid item ID");
            }
            $deleted = delete_inventory_item($id);
            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'Item deleted successfully!']);
            } else {
                throw new Exception("Item not found or already deleted");
            }
            break;

        default:
            throw new Exception("Unknown action");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
