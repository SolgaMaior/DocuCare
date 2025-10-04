<?php
require_once('model\file_handler.php');

function get_citizens_by_id($citID)
{
    global $db;
    $query = 'SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID
          FROM citizens
          WHERE c.citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
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
        $query = "SELECT citID, firstname, middlename, lastname, age, sex, civilstatus, occupation, contactnum, purokID, isArchived 
                  FROM citizens
                  WHERE purokID = :purokID AND isArchived = 0";
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID);
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
    $query = 'UPDATE citizens
              SET isArchived = 0
              WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->execute();
    $statement->closeCursor();
}


function add_citizen($firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum)
{
    global $db;
    $query = 'INSERT INTO citizens
                    (firstname, middlename, lastname, purokID, age, sex, civilstatus, occupation, contactnum )
                    VALUES
                    ( :firstname, :middlename, :lastname, :purokID, :age, :sex, :civilstatus, :occupation, :contactnum)';


    $statement = $db->prepare($query);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID);
    $statement->bindValue(':age', $age);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);
    $statement->execute();
    $statement->closeCursor();
}


function archive_citizen($citID)
{
    global $db;
    $query = 'UPDATE citizens
              SET isArchived = 1
              WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID);
    $statement->execute();
    $statement->closeCursor();
}


function update_citizen($citID, $firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum)
{
    global $db;
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
    $statement->bindValue(':citID', $citID);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID);
    $statement->bindValue(':age', $age);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);
    $statement->execute();
    $statement->closeCursor();
}


function checkCitizenExists($firstname, $middlename, $lastname)
{
    global $db;
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
