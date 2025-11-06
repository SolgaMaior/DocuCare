<?php

require_once('model/databases/db_con.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    global $db;

    if ($firstname && $lastname && $email && $password && $confirm) {
        if ($password === $confirm) {
            if (strlen($password) >= 8) {
                try {
                    //  Check if email already exists
                    $checkQuery = "SELECT userID FROM users WHERE email = :email";
                    $checkStmt = $db->prepare($checkQuery);
                    $checkStmt->bindValue(':email', $email);
                    $checkStmt->execute();

                    if ($checkStmt->fetch()) {
                        $error = 'Email already exists';
                    } else {

                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                        
                        // Create citizen record first
                        $citizenQuery = "INSERT INTO citizens (firstname, lastname, isArchived) VALUES (:firstname, :lastname, 1)";
                        $citizenStmt = $db->prepare($citizenQuery);
                        $citizenStmt->bindValue(':firstname', $firstname);
                        $citizenStmt->bindValue(':lastname', $lastname);
                        $citizenStmt->execute();
                        $citID = $db->lastInsertId();

                        // Create user record linked to citizen
                        $userQuery = "INSERT INTO users (citID, email, password) VALUES (:citID, :email, :password)";
                        $userStmt = $db->prepare($userQuery);
                        $userStmt->bindValue(':citID', $citID, PDO::PARAM_INT);
                        $userStmt->bindValue(':email', $email);
                        $userStmt->bindValue(':password', $hashedPassword);
                        $userStmt->execute();

                        // Step 5: Redirect after success
                        $success = 'Account created successfully! Your account is pending approval. You will be able to log in once approved by an administrator.';
                        header('Refresh: 3; URL=index.php?page=login');
                        exit;
                    }

                    $checkStmt->closeCursor();
                } catch (PDOException $e) {
                    $error = 'Account creation failed. Please try again.';
                }
            } else {
                $error = 'Password must be at least 8 characters';
            }
        } else {
            $error = 'Passwords do not match';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

require_once('view/Auth/create_account.view.php');
?>
