<?php
require_once(__DIR__ . '/db_con.php');

function add_appointment($lastname, $firstname, $middlename, $sex, $age, $purok, $schedule, $userID) {
    global $db; 
    $query = "INSERT INTO appointments (lastname, firstname, middlename, sex, age, purok, schedule, status, userID)
              VALUES (:lastname, :firstname, :middlename, :sex, :age, :purok, :schedule, 'Pending', :userID)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':lastname', $lastname);
    $stmt->bindValue(':firstname', $firstname);
    $stmt->bindValue(':middlename', $middlename);
    $stmt->bindValue(':sex', $sex);
    $stmt->bindValue(':age', $age, PDO::PARAM_INT);
    $stmt->bindValue(':purok', $purok);
    $stmt->bindValue(':schedule', $schedule);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

function get_appointments($userID = null) {
    global $db;
    $query = "SELECT id, lastname, firstname, middlename, sex, age, purok, schedule, status FROM appointments";
    if ($userID !== null) {
        $query .= " WHERE userID = :userID";
    }
    $query .= " ORDER BY schedule ASC";
    $stmt = $db->prepare($query);
    if ($userID !== null) {
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $results;
}

function update_appointment($id, $lastname, $firstname, $middlename, $sex, $age, $purok, $schedule, $userID) {
    global $db;
    $query = "UPDATE appointments
              SET lastname = :lastname, firstname = :firstname, middlename = :middlename,
                  sex = :sex, age = :age, purok = :purok, schedule = :schedule
              WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':lastname', $lastname);
    $stmt->bindValue(':firstname', $firstname);
    $stmt->bindValue(':middlename', $middlename);
    $stmt->bindValue(':sex', $sex);
    $stmt->bindValue(':age', $age, PDO::PARAM_INT);
    $stmt->bindValue(':purok', $purok);
    $stmt->bindValue(':schedule', $schedule);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

function delete_appointment($id) {
    global $db;
    $query = "DELETE FROM appointments WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

function update_appointment_status($id, $status) {
    global $db;
    $query = "UPDATE appointments SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}
?>