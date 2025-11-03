<?php

require('model/databases/citizensdb.php');
require('model/databases/diagnosisdb.php');
require('model/databases/illnessdb.php'); // ADD THIS LINE
require('model/databases/db_con.php');
require('model/record_file_func/diagnosis_api.php');

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

    // Handle diagnosis generation
    if ($action === 'generate_diagnosis') {
        $symptoms = trim(filter_input(INPUT_POST, 'medical_condition') ?? '');
        $additionalDescription = trim(filter_input(INPUT_POST, 'medical_notes') ?? '');

        if (empty($symptoms)) {
            $diagnosisResults = [
                'success' => false,
                'error' => 'Please enter symptoms to generate diagnosis.'
            ];
        } else {
            $diagnosisAPI = new DiagnosisAPI();
            $diagnosisResults = $diagnosisAPI->getDiagnosis($symptoms, $additionalDescription);
        }
    }

    if ($action === 'add_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        $firstname = trim(filter_input(INPUT_POST, 'first_name')) ?? '';
        $middlename = trim(filter_input(INPUT_POST, 'middle_name')) ?? '';
        $lastname = trim(filter_input(INPUT_POST, 'last_name')) ?? '';
        $purokID = filter_input(INPUT_POST, 'purok', FILTER_VALIDATE_INT);
        $birth_date = filter_input(INPUT_POST, 'birth_date') ?? '';
        $sex = filter_input(INPUT_POST, 'sex') ?? '';
        $civilstatus = filter_input(INPUT_POST, 'civilstatus') ?? '';
        $occupation = trim(filter_input(INPUT_POST, 'occupation')) ?? '';
        $contactnum = trim(filter_input(INPUT_POST, 'contactnum')) ?? '';

        // Validate required fields
        $missingRequired = (
            $firstname === '' ||
            $lastname === '' ||
            $purokID === null ||
            $birth_date === '' ||
            $sex === '' ||
            $civilstatus === '' ||
            $occupation === '' ||
            $contactnum === ''
        );

        if ($missingRequired) {
            $redirectPurok = $_GET['purokID'] ?? 'all';
            header("Location: index.php?page=records&purok=$redirectPurok&error=missing_fields");
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
                $birth_date,
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
                $birth_date,
                $sex,
                $civilstatus,
                $occupation,
                $contactnum,
                $profileImageData,
                $profileImageType
            );
        }

        // Handle medical files upload
        $medicalFiles = [];
        if (isset($_FILES['medical_files']) && !empty($_FILES['medical_files']['name'][0])) {
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

        // ADD THIS SECTION - Handle common illness selection
        $common_illness_id = filter_input(INPUT_POST, 'common_illness', FILTER_VALIDATE_INT);
    
        // DEBUG: Check what's being received

        if ($common_illness_id) {  // Adding new citizen - create new illness record
            add_illness_record($targetCitizenID, $purokID, $common_illness_id);
            
        }
        // END OF ILLNESS SECTION

        // Handle diagnosis/symptoms storage
        $symptoms = trim(filter_input(INPUT_POST, 'medical_condition') ?? '');
        $description = trim(filter_input(INPUT_POST, 'medical_notes') ?? '');
        
        if (!empty($symptoms)) {
            // Include the diagnosis database functions
            require_once('model/databases/diagnosisdb.php');
            
            // Add the diagnosis to the database
            add_diagnosis($targetCitizenID, $symptoms, $description);
        } else {
            update_diagnosis($targetCitizenID, $symptoms, $description);
        }

        $redirectPurok = $_GET['purokID'] ?? 'all';
        $successMessage = "success=1";
        if (!empty($medicalFiles)) {
            $successMessage .= "&medical_uploaded=1&files_count=" . count($medicalFiles);
        }
        if (!empty($symptoms)) {
            $successMessage .= "&diagnosis_saved=1";
        }
        // ADD THIS - Include illness saved message
        if ($common_illness_id) {
            $successMessage .= "&illness_saved=1";
        }

        header("Location: index.php?page=records&$successMessage");
        exit;
    }

    if ($action === 'archive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            archive_citizen($citID);
            header("Location: index.php?page=records");
        }
        $redirectPurok = $_GET['purokID'] ?? 'all';
        exit;
    }

    if ($action === 'unarchive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            restore_citizen($citID);
        }
        header("Location: index.php?page=records");
        exit;
    }
}

$purokID = $_GET['purokID'] ?? 'all';
$citizens = ($purokID === 'archived') ? get_archived_citizens() : get_citizens_by_purok($purokID);

// ADD THIS - Load illnesses for the dropdown
$illnesses = get_all_illnesses();

// Get pagination parameters
$page = filter_input(INPUT_GET, 'paging', FILTER_VALIDATE_INT) ?: 1;
$perPage = 15; // Records per page

// Get paginated citizens
$citizens = get_citizens_by_purok($purokID, $page, $perPage);
$totalCitizens = get_citizens_count($purokID);
$totalPages = ceil($totalCitizens / $perPage);

if(CURRENT_USER_IS_ADMIN){
    require_once('view/record.view.php');
}else{ 
    // Load both active and archived citizens
    $activeCitizens = get_citizens_by_purok($purokID);
    $archivedCitizens = get_archived_citizens();
    $citizens = array_merge($activeCitizens, $archivedCitizens);
    require_once('view/user_record.view.php');
}