<?php
// logout.controller.php - Log out and clear cookie
require_once('config/config.php');

// Delete cookie
setcookie(
    COOKIE_NAME,
    '',
    time() - 3600,
    COOKIE_PATH,
    COOKIE_SECURE,
    COOKIE_HTTPONLY
);

// Optional: You can also destroy session (if using session_start())
session_start();
session_unset();
session_destroy();

// Redirect to login
header('Location: index.php?page=login');
exit;
?>
