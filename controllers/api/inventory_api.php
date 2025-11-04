<?php
// controllers/api/inventory_api.php - DEBUG VERSION
session_start();
require_once('../../model/databases/db_con.php');
require_once('../../model/databases/inventorydb.php');

// Set JSON header
header('Content-Type: application/json');

// DEBUG: Log all incoming data
error_log("POST data: " . print_r($_POST, true));

// Check if user is admin
if (!isset($_SESSION['user']) || !$_SESSION['user']['isAdmin']) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_item':
            $name = trim($_POST['name'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $stock = (int)($_POST['stock'] ?? 0);
            
            if (empty($name) || empty($category)) {
                echo json_encode(['success' => false, 'error' => 'Name and category are required']);
                exit;
            }
            
            add_inventory_item($name, $category, $stock);
            echo json_encode([
                'success' => true,
                'message' => "Item '$name' added successfully!"
            ]);
            break;
            
        case 'update_stocks':
            // DEBUG: Show what we received
            $stocksJson = $_POST['stocks_json'] ?? '';
            error_log("Stocks JSON received: " . $stocksJson);
            
            if (empty($stocksJson)) {
                // Also check if stocks came as array
                if (isset($_POST['stocks']) && is_array($_POST['stocks'])) {
                    $stocks = $_POST['stocks'];
                    error_log("Stocks received as array: " . print_r($stocks, true));
                } else {
                    echo json_encode([
                        'success' => false, 
                        'error' => 'No stock data provided',
                        'debug' => $_POST
                    ]);
                    exit;
                }
            } else {
                $stocks = json_decode($stocksJson, true);
                error_log("Decoded stocks: " . print_r($stocks, true));
            }
            
            if (!is_array($stocks) || empty($stocks)) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Invalid stock data format',
                    'received' => $stocks
                ]);
                exit;
            }
            
            // DEBUG: Log before update
            error_log("About to update stocks: " . print_r($stocks, true));
            
            update_multiple_stocks($stocks);
            
            echo json_encode([
                'success' => true,
                'message' => 'Inventory updated successfully!',
                'updated_count' => count($stocks)
            ]);
            break;
            
        case 'delete_item':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
                exit;
            }
            
            $deleted = delete_inventory_item($id);
            
            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Item deleted successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Item not found or already deleted'
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>