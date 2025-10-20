<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

require_once('model/databases/db_con.php');

$error = '';

// If already logged in, redirect to index
if (isset($_SESSION['userID'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND password IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                
                // Redirect to index
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

require('view/Auth/login.view.php');
?>