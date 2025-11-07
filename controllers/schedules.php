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
    
    // Redirect to clear POST data and preserve page number
    header("Location: index.php?page=schedules&paging=$page&success=1");
    exit;
}

// Get pagination parameters
$page = filter_input(INPUT_GET, 'paging', FILTER_VALIDATE_INT) ?: 1;
$perPage = 15;

// Get status filter
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'all';

// Get paginated appointments based on filter
$appointments = get_all_appointments($page, $perPage, $status);
$totalAppointments = get_appointments_count($status);
$totalPages = ceil($totalAppointments / $perPage);


// Check for success message after redirect
if (isset($_GET['success'])) {
    $message = "Appointment status updated successfully!";
}

require_once('view/schedules.view.php');
?>