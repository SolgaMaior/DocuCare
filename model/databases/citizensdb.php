<?php


function get_dashboard_stats()
{
    global $db;

    $query = "SELECT 
        (SELECT COUNT(*) FROM citizens) AS total_citizens,
        (SELECT COUNT(*) FROM illness_records) AS total_illness_records,
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM users WHERE isApproved = 0) AS total_pending_accounts
    ";
    $statement = $db->prepare($query);
    $statement->execute();
    $stats = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return $stats;
}

function get_pie_data(){
    global $db;

    $query = "SELECT p.purokName, COUNT(c.citID) AS citizen_count
            FROM purok p
            LEFT JOIN citizens c ON p.purokID = c.purokID AND c.isArchived = 0
            GROUP BY p.purokID, p.purokName";
    $statement = $db->prepare($query);
    $statement->execute();
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    return $data;
}



function get_citizens_by_id($citID)
{
    global $db;
    
    if (!is_numeric($citID) || $citID <= 0) {
        return false;
    }
    
    $query = 'SELECT citID, firstname, middlename, lastname, birth_date, sex, civilstatus, occupation, contactnum, purokID, isArchived,
        TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as age
        FROM citizens
        WHERE citID = :citID';
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    
    $statement->execute();
    $citizen = $statement->fetch();
    $statement->closeCursor();
    return $citizen;
}

// In citizensdb.php
function get_citizens_by_purok($purokID, $page = 1, $perPage = 15)
{
    global $db;
    $offset = ($page - 1) * $perPage;

    if ($purokID === 'all') {
        $query = "SELECT citID, firstname, middlename, lastname, birth_date, sex, 
                  civilstatus, occupation, contactnum, purokID, isArchived,
                  TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as age
                  FROM citizens WHERE isArchived = 0
                  ORDER BY lastname
                  LIMIT :limit OFFSET :offset";
        $statement = $db->prepare($query);
    } else {
        $query = "SELECT citID, firstname, middlename, lastname, birth_date, sex,
                  civilstatus, occupation, contactnum, purokID, isArchived,
                  TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as age
                  FROM citizens
                  WHERE purokID = :purokID AND isArchived = 0
                  ORDER BY lastname
                  LIMIT :limit OFFSET :offset";
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    }
    
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    return $result;
}

// Add function to get total count
function get_citizens_count($purokID)
{
    global $db;
    
    if ($purokID === 'all') {
        $query = "SELECT COUNT(*) FROM citizens WHERE isArchived = 0";
        $statement = $db->prepare($query);
    } else {
        $query = "SELECT COUNT(*) FROM citizens 
                  WHERE purokID = :purokID AND isArchived = 0";
        $statement = $db->prepare($query);
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    }
    
    $statement->execute();
    $count = $statement->fetchColumn();
    $statement->closeCursor();
    return $count;
}

function get_citizen_file_data($citID)
{
    global $db;

    $query = "SELECT id, file_name, mime_type FROM record_files WHERE citID = :citID";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->execute();
    $files = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    $result = [];
    foreach ($files as $file) {
        $id = $file['id'];
        $result[] = [
            'id' => $id,
            'filename' => $file['file_name'],
            'mime' => $file['mime_type'],
            // new paths:
            'path' => "model/record_file_func/display_file.php?id=$id", // for <img src="">
            'view' => "index.php?page=download_file&id=$id"             // for <a href>
        ];
    }
    return $result;
}


function get_archived_citizens($page = null, $perPage = null) {
    global $db;
    $query = "SELECT * FROM citizens WHERE isArchived = 1 ORDER BY citID DESC";
    
    if ($page !== null && $perPage !== null) {
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT :limit OFFSET :offset";
    }
    
    $statement = $db->prepare($query);
    
    if ($page !== null && $perPage !== null) {
        $statement->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    }
    
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}
    
function get_archived_citizens_count() {
    global $db;
    $statement = $db->prepare("SELECT COUNT(*) FROM citizens WHERE isArchived = 1");
    $statement->execute();
    return $statement->fetchColumn();
}

//ADD/UPDATE FUNCTIONS

function add_citizen_file($citID, $file_name, $file_type, $file_data)
{
    global $db;

    $query = "INSERT INTO record_files (citID, file_name, mime_type, file_data)
            VALUES (:citID, :file_name, :mime_type, :file_data)";
    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->bindValue(':file_name', $file_name, PDO::PARAM_STR);
    $statement->bindValue(':mime_type', $file_type, PDO::PARAM_STR);
    $statement->bindParam(':file_data', $file_data, PDO::PARAM_LOB);
    $statement->execute();
    $statement->closeCursor();
}

