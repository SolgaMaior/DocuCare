<?php
// controllers/refresh_clusters.php

// Prevent HTML errors from appearing in JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header FIRST
header('Content-Type: application/json');

session_start();

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized. Admin access required.'
    ]);
    exit;
}

try {
    require_once(__DIR__ . '/../model/databases/map_data.php');
    
    // Force refresh the cluster cache
    $result = refresh_cluster_cache();
    
    echo json_encode([
        'success' => true,
        'message' => 'Clusters refreshed successfully',
        'data' => $result
    ]);
    
} catch (Exception $e) {
    error_log("Refresh clusters error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to refresh clusters',
        'message' => $e->getMessage()
    ]);
}

exit;
?>