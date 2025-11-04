<?php
// controllers/reports.controller.php

session_start();

require_once('model/databases/db_con.php');
require_once('model/ReportModel.php');


// Get filter parameters
$reportType = $_GET['type'] ?? 'overview';
$purokID = isset($_GET['purok']) && $_GET['purok'] !== '' ? intval($_GET['purok']) : null;
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Initialize data array
$data = [
    'reportType' => $reportType,
    'purokID' => $purokID,
    'startDate' => $startDate,
    'endDate' => $endDate,
    'puroks' => get_puroks(),
    'pageTitle' => 'Reports & Analytics'
];

// Fetch data based on report type
switch ($reportType) {
    case 'citizens':
        $data['citizenStats'] = get_citizen_statistics($purokID);
        $data['citizensByPurok'] = get_citizens_by_purok_report($purokID);
        $data['citizensList'] = get_citizens_list_report($purokID, 100);
        break;
        
    case 'health':
        $data['illnessStats'] = get_illness_statistics($purokID, $startDate, $endDate);
        $data['illnessTrends'] = get_illness_trends(6);
        $data['appointmentStats'] = get_appointment_statistics($startDate, $endDate);
        break;
        
    case 'inventory':
        $data['inventoryStats'] = get_inventory_statistics();
        $data['lowStockItems'] = get_low_stock_items();
        break;
        
    case 'overview':
    default:
        $data['citizenStats'] = get_citizen_statistics();
        $data['illnessStats'] = get_illness_statistics(null, $startDate, $endDate);
        $data['appointmentStats'] = get_appointment_statistics($startDate, $endDate);
        $data['inventoryStats'] = get_inventory_statistics();
        $data['citizensByPurok'] = get_citizens_by_purok_report();
        break;
}

// Extract data for view
extract($data);

// Load view
require __DIR__ . '/../view/reports_view.php';
