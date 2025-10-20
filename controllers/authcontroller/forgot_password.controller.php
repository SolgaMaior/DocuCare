<?php
session_start();
require_once('model/databases/db_con.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Save token to database
                $updateQuery = "UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindValue(':token', $token);
                $updateStmt->bindValue(':expiry', $expiry);
                $updateStmt->bindValue(':email', $email);
                $updateStmt->execute();
                $updateStmt->closeCursor();

                // Store email in session for reset
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_token'] = $token;

                // Redirect to reset page
                header('Location: reset_password.php');
                exit;
            } else {
                $error = 'Email not found';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    } else {
        $error = 'Please enter your email';
    }
}
?>