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
        $firstname = $_POST['first_name'];
        $middlename = $_POST['middle_name'];
        $lastname = $_POST['last_name'];
        $purokID = $_POST['purok'];
        $age = $_POST['age'];
        $sex = $_POST['sex'];
        $civilstatus = $_POST['civilstatus'];
        $occupation = $_POST['occupation'];
        $contactnum = $_POST['contactnum'];

        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] !== UPLOAD_ERR_NO_FILE) {
            makePatientDirectory($firstname, $lastname);
            $result = handleImageUpload($_FILES['profileImage'], $firstname, $lastname);

            if ($result['success']) {
                $imagePath = $result['path'];
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
        header("Location: index.php?purokID=" . urlencode($redirectPurok) . "&success=1");
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
