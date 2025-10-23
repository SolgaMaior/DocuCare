<?php
require('model/databases/appointmentdb.php');
require('model/mailer.php');

if (!CURRENT_USER_IS_ADMIN) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$message = '';


if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $id = (int)$_POST['appointment_id'];
    $email = get_user_email_by_appointment_id($id);
    $action = $_POST['action'];


    
    if ($action === 'approve') {
        update_appointment_status($id, 'Approved');
        send_appointment_status($id, 'Approved', $email);

        $message = "Appointment approved!";
    } elseif ($action === 'deny') {
        update_appointment_status($id, 'Denied');
        send_appointment_status($id, 'Denied', $email);
        $message = "Appointment denied!";
    }
    if ($action === 'delete') {
        delete_appointment($id);
        $message = "Appointment deleted!";
    }
}

$appointments = get_appointments();

require_once('view/schedules.view.php');
?>
