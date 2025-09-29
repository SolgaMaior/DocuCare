<?php


function get_citizens_by_purok($purokID)
{
    global $db;

    if ($purokID) {
        $query = 'SELECT c.citizenID, c.firstname, c.middlename, c.lastname, p.purokName AS purok
            FROM citizens c
            JOIN puroks p ON c.purokID = p.purokID
            WHERE c.purokID = :purokID
            ORDER BY c.lastname';
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID);
        $statement->execute();
        $citizens = $statement->fetchAll();
        $statement->closeCursor();
        return $citizens;
    } else {

        $query = 'SELECT * FROM citizens ORDER BY lastname';
        $statement = $db->prepare($query);
        $statement->execute();
        $citizens = $statement->fetchAll();
        $statement->closeCursor();
        return $citizens;
    }
}

function add_citizen($firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum)
{
    global $db;

    // Updated query to match all the parameters you're passing
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
              SET isArchived = 1, archivedDate = NOW()
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
