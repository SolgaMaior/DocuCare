<?php
// authCheck.php - Cookie-based authentication
require_once('config/config.php');
require_once('model/databases/db_con.php');

function validateAuthToken($token, $db) {
    if (empty($token)) {
        return false;
    }

    try {
        // Split the token (userID:hash)
        $parts = explode(':', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        list($userID, $hash) = $parts;
        $userID = (string) trim($userID); // ✅ ensure consistent type

        // Get user from database
        $query = "SELECT userID, email, citID, isAdmin FROM users WHERE userID = :userID";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$user) {
            return false;
        }

        // Verify the hash using secret key from config
        $expectedHash = hash_hmac('sha256', (string)$userID, SECRET_KEY); // ✅ cast to string again

        if (!hash_equals($expectedHash, $hash)) {
            return false;
        }

        return $user;
    } catch (PDOException $e) {
        error_log('AuthCheck error: ' . $e->getMessage());
        return false;
    }
}

// Check if auth cookie exists
$authToken = $_COOKIE[COOKIE_NAME] ?? null;
$user = validateAuthToken($authToken, $db);

if (!$user) {
    // Clear invalid cookie
    setcookie(
        COOKIE_NAME,
        '',
        time() - 3600,
        COOKIE_PATH,
        COOKIE_SECURE,
        COOKIE_HTTPONLY
    );
    header('Location: index.php?page=login');
    exit;
}

// Make user data available globally (similar to session)
define('CURRENT_USER_ID', $user['userID']);
define('CURRENT_USER_EMAIL', $user['email']);
define('CURRENT_CITIZEN_ID', $user['citID'] ?? null);
define('CURRENT_USER_IS_ADMIN', $user['isAdmin'] ?? 0);
?>
