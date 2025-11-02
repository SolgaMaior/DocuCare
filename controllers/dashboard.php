<?php
require('model/databases/map_data.php');
require('model/databases/citizensdb.php');


$dashboard_stats = get_dashboard_stats();

require('view/dashboard.view.php');

