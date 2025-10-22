<?php
// logout.controller.php - Log out and clear cookie
require_once('config/config.php');




header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
