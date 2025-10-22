<?php
// controllers/authcontroller/forgot_password.controller.php - All-in-one reset
require_once('config/config.php');
require_once('model/databases/db_con.php');
require_once('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$error = '';
$success = '';
$step = $_GET['step'] ?? 'email'; // email, verify, or reset
$displayToken = ''; // For testing

// If already logged in, redirect to dashboard
if (isset($_COOKIE[COOKIE_NAME])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// STEP 1: Request reset (enter email)
if ($step === 'email' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    
    if ($email) {
        try {
            // Check if email exists
            $query = "SELECT userID FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if ($user) {
                // Generate reset token (6-digit code)
                $token = sprintf('%06d', mt_rand(100000, 999999));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save hashed token to database
                $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindValue(':token', $hashedToken);
                $updateStmt->bindValue(':expiry', $expiry);
                $updateStmt->bindValue(':email', $email);
                $updateStmt->execute();
                $updateStmt->closeCursor();
                
                // Store email in cookie
                setcookie('reset_email', $email, time() + 3600, COOKIE_PATH, COOKIE_SECURE, true);
                setcookie('reset_display_token', $token, time() + 3600, COOKIE_PATH, COOKIE_SECURE, true); // For testing
                
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // or your mail server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'docucareph@gmail.com'; // replace with your email
                    $mail->Password = app_password; // use App Password if Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('your_email@gmail.com', 'DocuCare');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your DocuCare Password Reset Code';
                    $mail->Body = "Your verification code is: <b>$token</b><br>This code will expire in 1 hour.";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Mail error: {$mail->ErrorInfo}");
                }

                
                // Move to verification step
                header('Location: index.php?page=forgot_password&step=verify');
                exit;
            } else {
                $error = 'Email not found';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
            error_log('Forgot password error: ' . $e->getMessage());
        }
    } else {
        $error = 'Please enter your email';
    }
}

// STEP 2: Verify code
if ($step === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $resetEmail = $_COOKIE['reset_email'] ?? '';
    
    if (!$resetEmail) {
        header('Location: index.php?page=forgot_password');
        exit;
    }
    
    if ($code) {
        try {
            $query = "SELECT userID, reset_token, reset_token_expiry FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':email', $resetEmail);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if ($user) {
                // Check expiry
                if (strtotime($user['reset_token_expiry']) < time()) {
                    $error = 'Reset code has expired. Please request a new one.';
                } elseif (password_verify($code, $user['reset_token'])) {
                    // Code is valid
                    setcookie('reset_verified', '1', time() + 600, COOKIE_PATH, COOKIE_SECURE, true);
                    header('Location: index.php?page=forgot_password&step=reset');
                    exit;
                } else {
                    $error = 'Invalid verification code';
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
            error_log('Verify code error: ' . $e->getMessage());
        }
    } else {
        $error = 'Please enter the verification code';
    }
}

// STEP 3: Set new password
if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $resetEmail = $_COOKIE['reset_email'] ?? '';
    $verified = $_COOKIE['reset_verified'] ?? '';
    
    if (!$resetEmail || !$verified) {
        header('Location: index.php?page=forgot_password');
        exit;
    }
    
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword && $confirmPassword) {
        if (strlen($newPassword) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            try {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL WHERE email = :email";
                $stmt = $db->prepare($updateQuery);
                $stmt->bindValue(':password', $hashedPassword);
                $stmt->bindValue(':email', $resetEmail);
                $stmt->execute();
                $stmt->closeCursor();
                
                // Clear cookies
                setcookie('reset_email', '', time() - 3600, COOKIE_PATH, COOKIE_SECURE, true);
                setcookie('reset_display_token', '', time() - 3600, COOKIE_PATH, COOKIE_SECURE, true);
                setcookie('reset_verified', '', time() - 3600, COOKIE_PATH, COOKIE_SECURE, true);

                $success = 'Password updated successfully! Redirecting to login...';
                header('Refresh: 2; url=index.php?page=login');
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again.';
                error_log('New password error: ' . $e->getMessage());
            }
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

// Set display token for testing (step 2 only)
if ($step === 'verify') {
    $displayToken = $_COOKIE['reset_display_token'] ?? 'N/A';
}

// Load the view
require('view/Auth/forgot_password.php');
?>