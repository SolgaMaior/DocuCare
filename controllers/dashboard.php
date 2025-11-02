<?php
require('model/databases/map_data.php');
require('model/databases/citizensdb.php');

// Fetch stats and pie data
$dashboard_stats = get_dashboard_stats();
$pie_data = get_pie_data();

// Convert to JSON for chart
$data_json = json_encode($pie_data);

// Load main dashboard view
require('view/dashboard.view.php');
?>
