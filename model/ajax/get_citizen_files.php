<?php
require_once('../databases/db_con.php');
require_once('../databases/citizensdb.php');

header('Content-Type: application/json');

$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);

if (!$citID) {
    echo json_encode(['error' => 'Invalid citizen ID']);
    exit;
}

$files = get_citizen_file_data($citID);
echo json_encode(['files' => $files]);
?>