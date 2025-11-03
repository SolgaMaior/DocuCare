<?php
require_once('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


function send_appointment_status($id, $status, $email) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'docucareph@gmail.com';
        $mail->Password = app_password; // App password, not Gmail login
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('docucareph@gmail.com', 'DocuCare');
        $mail->addAddress($email);

        $mail->isHTML(true);

        $date = get_appointment_date($id);
        $formattedDate = $date;

        if ($status === 'Approved') {
            $mail->Subject = 'Your Appointment Has Been Approved';
            $mail->Body = "Your appointment on {$formattedDate} has been approved.";
        } else {
            $mail->Subject = 'Your Appointment Has Been Denied';
            $mail->Body = "Your appointment on {$formattedDate} has been denied.";
        }

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}

function send_account_approval_status($email, $status) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'docucareph@gmail.com';
        $mail->Password = app_password; // App password, not Gmail login
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('docucareph@gmail.com', 'DocuCare');
        $mail->addAddress($email);

        $mail->isHTML(true);

        if ($status === 'approved') {
            $mail->Subject = 'Your Account Has Been Approved';
            $mail->Body = "Dear User,<br> Congratulations, your account has been approved.";
        } else {
            $mail->Subject = 'Your Account Has Been Denied';
            $mail->Body = "Dear User,<br> Unfortunately, your account has been denied.";
        }

        $mail->send();
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }
}
