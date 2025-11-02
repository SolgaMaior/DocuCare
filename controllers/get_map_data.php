<?php
require_once('../model/databases/map_data.php');
header('Content-Type: application/json');
echo json_encode($merged); // whatever variable holds your purok data