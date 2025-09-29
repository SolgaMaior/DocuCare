<?php

function get_citizens_by_purok($purokID = null)
{
    global $db;

    if ($purokID === 'all' || empty($purokID)) {
        // Show all citizens
        $query = 'SELECT c.citID, c.firstname, c.middlename, c.lastname, p.purokName AS purok
                  FROM citizens c
                  JOIN purok p ON c.purokID = p.purokID
                  WHERE c.isArchived = 0
                  ORDER BY c.lastname';
        $statement = $db->prepare($query);
        $statement->execute();
    } else {
        // Filter by specific purok
        $query = 'SELECT c.citID, c.firstname, c.middlename, c.lastname, p.purokName AS purok
                  FROM citizens c
                  JOIN purok p ON c.purokID = p.purokID
                  WHERE c.isArchived = 0
                  AND c.purokID = :purokID
                  ORDER BY c.lastname';
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
        $statement->execute();
    }

    $citizens = $statement->fetchAll();
    $statement->closeCursor();
    return $citizens;
}



function get_archived_citizens()
{
    global $db;
    $query = 'SELECT * FROM citizens WHERE isArchived = 1 ORDER BY lastname';
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
              SET isArchived = 0, archivedDate = NULL
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
    $query = 'UPDATE citizens
                     SET firstname = :firstname,
                         middlename = :middlename,
                         lastname = :lastname,
                         age = :age,
                         sex = :sex,
                         civilstatus = :civilstatus,
                         occupation = :occupation,
                         contactnumber = :contactnumber
                     WHERE citID = :citID';
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
    $statement->bindValue(':contactnumber', $contactnum);
    $statement->execute();
    $statement->closeCursor();
}
