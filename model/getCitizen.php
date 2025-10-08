<?php
include 'model/databases/citizensdb.php';

$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);
if ($citID) {
    $citizen = get_citizens_by_id($citID);
}