function add_citizen($firstname, $middlename, $lastname, $purokID, $birth_date, $sex, $civilstatus, $occupation, $contactnum, $profile_image = null, $profile_image_type = null)
{
    global $db; 

    $query = "INSERT INTO citizens 
        (firstname, middlename, lastname, purokID, birth_date, sex, civilstatus, occupation, contactnum, profile_image, profile_image_type)
        VALUES 
        (:firstname, :middlename, :lastname, :purokID, :birth_date, :sex, :civilstatus, :occupation, :contactnum, :profile_image, :profile_image_type)";

    $statement = $db->prepare($query);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    $statement->bindValue(':birth_date', $birth_date);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);
    $statement->bindParam(':profile_image', $profile_image, PDO::PARAM_LOB);
    $statement->bindValue(':profile_image_type', $profile_image_type);
    $statement->execute();

    $last_id = $db->lastInsertId();

    $statement->closeCursor();
    return $last_id;
}

function update_citizen($citID, $firstname, $middlename, $lastname, $purokID, $birth_date, $sex, $civilstatus, $occupation, $contactnum, $profile_image = null, $profile_image_type = null)
{
    global $db;

    if ($profile_image !== null && $profile_image_type !== null) {
        $query = "UPDATE citizens 
                SET firstname = :firstname,
                    middlename = :middlename,
                    lastname = :lastname,
                    purokID = :purokID,
                    birth_date = :birth_date,
                    sex = :sex,
                    civilstatus = :civilstatus,
                    occupation = :occupation,
                    contactnum = :contactnum,
                    profile_image = :profile_image,
                    profile_image_type = :profile_image_type
                WHERE citID = :citID";
    } else {
        $query = "UPDATE citizens 
                SET firstname = :firstname,
                    middlename = :middlename,
                    lastname = :lastname,
                    purokID = :purokID,
                    birth_date = :birth_date,
                    sex = :sex,
                    civilstatus = :civilstatus,
                    occupation = :occupation,
                    contactnum = :contactnum
                WHERE citID = :citID";
    }

    $statement = $db->prepare($query);
    $statement->bindValue(':citID', $citID, PDO::PARAM_INT);
    $statement->bindValue(':firstname', $firstname);
    $statement->bindValue(':middlename', $middlename);
    $statement->bindValue(':lastname', $lastname);
    $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    $statement->bindValue(':birth_date', $birth_date);
    $statement->bindValue(':sex', $sex);
    $statement->bindValue(':civilstatus', $civilstatus);
    $statement->bindValue(':occupation', $occupation);
    $statement->bindValue(':contactnum', $contactnum);

    if ($profile_image !== null && $profile_image_type !== null) {
        $statement->bindParam(':profile_image', $profile_image, PDO::PARAM_LOB);
        $statement->bindValue(':profile_image_type', $profile_image_type);
    }

    $statement->execute();
    $statement->closeCursor();
}

//OTHER FUNCTIONS

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

function search_citizens($searchTerm, $purokID = 'all', $page = 1, $perPage = 15)
{
    global $db;
    $offset = ($page - 1) * $perPage;
    
    // Prepare search pattern
    $searchPattern = "%{$searchTerm}%";
    
    // Base query with search conditions
    $baseWhere = "(firstname LIKE :search OR 
                   middlename LIKE :search OR 
                   lastname LIKE :search OR 
                   contactnum LIKE :search OR 
                   occupation LIKE :search)";
    
    // Add purok filter
    if ($purokID === 'all') {
        $whereClause = "$baseWhere AND isArchived = 0";
    } elseif ($purokID === 'archived') {
        $whereClause = "$baseWhere AND isArchived = 1";
    } else {
        $whereClause = "$baseWhere AND purokID = :purokID AND isArchived = 0";
    }
    
    // Main query
    $query = "SELECT citID, firstname, middlename, lastname, birth_date, sex, 
              civilstatus, occupation, contactnum, purokID, isArchived,
              TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as age
              FROM citizens 
              WHERE $whereClause
              ORDER BY lastname
              LIMIT :limit OFFSET :offset";
    
    $statement = $db->prepare($query);
    $statement->bindValue(':search', $searchPattern, PDO::PARAM_STR);
    
    if ($purokID !== 'all' && $purokID !== 'archived') {
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    }
    
    $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
    $statement->execute();
    
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    
    return $result;
}

function get_search_count($searchTerm, $purokID = 'all')
{
    global $db;
    
    $searchPattern = "%{$searchTerm}%";
    
    $baseWhere = "(firstname LIKE :search OR 
                   middlename LIKE :search OR 
                   lastname LIKE :search OR 
                   contactnum LIKE :search OR 
                   occupation LIKE :search)";
    
    if ($purokID === 'all') {
        $whereClause = "$baseWhere AND isArchived = 0";
    } elseif ($purokID === 'archived') {
        $whereClause = "$baseWhere AND isArchived = 1";
    } else {
        $whereClause = "$baseWhere AND purokID = :purokID AND isArchived = 0";
    }
    
    $query = "SELECT COUNT(*) FROM citizens WHERE $whereClause";
    $statement = $db->prepare($query);
    $statement->bindValue(':search', $searchPattern, PDO::PARAM_STR);
    
    if ($purokID !== 'all' && $purokID !== 'archived') {
        $statement->bindValue(':purokID', $purokID, PDO::PARAM_INT);
    }
    
    $statement->execute();
    $count = $statement->fetchColumn();
    $statement->closeCursor();
    
    return $count;
}
?>