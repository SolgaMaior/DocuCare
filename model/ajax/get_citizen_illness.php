<?php
require_once('../databases/db_con.php');
require_once('../databases/illnessdb.php');

header('Content-Type: application/json');

$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);

if (!$citID) {
    echo json_encode(['error' => 'Invalid citizen ID']);
    exit;
}

$illnessRecords = get_citizen_illness_records($citID);
echo json_encode(['illness_records' => $illnessRecords]);
?>