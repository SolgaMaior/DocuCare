<?php

function get_all_illnesses() {
    global $db;
    $query = "SELECT illness_id, illness_name FROM illnesses ORDER BY illness_name";
    $statement = $db->prepare($query);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function add_illness_record($citID, $purokID, $illness_id) {
    global $db;

    $query = "INSERT INTO illness_records (citID, purokID, illness_id, record_date)
              VALUES (:citID, :purokID, :illness_id, NOW())";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->bindValue(':purokID', $purokID);
    $statement->bindValue(':illness_id', $illness_id);
    $statement->execute();

    return $db->lastInsertId();
}



function get_citizen_illness_records($citID) {
    global $db;
    $query = "SELECT ir.*, i.illness_name
              FROM illness_records ir
              JOIN illnesses i ON ir.illness_id = i.illness_id
              WHERE ir.citID = :citID
              ORDER BY ir.record_date DESC";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function delete_illness_record($citID) {
    global $db;
    $query = "DELETE FROM illness_records WHERE citID = :citID";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->execute();
}