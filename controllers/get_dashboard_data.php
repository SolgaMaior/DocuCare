<?php
// Prevent HTML errors from appearing in JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header FIRST
header('Content-Type: application/json');

session_start();

try {
    // Include files
    require_once(__DIR__ . '/../model/databases/map_data.php');
    require_once(__DIR__ . '/../model/databases/citizensdb.php');

    // Fetch all data 
    $data = [
        'stats' => get_dashboard_stats(),
        'pie' => get_pie_data(),
        'map' => get_map_data_cached() // Cache Map for faster loading
    ];
    
    echo json_encode($data);
    
} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch dashboard data',
        'message' => $e->getMessage()
    ]);
}
exit;
?>