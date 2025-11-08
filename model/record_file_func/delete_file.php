<?php
// Start output buffering to catch any errors
ob_start();

// Suppress all output
ini_set('display_errors', 0);
error_reporting(0);

// Try to load database
try {
    // Try different paths
    if (file_exists('../databases/db_con.php')) {
        require_once '../databases/db_con.php';
    } 
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

// Clear any output from includes
ob_end_clean();

// Now set JSON header
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

try {
    
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    if (!isset($input['id'])) {
        throw new Exception('File ID not provided');
    }
    
    $fileID = intval($input['id']);
    
    if ($fileID <= 0) {
        throw new Exception('Invalid file ID: ' . $fileID);
    }
    
   
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    // Delete the file
    $stmt = $db->prepare("DELETE FROM record_files WHERE id = :id");
    $stmt->bindValue(':id', $fileID, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new Exception('Delete query failed');
    }
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('File not found or already deleted');
    }
    
    $stmt->closeCursor();
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'File deleted successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
exit;
?>