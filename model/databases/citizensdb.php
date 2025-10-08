<?php
require_once('model\file_handler.php');

function get_citizens_by_id($citID)
{
    global $db;
    
    // Validate input
    if (!is_numeric($citID) || $citID <= 0) {
        return false;
    }
    
    $query = 'SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID
          FROM citizens
          WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->execute();
    $citizen = $statement->fetch();
    $statement->closeCursor();
    return $citizen;
}

function get_citizens_by_purok($purokID)
{
    global $db;

    if ($purokID === 'all') {
        $query = "SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID, isArchived 
                  FROM citizens WHERE isArchived = 0
                  ORDER BY lastname";
        $statement = $db->prepare($query);
    } else {
        // Validate purokID if it's not 'all'
        if (!is_numeric($purokID) || $purokID <= 0) {
            return [];
        }
        
        $query = "SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID, isArchived 
                  FROM citizens
                  WHERE purokID = :purokID AND isArchived = 0";
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    }

    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result;
}



function get_archived_citizens()
{
    global $db;
    $query = "SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID, isArchived 
            FROM citizens
              WHERE isArchived = 1
              ORDER BY lastname";
    $statement = $db->prepare($query);
    $statement->execute();
    $citizens = $statement->fetchAll();
    $statement->closeCursor();
    return $citizens;
}


function restore_citizen($citID)
{
    global $db;
    
    // Validate input
    if (!is_numeric($citID) || $citID <= 0) {
        return false;
    }
    
    $query = 'UPDATE citizens
              SET isArchived = 0
              WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
    return true;
}


function add_citizen($firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum)
{
    global $db;
    
    // Validate required inputs
    if (empty($firstname) || empty($lastname) || !is_numeric($purokID) || !is_numeric($age) || 
        empty($sex) || empty($civilstatus) || empty($occupation) || empty($contactnum)) {
        return false;
    }
    
    $query = 'INSERT INTO citizens
                    (firstname, middlename, lastname, purokID, age, sex, civilstatus, occupation, contactnum )
                    VALUES
                    ( :firstname, :middlename, :lastname, :purokID, :age, :sex, :civilstatus, :occupation, :contactnum)';

    $statement = $db->prepare($query);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    $statement->bindValue(':age', $age, PDO::PARAM_INT);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);
    $statement->execute();
    $statement->closeCursor();
    return true;
}


function archive_citizen($citID)
{
    global $db;
    
    // Validate input
    if (!is_numeric($citID) || $citID <= 0) {
        return false;
    }
    
    $query = 'UPDATE citizens
              SET isArchived = 1
              WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->execute();
    $statement->closeCursor();
    return true;
}


function update_citizen($citID, $firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum)
{
    global $db;
    
    // Validate required inputs
    if (!is_numeric($citID) || $citID <= 0 || empty($firstname) || empty($lastname) || 
        !is_numeric($purokID) || !is_numeric($age) || empty($sex) || empty($civilstatus) || 
        empty($occupation) || empty($contactnum)) {
        return false;
    }
    
    $query = "UPDATE citizens 
              SET firstname = :firstname,
                  middlename = :middlename,
                  lastname = :lastname,
                  purokID = :purokID,
                  age = :age,
                  sex = :sex,
                  civilstatus = :civilstatus,
                  occupation = :occupation,
                  contactnum = :contactnum
              WHERE citID = :citID";

    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    $statement->bindValue(':age', $age, PDO::PARAM_INT);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);
    $statement->execute();
    $statement->closeCursor();
    return true;
}


function checkCitizenExists($firstname, $middlename, $lastname)
{
    global $db;
    
    // Validate inputs
    if (empty($firstname) || empty($lastname)) {
        return false;
    }
    
    $query = 'SELECT COUNT(*) FROM citizens WHERE firstname = :firstname AND middlename = :middlename AND lastname = :lastname';
    $statement = $db->prepare($query);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->execute();
    $count = $statement->fetchColumn();
    $statement->closeCursor();
    return $count > 0;
}
