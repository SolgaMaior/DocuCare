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
    
    // First, check if citizen already has an illness record
    $checkQuery = "SELECT recordID FROM illness_records WHERE citID = :citID";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindValue(':citID', $citID);
    $checkStmt->execute();
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing record instead of creating new one
        $query = "UPDATE illness_records 
                  SET illness_id = :illness_id, 
                      purokID = :purokID, 
                      record_date = NOW() 
                  WHERE citID = :citID";
        $statement = $db->prepare($query);
        $statement->bindValue(':citID', $citID);
        $statement->bindValue(':purokID', $purokID);
        $statement->bindValue(':illness_id', $illness_id);
        $statement->execute();
        return $existing['recordID'];
    } else {
        // Create new record
        $query = "INSERT INTO illness_records (citID, purokID, illness_id, record_date)
                  VALUES (:citID, :purokID, :illness_id, NOW())";
        $statement = $db->prepare($query);
        $statement->bindValue(':citID', $citID);
        $statement->bindValue(':purokID', $purokID);
        $statement->bindValue(':illness_id', $illness_id);
        $statement->execute();
        return $db->lastInsertId();
    }
}

function update_illness_record($citID, $illness_id) {
    global $db;
    
    // Check if record exists
    $query = "SELECT recordID FROM illness_records WHERE citID = :citID";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->execute();
    $existing = $statement->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // Update existing record
        $query = "UPDATE illness_records
                  SET illness_id = :illness_id, 
                      record_date = NOW()
                  WHERE citID = :citID";
        $statement = $db->prepare($query);
        $statement->bindValue(':citID', $citID);
        $statement->bindValue(':illness_id', $illness_id);
        $statement->execute();
    } else {
        // Get purokID from citizens table
        $purokQuery = "SELECT purokID FROM citizens WHERE citID = :citID";
        $purokStmt = $db->prepare($purokQuery);
        $purokStmt->bindValue(':citID', $citID);
        $purokStmt->execute();
        $citizen = $purokStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($citizen) {
            // Create new record if none exists
            add_illness_record($citID, $citizen['purokID'], $illness_id);
        }
    }
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