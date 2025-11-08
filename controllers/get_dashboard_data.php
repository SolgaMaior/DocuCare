<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();

try {
    require_once(__DIR__ . '/../model/databases/map_data.php');
    require_once(__DIR__ . '/../model/databases/citizensdb.php');
    
    // Get date range from query parameters
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    
    // Validate date format if provided
    if ($start_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
        throw new Exception('Invalid start_date format. Use YYYY-MM-DD');
    }
    if ($end_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
        throw new Exception('Invalid end_date format. Use YYYY-MM-DD');
    }
    
    // Fetch all data 
    $data = [
        'stats' => get_dashboard_stats(),
        'pie' => get_pie_data(),
        'map' => get_map_data_cached($start_date, $end_date),
        'date_range' => [
            'start' => $start_date,
            'end' => $end_date
        ]
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