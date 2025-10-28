<?php
require_once(__DIR__ . '/db_con.php');

function add_appointment($schedule, $userID, $citID) {
    global $db; 
    $query = "INSERT INTO appointments (schedule, status, userID, citID)
              VALUES (:schedule, 'Pending', :userID, :citID)";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':schedule', $schedule);
    $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindValue(':citID', $citID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}

function get_all_appointments($userID = null) {
    global $db;
    $query = "SELECT a.id, a.citID, a.schedule, a.status, 
              c.lastname, c.firstname, c.middlename, c.purokID, c.sex, c.birth_date,
              TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) as age
              FROM appointments a
              JOIN citizens c ON a.citID = c.citID
              WHERE a.status = 'Pending'";
              
    if ($userID !== null) {
        $query .= " AND a.userID = :userID";
    }
    $query .= " ORDER BY a.schedule ASC";
    
    $stmt = $db->prepare($query);
    if ($userID !== null) {
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $results;
}

function get_appointments($userID = null) {
    global $db;
    $query = "SELECT a.id, a.citID, a.schedule, a.status, 
              c.lastname, c.firstname, c.middlename, c.purokID, c.sex, c.birth_date,
              TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) as age
              FROM appointments a
              JOIN citizens c ON a.citID = c.citID";
              
    if ($userID !== null) {
        $query .= " WHERE a.userID = :userID";
    }
    $query .= " ORDER BY a.schedule ASC";
    
    $stmt = $db->prepare($query);
    if ($userID !== null) {
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $results;
}

function update_appointment($id, $schedule) {
    global $db;
    $query = "UPDATE appointments
              SET schedule = :schedule
              WHERE id = :id";
    $stmt = $db->prepare($query);
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

function get_appointment_date($id) {
    global $db;
    $query = "SELECT schedule FROM appointments WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $result ? $result['schedule'] : null;
}

function get_user_email_by_appointment_id($id) {
    global $db;
    $query = "SELECT u.email
              FROM appointments a
              JOIN users u ON a.userID = u.userID
              WHERE a.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $result ? $result['email'] : null;
}
?>