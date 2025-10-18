<?php

function getdiagnoses($citID){
    global $db;

    $query = "SELECT * FROM diagnosis WHERE citID = :citID ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':citID', $citID, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $results;
}

function add_diagnosis($citID, $symptoms, $description) {
    global $db;
    $query = "INSERT INTO diagnosis (symptoms, description, citID)
              VALUES (:symptoms, :description, :citID)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':citID', $citID, PDO::PARAM_INT);
    $stmt->bindValue(':symptoms', $symptoms); 
    $stmt->bindValue(':description', $description);  
    $stmt->execute();
    $stmt->closeCursor();
}

function update_diagnosis($id, $symptoms, $description) {
    global $db;
    $query = "UPDATE diagnosis
              SET symptoms = :symptoms, description = :description
              WHERE diagID = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':symptoms', $symptoms);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

function delete_diagnosis($id) {
    global $db;
    $query = "DELETE FROM diagnosis WHERE diagID = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}