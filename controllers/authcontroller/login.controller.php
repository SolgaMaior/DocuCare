<?php
require_once('config/config.php');
require_once('model/databases/db_con.php');

$error = '';

// If already logged in (has valid cookie), redirect to index
if (isset($_COOKIE[COOKIE_NAME])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    if ($email && $password) {
        try {
            $query = "SELECT * FROM users WHERE email = :email AND password IS NOT NULL";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            if ($user && password_verify($password, $user['password']) && $user['isApproved']) {
                // Create authentication token
                $userID = (string) $user['userID'];  //  Cast to string
                $hash = hash_hmac('sha256', $userID, SECRET_KEY);
                $authToken = $userID . ':' . $hash;
                
                // Set cookie expiration (not implemented yet)
                $expiry = $rememberMe ? time() + (COOKIE_LIFETIME_DAYS * 24 * 60 * 60) : 0;
                
                // Set secure cookie
                setcookie(
                    COOKIE_NAME,
                    $authToken,
                    $expiry,
                    COOKIE_PATH,
                    COOKIE_SECURE,
                    COOKIE_HTTPONLY
                );
                
                // Redirect to dashboard
                header('Location: index.php');
                exit;
            } else if ($user && !password_verify($password, $user['password'])) {
                $error = 'Invalid email or password';
            }else if ($user && !$user['isApproved']) {
                $error = 'Account not approved. Please contact support.';
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
            error_log('Login error: ' . $e->getMessage());
        }
    } else {
        $error = 'Please fill in all fields';
    }
}

// NOW load the view (only once!)
require('view/Auth/login.view.php');
?>