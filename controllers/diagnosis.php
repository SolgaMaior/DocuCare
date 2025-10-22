<?php
// controllers/diagnosis_controller.php

require_once('model/record_file_func/diagnosis_api.php');
require_once('model/databases/db_con.php');



// Initialize variables
$diagnosisResults = null;
$symptoms = '';
$additionalDescription = '';
$error = '';
$citizenID = null;

$citizenData = null;
$firstname = '';
$middlename = '';
$lastname = '';


    




// Get data from URL parameters (passed from records page)
if (isset($_GET['symptoms'])) {
    $symptoms = trim($_GET['symptoms']);
    $additionalDescription = trim($_GET['additional_description'] ?? '');
    $citizenID = filter_input(INPUT_GET, 'citID', FILTER_VALIDATE_INT);
    
    // Automatically generate diagnosis if symptoms are provided
    if (!empty($symptoms)) {
        $diseasesList = trim($_GET['diseases_list'] ?? '');
        
        $diagnosisAPI = new DiagnosisAPI();
        $diagnosisResults = $diagnosisAPI->getDiagnosis($symptoms, $additionalDescription, $diseasesList);

        if (!$diagnosisResults['success']) {
            $error = $diagnosisResults['error'];
            $diagnosisResults = null;
        }
    }
}

// Handle form submission (if user wants to regenerate)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action');

    if ($action === 'generate_diagnosis') {
        $symptoms = trim(filter_input(INPUT_POST, 'symptoms') ?? '');
        $additionalDescription = trim(filter_input(INPUT_POST, 'additional_description') ?? '');
        $diseasesList = trim(filter_input(INPUT_POST, 'diseases_list') ?? '');
        $citizenID = filter_input(INPUT_POST, 'citID', FILTER_VALIDATE_INT);

        if (empty($symptoms)) {
            $error = 'Please enter symptoms to generate diagnosis.';
        } else {
            $diagnosisAPI = new DiagnosisAPI();
            $diagnosisResults = $diagnosisAPI->getDiagnosis($symptoms, $additionalDescription, $diseasesList);

            if (!$diagnosisResults['success']) {
                $error = $diagnosisResults['error'];
                $diagnosisResults = null;
            }
        }
    }

    if ($action === 'clear_results') {
        $diagnosisResults = null;
        $symptoms = '';
        $additionalDescription = '';
        $error = '';
        $citizenID = null;
    }
}

    
if ($citizenID) {
    require_once('model/databases/citizensdb.php');
    $citizenData = get_citizens_by_id($citizenID);

    if ($citizenData) {
        $firstname = $citizenData['firstname'] ?? '';
        $middlename = $citizenData['middlename'] ?? '';
        $lastname = $citizenData['lastname'] ?? '';
    }
}

require_once('view/diagnosis.view.php');
?>