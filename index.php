<?php
    require('model/database.php');
    require('model/citizensdb.php');

    $citizenID = filter_input(INPUT_POST, 'citizenID', FILTER_VALIDATE_INT);
    
    $purokID = filter_input(INPUT_POST, 'purokID', FILTER_VALIDATE_INT);
    if(!$purokID){
        $purokID = filter_input(INPUT_GET, 'purokID', FILTER_VALIDATE_INT);
    }

    $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
    if (!$action) {
        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
        if (!$citizenID) {
            $action = 'list_citizens';
        }
    }

    switch ($action) {
        default:
        $citizennames = get_citizens_by_purok($purokID);
        include('view/citizen_list.php');
    }
