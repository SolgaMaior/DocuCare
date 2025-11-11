<?php
require_once('../databases/db_con.php');
require_once('../databases/diagnosisdb.php');

header('Content-Type: application/json');

$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);

if (!$citID) {
    echo json_encode(['error' => 'Invalid citizen ID']);
    exit;
}

$diagnoses = getdiagnoses($citID);
echo json_encode(['diagnoses' => $diagnoses]);
?>