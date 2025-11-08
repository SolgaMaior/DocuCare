<?php
require_once('authCheck.php');          
require_once('model/databases/citizensdb.php');  

// Get citID from URL if provided
$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);


// If there's a valid citizen ID, fetch the record
if ($citID) {
    $citizen = get_citizens_by_id($citID);
} else {
    $citizen = null;
}
?>
