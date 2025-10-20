<?php
// auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in

if (!isset($_SESSION['userID'])) {
    header('Location: index.php?page=login');
    exit;
}
?>