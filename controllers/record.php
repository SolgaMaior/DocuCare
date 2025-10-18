<?php

require('model/databases/citizensdb.php');
require('model/databases/diagnosisdb.php');
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

        // Handle diagnosis/symptoms storage
        $symptoms = trim(filter_input(INPUT_POST, 'medical_condition') ?? '');
        $description = trim(filter_input(INPUT_POST, 'medical_notes') ?? '');
        
        if (!empty($symptoms)) {
            // Include the diagnosis database functions
            require_once('model/databases/diagnosisdb.php');
            
            // Add the diagnosis to the database
            add_diagnosis($targetCitizenID, $symptoms, $description);
        }

        $redirectPurok = $_GET['purokID'] ?? 'all';
        $successMessage = "success=1";
        if (!empty($medicalFiles)) {
            $successMessage .= "&medical_uploaded=1&files_count=" . count($medicalFiles);
        }
        if (!empty($symptoms)) {
            $successMessage .= "&diagnosis_saved=1";
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

require_once('view/record.view.php');