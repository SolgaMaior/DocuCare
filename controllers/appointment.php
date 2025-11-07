<?php
require('model/databases/appointmentdb.php');
require('model/databases/db_con.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule   = filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($schedule) {
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

            if ($currentAppointment && $currentAppointment['status'] === 'Approved' && $currentAppointment['status'] === 'Denied') {
                $message = "Cannot edit an approved appointment!";
            } else {
                update_appointment($id, $schedule);
                $message = "Appointment updated successfully!";
            }
        } else {
            add_appointment($schedule, CURRENT_USER_ID, CURRENT_CITIZEN_ID);
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

$appointments = get_appointments(CURRENT_USER_ID);
require_once('view/appointment.view.php');
?>