<?php
require_once('../model/databases/map_data.php');
header('Content-Type: application/json');

$map_data = get_map_data_cached();
echo json_encode($map_data);
?>