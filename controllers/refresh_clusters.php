<?php
// Start with error handling to prevent HTML output
error_reporting(0); // Suppress errors in production
ini_set('display_errors', 0);

// Set JSON header FIRST before any output
header('Content-Type: application/json');

// Start session and check authentication
session_start();

// Include files
require_once(__DIR__ . '/../authCheck.php');
require_once(__DIR__ . '/../model/databases/map_data.php');

try {
    // Check admin permission
    if (!defined('CURRENT_USER_IS_ADMIN') || !CURRENT_USER_IS_ADMIN) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Delete cache to force refresh
    $cache_file = __DIR__ . '/../cache/map_clusters.json';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }

    // Get fresh data
    $fresh_data = get_map_data_cached();

    echo json_encode([
        'success' => true, 
        'message' => 'Clusters refreshed successfully',
        'clusters' => count($fresh_data)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to refresh clusters',
        'message' => $e->getMessage()
    ]);
}
exit;
?>