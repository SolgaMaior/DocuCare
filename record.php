<?php
require('model/databases/citizensdb.php');
require('model/databases/db_con.php');



// Initialize variables
$citizenID = filter_input(INPUT_POST, 'citizenID', FILTER_VALIDATE_INT);
$purokID = filter_input(INPUT_POST, 'purokID', FILTER_VALIDATE_INT);

if (!$purokID) {
    $purokID = filter_input(INPUT_GET, 'purokID', FILTER_VALIDATE_INT);
}

// Get action from POST or GET
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
        $firstname = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING)) ?? '';
        $middlename = trim(filter_input(INPUT_POST, 'middle_name', FILTER_SANITIZE_STRING)) ?? '';
        $lastname = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING)) ?? '';
        $purokID = filter_input(INPUT_POST, 'purok', FILTER_VALIDATE_INT);
        $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
        $sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_STRING);
        $civilstatus = filter_input(INPUT_POST, 'civilstatus', FILTER_SANITIZE_STRING);
        $occupation = trim(filter_input(INPUT_POST, 'occupation', FILTER_SANITIZE_STRING)) ?? '';
        $contactnum = trim(filter_input(INPUT_POST, 'contactnum', FILTER_SANITIZE_STRING)) ?? '';

        // Basic validation for required fields
        $missingRequired = (
            $firstname === '' ||
            $lastname === '' ||
            $purokID === null ||
            $age === null ||
            $sex === null || $sex === '' ||
            $civilstatus === null || $civilstatus === '' ||
            $occupation === '' ||
            $contactnum === ''
        );
        if ($missingRequired) {
            $redirectPurok = $_GET['purokID'] ?? 'all';
            
            exit;
        }

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            makePatientDirectory($firstname, $lastname);
            $result = handleImageUpload($_FILES['profileImage'], $firstname, $lastname);

            if ($result['success']) {
                $imagePath = $result['path'];
            }
        }

        // Handle medical files upload using the existing storeFile function
        $medicalFiles = [];
        if (isset($_FILES['medical_files']) && !empty($_FILES['medical_files']['name'][0])) {
            $medicalCondition = trim(filter_input(INPUT_POST, 'medical_condition', FILTER_SANITIZE_STRING)) ?? '';
            $medicalNotes = trim(filter_input(INPUT_POST, 'medical_notes', FILTER_SANITIZE_STRING)) ?? '';
            
            // Process each uploaded file
            foreach ($_FILES['medical_files']['name'] as $key => $filename) {
                if ($_FILES['medical_files']['error'][$key] === UPLOAD_ERR_OK) {
                    // Create a single file array for the storeFile function
                    $fileArray = [
                        'name' => $_FILES['medical_files']['name'][$key],
                        'type' => $_FILES['medical_files']['type'][$key],
                        'tmp_name' => $_FILES['medical_files']['tmp_name'][$key],
                        'error' => $_FILES['medical_files']['error'][$key],
                        'size' => $_FILES['medical_files']['size'][$key]
                    ];
                    
                    // Use the existing storeFile function with file index
                    $result = storeFile($fileArray, $firstname, $lastname, $key);
                    
                    if ($result['success']) {
                        $medicalFiles[] = [
                            'path' => $result['path'],
                            'filename' => $result['filename'],
                            'original_name' => $filename,
                            'condition' => $medicalCondition,
                            'notes' => $medicalNotes
                        ];
                    }
                }
            }
        }

        if ($citID) {
            // Edit existing record
            update_citizen($citID, $firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum, $imagePath);
        } else {
            // Add new record
            add_citizen($firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum, $imagePath);
        }

        // Redirect with success message and preserve purokID filter
        $redirectPurok = $_GET['purokID'] ?? 'all';
        $successMessage = "success=1";
        
        // Add medical files upload success message if files were uploaded
        if (!empty($medicalFiles)) {
            $successMessage .= "&medical_uploaded=1&files_count=" . count($medicalFiles);
        }
        
        
        exit;
    }

    if ($action === 'archive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            archive_citizen($citID);
        }
        // Preserve current purok filter
        $redirectPurok = $_GET['purokID'] ?? 'all';
        
        exit;
    }

    if ($action === 'unarchive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            restore_citizen($citID);
        }
        
        exit;
    }

}

// Get data for display (only after POST handling)
$purokID = $_GET['purokID'] ?? 'all';
$citizens = ($purokID === 'archived') ? get_archived_citizens() : get_citizens_by_purok($purokID);

include('view/record.view.php');