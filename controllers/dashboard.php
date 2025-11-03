<?php
// Your main dashboard controller (index.php or dashboard controller)
require('model/databases/map_data.php');
require('model/databases/citizensdb.php');

// Remove these lines - we'll fetch via AJAX instead
// $dashboard_stats = get_dashboard_stats();
// $pie_data = get_pie_data();
// $data_json = json_encode($pie_data);

// Just load the view
require('view/dashboard.view.php');