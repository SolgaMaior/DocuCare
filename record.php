
<?php

require('model/databases/citizensdb.php');
require('model/databases/db_con.php');


$citizenID = filter_input(INPUT_POST, 'citizenID', FILTER_VALIDATE_INT);
$purokID = filter_input(INPUT_POST, 'purokID', FILTER_VALIDATE_INT);

if (!$purokID) {
    $purokID = filter_input(INPUT_GET, 'purokID', FILTER_VALIDATE_INT);
}

$action = filter_input(INPUT_POST, 'action');
if (!$action) {
    $action = filter_input(INPUT_GET, 'action');
}
if (!$action) {
    $action = 'list_citizens';
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add_citizen') {
    $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
    $firstname = trim(filter_input(INPUT_POST, 'first_name')) ?? '';
    $middlename = trim(filter_input(INPUT_POST, 'middle_name')) ?? '';
    $lastname = trim(filter_input(INPUT_POST, 'last_name')) ?? '';
    $purokID = filter_input(INPUT_POST, 'purok', FILTER_VALIDATE_INT);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $sex = filter_input(INPUT_POST, 'sex') ?? '';
    $civilstatus = filter_input(INPUT_POST, 'civilstatus') ?? '';
    $occupation = trim(filter_input(INPUT_POST, 'occupation')) ?? '';
    $contactnum = trim(filter_input(INPUT_POST, 'contactnum')) ?? '';

    // Validate required fields
    $missingRequired = (
        $firstname === '' ||
        $lastname === '' ||
        $purokID === null ||
        $age === null ||
        $sex === '' ||
        $civilstatus === '' ||
        $occupation === '' ||
        $contactnum === ''
    );

    if ($missingRequired) {
        $redirectPurok = $_GET['purokID'] ?? 'all';
        header("Location: record.php?purok=$redirectPurok&error=missing_fields");
        exit;
    }

    $profileImageData = null;
    $profileImageType = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $profileImageData = file_get_contents($_FILES['profileImage']['tmp_name']);
        $profileImageType = $_FILES['profileImage']['type'];
    }

    if ($citID) {
        update_citizen(
            $citID,
            $firstname,
            $middlename,
            $lastname,
            $purokID,
            $age,
            $sex,
            $civilstatus,
            $occupation,
            $contactnum,
            $profileImageData,
            $profileImageType
        );
        $targetCitizenID = $citID;
    } else {
        $targetCitizenID = add_citizen(
            $firstname,
            $middlename,
            $lastname,
            $purokID,
            $age,
            $sex,
            $civilstatus,
            $occupation,
            $contactnum,
            $profileImageData,
            $profileImageType
        );
    }

    $medicalFiles = [];
    if (isset($_FILES['medical_files']) && !empty($_FILES['medical_files']['name'][0])) {
        $medicalCondition = trim(filter_input(INPUT_POST, 'medical_condition')) ?? '';
        $medicalNotes = trim(filter_input(INPUT_POST, 'medical_notes')) ?? '';

        foreach ($_FILES['medical_files']['tmp_name'] as $i => $tmpName) {
            if ($_FILES['medical_files']['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['medical_files']['name'][$i];
                $file_type = $_FILES['medical_files']['type'][$i];
                $file_data = file_get_contents($tmpName);

                add_citizen_file($targetCitizenID, $file_name, $file_type, $file_data);
                $medicalFiles[] = $file_name;
            }
        }
    }

    $redirectPurok = $_GET['purokID'] ?? 'all';
    $successMessage = "success=1";
    if (!empty($medicalFiles)) {
        $successMessage .= "&medical_uploaded=1&files_count=" . count($medicalFiles);
    }

    header("Location: record.php?$successMessage");
    exit;
}


    if ($action === 'archive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            archive_citizen($citID);
        }
        
        $redirectPurok = $_GET['purokID'] ?? 'all';
        header("Location: record.php");
        exit;
    }

    if ($action === 'unarchive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            restore_citizen($citID);
        }
        header("Location: record.php");
        exit;
    }

}

$purokID = $_GET['purokID'] ?? 'all';
$citizens = ($purokID === 'archived') ? get_archived_citizens() : get_citizens_by_purok($purokID);

require_once('view/record.view.php');