<?php
require_once('model/databases/citizensdb.php');
require_once('model/databases/db_con.php');
require_once('model/file_handler.php');

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

// Handle POST actions before any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'add_citizen') {
        $citID = $_POST['citID'] ?? null;
        $firstname = $_POST['first_name'] ?? '';
        $middlename = $_POST['middle_name'] ?? '';
        $lastname = $_POST['last_name'] ?? '';
        $purokID = isset($_POST['purok']) && $_POST['purok'] !== '' ? (int)$_POST['purok'] : null;
        $age = isset($_POST['age']) && $_POST['age'] !== '' ? (int)$_POST['age'] : null;
        $sex = $_POST['sex'] ?? null;
        $civilstatus = $_POST['civilstatus'] ?? null;
        $occupation = $_POST['occupation'] ?? '';
        $contactnum = $_POST['contactnum'] ?? '';

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
            header("Location: index.php?purokID=" . urlencode($redirectPurok) . "&error=missing_required_fields");
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
            $medicalCondition = $_POST['medical_condition'] ?? '';
            $medicalNotes = $_POST['medical_notes'] ?? '';
            
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
        
        header("Location: index.php?purokID=" . urlencode($redirectPurok) . "&" . $successMessage);
        exit;
    }









    if ($action === 'archive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            archive_citizen($citID);
        }
        // Preserve current purok filter
        $redirectPurok = $_GET['purokID'] ?? 'all';
        header("Location: index.php?purokID=" . urlencode($redirectPurok) . "&archived=1");
        exit;
    }

    if ($action === 'unarchive_citizen') {
        $citID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);
        if ($citID) {
            restore_citizen($citID);
        }
        header("Location: index.php?purokID=archived&unarchived=1");
        exit;
    }

}



// Get data for display (only after POST handling)
$purokID = $_GET['purokID'] ?? 'all';
$citizens = ($purokID === 'archived') ? get_archived_citizens() : get_citizens_by_purok($purokID);

// Include view
include('view/record.php');
