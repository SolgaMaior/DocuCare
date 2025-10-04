<?php
include 'model/databases/citizensdb.php';

if (isset($_GET['citID'])) {
    $citizen = get_citizens_by_id($_GET['citID']);
}
