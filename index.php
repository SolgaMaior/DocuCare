<?php
require_once('model/citizensdb.php'); // adjust path if needed
require_once('model/db_con.php');

$citizenID = filter_input(INPUT_POST, 'citizenID', FILTER_VALIDATE_INT);

$purokID = filter_input(INPUT_POST, 'purokID', FILTER_VALIDATE_INT);
if (!$purokID) {
    $purokID = filter_input(INPUT_GET, 'purokID', FILTER_VALIDATE_INT);
}


$action = $_POST['action'] ?? $_GET['action'] ?? 'list_citizens';

$action = filter_input(INPUT_POST, 'action');
if (!$action) {
    $action = filter_input(INPUT_GET, 'action');
    if (!$citizenID) {
        $action = 'list_citizens';
    }
}


switch ($action) {
    case 'archive_citizen':
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            archive_citizen($citID);
        }
        header("Location: index.php?archived=1");
        exit;
        break;

    case 'add_citizen':
        // handle add logic
        header("Location: index.php?success=1");
        exit;
        break;

    case 'list_citizens':
    default:
        $purokID = filter_input(INPUT_GET, 'purokID', FILTER_VALIDATE_INT);
        $citizennames = get_citizens_by_purok($purokID);
        include('view/record.php');
        break;
}
