<?php
require_once(__DIR__ . '/db_con.php');

function add_appointment($schedule, $userID, $citID) {
    global $db; 
    $query = "INSERT INTO appointments (schedule, status, userID, citID)
              VALUES (:schedule, 'Pending', :userID, :citID)";
    $statement = $db->prepare($query);
    $statement->bindValue(':schedule', $schedule);
    $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
}

function get_all_appointments($page = 1, $perPage = 15) {
    global $db;
    $offset = ($page - 1) * $perPage;

    $query = "SELECT a.id, a.citID, a.schedule, a.status, 
              c.lastname, c.firstname, c.middlename, c.purokID, c.sex, c.birth_date,
              TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) as age
              FROM appointments a
              JOIN citizens c ON a.citID = c.citID
            --   WHERE a.status = 'Pending'
              ORDER BY a.schedule ASC
              LIMIT :limit OFFSET :offset"; // ADDED THIS LINE

    $statement = $db->prepare($query);
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT); // FIXED: was missing
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
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

    $statement = $db->prepare($query);
    if ($userID !== null) {
        $statement->bindValue(':userID', $userID, PDO::PARAM_INT);
    }
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $results;
}

// Fixed: Count only pending appointments to match the query filter
function get_appointments_count()
{
    global $db;

    $query = "SELECT COUNT(*) FROM appointments WHERE status = 'Pending'"; // FIXED: removed isArchived, added status filter
    $statement = $db->prepare($query);
    $statement->execute();
    $count = $statement->fetchColumn();
    $statement->closeCursor();
    return $count;
}

function update_appointment($id, $schedule) {
    global $db;
    $query = "UPDATE appointments
              SET schedule = :schedule
              WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':schedule', $schedule);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
}

function delete_appointment($id) {
    global $db;
    $query = "DELETE FROM appointments WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
}

function update_appointment_status($id, $status) {
    global $db;
    $query = "UPDATE appointments SET status = :status WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
}

function get_appointment_date($id) {
    global $db;
    $query = "SELECT schedule FROM appointments WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result ? $result['schedule'] : null;
}

function get_user_email_by_appointment_id($id) {
    global $db;
    $query = "SELECT u.email
              FROM appointments a
              JOIN users u ON a.userID = u.userID
              WHERE a.id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result ? $result['email'] : null;
}
?>