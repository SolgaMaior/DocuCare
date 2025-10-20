<?php
require('model/databases/appointmentdb.php');
require('model/databases/db_con.php');



$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname   = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $firstname  = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $middlename = trim(filter_input(INPUT_POST, 'middlename', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $sex        = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $age        = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $purok      = filter_input(INPUT_POST, 'purok', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $schedule   = filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($lastname && $firstname && $sex && $age !== false && $purok && $schedule) {
        if (!empty($_POST['appointmentID'])) {
            $id = (int)$_POST['appointmentID'];
            
            // Check if appointment is approved before updating
            $currentAppointment = null;
            $allAppointments = get_appointments();
            foreach ($allAppointments as $app) {
                if ($app['id'] == $id) {
                    $currentAppointment = $app;
                    break;
                }
            }
            
            if ($currentAppointment && $currentAppointment['status'] === 'Approved') {
                $message = "Cannot edit an approved appointment!";
            } else {
                update_appointment($id, $lastname, $firstname, $middlename, $sex, $age, $purok, $schedule);
                $message = "Appointment updated successfully!";
            }
        } else {
            add_appointment($lastname, $firstname, $middlename, $sex, $age, $purok, $schedule);
            $message = "Appointment set successfully!";
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    delete_appointment($delete_id);
    $message = "Appointment deleted successfully!";
}

$appointments = get_appointments();
require_once('view/appointment.view.php');
?>