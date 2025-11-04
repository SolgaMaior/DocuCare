<?php
// controllers/api/inventory_api.php
header('Content-Type: application/json');

require_once('../../authCheck.php');

require_once('../../model/databases/db_con.php');
require_once('../../model/databases/Inventorydb.php');

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        case 'add_item':
            // Only admins can add
            if (!CURRENT_USER_IS_ADMIN) {
                throw new Exception("Access denied.");
            }

            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $stock = (int)($_POST['stock'] ?? 0);

            if (empty($name) || empty($category)) {
                throw new Exception("Missing required fields.");
            }

            add_inventory_item($name, $category, $stock);
            echo json_encode(['success' => true, 'message' => "Item '$name' added successfully!"]);
            break;

        case 'update_stocks':
            if (!CURRENT_USER_IS_ADMIN) {
                throw new Exception("Access denied.");
            }

            $stocks = $_POST['stocks'] ?? null;
            if (!$stocks || !is_array($stocks)) {
                throw new Exception("Invalid stock data.");
            }

            update_multiple_stocks($stocks);
            echo json_encode(['success' => true, 'message' => "Stocks updated successfully!"]);
            break;

        case 'delete_item':
            if (!CURRENT_USER_IS_ADMIN) {
                throw new Exception("Access denied.");
            }

            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("Missing item ID.");

            $deleted = delete_inventory_item($id);
            echo json_encode(['success' => $deleted, 'message' => $deleted ? "Item deleted." : "Item not found."]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => "Invalid action."]);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
