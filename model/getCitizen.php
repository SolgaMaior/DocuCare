<?php
require_once('authCheck.php');          // ensures user is logged in
require_once('model/databases/citizensdb.php');  // your get_citizens_by_id() function

// Get citID from URL if provided
$citID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);

// If the user is NOT admin, override citID with their own
if (!CURRENT_USER_IS_ADMIN) {
    
}

// If there's a valid citizen ID, fetch the record
if ($citID) {
    $citizen = get_citizens_by_id($citID);
} else {
    $citizen = null;
}
?>
