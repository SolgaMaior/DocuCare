<?php
require_once(__DIR__ . '/appointmentdb_con.php'); 
// Use $pdo from appointmentdb_con.php

// Add a new appointment
function add_appointment($lastname, $firstname, $middlename, $sex, $age, $purok, $schedule) {
    global $pdo;
    $query = "INSERT INTO appointments (lastname, firstname, middlename, sex, age, purok, schedule, status)
              VALUES (:lastname, :firstname, :middlename, :sex, :age, :purok, :schedule, 'Pending')";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':lastname', $lastname);
    $stmt->bindValue(':firstname', $firstname);
    $stmt->bindValue(':middlename', $middlename);
    $stmt->bindValue(':sex', $sex);
    $stmt->bindValue(':age', $age, PDO::PARAM_INT);
    $stmt->bindValue(':purok', $purok);
    $stmt->bindValue(':schedule', $schedule);
    $stmt->execute();
    $stmt->closeCursor();
}

// Get all appointments
function get_appointments() {
    global $pdo;
    $query = "SELECT * FROM appointments ORDER BY schedule";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $results;
}

// Update an existing appointment
function update_appointment($id, $lastname, $firstname, $middlename, $sex, $age, $purok, $schedule) {
    global $pdo;
    $query = "UPDATE appointments 
              SET lastname = :lastname, firstname = :firstname, middlename = :middlename,
                  sex = :sex, age = :age, purok = :purok, schedule = :schedule
              WHERE id = :id";
    $stmt = $pdo->prepare($query);
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

// Delete an appointment
function delete_appointment($id) {
    global $pdo;
    $query = "DELETE FROM appointments WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

// Update appointment status (Approve / Deny)
function update_appointment_status($id, $status) {
    global $pdo;
    $query = "UPDATE appointments SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}
?>
