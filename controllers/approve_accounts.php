<?php
require_once('model/databases/db_con.php');
require_once('model/mailer.php');

if (!CURRENT_USER_IS_ADMIN) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

$message = '';
$messageType = '';

// Handle approval/denial actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $action = $_POST['action'];
        $user_id = (int)$_POST['user_id'];
        
        try {
            // Fetch recipient email before making changes
            $emailQuery = "SELECT email FROM users WHERE userID = :user_id";
            $emailStmt = $db->prepare($emailQuery);
            $emailStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $emailStmt->execute();
            $email = $emailStmt->fetchColumn();

            if (!$email) {
                throw new PDOException('User email not found.');
            }

            if ($action === 'approve') {
                $query = "UPDATE users SET isApproved = 1 WHERE userID = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Account approved successfully!";
                $messageType = 'success';
                send_account_approval_status($email, 'approved');

            } elseif ($action === 'deny') {
                $query = "DELETE FROM users WHERE userID = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Account denied successfully!";
                $messageType = 'success';
                send_account_approval_status($email, 'denied');
            }
        } catch (PDOException $e) {
            $message = "Error processing request. Please try again.";
            $messageType = 'error';
        }
    }
}

// Fetch pending accounts (users that are not yet approved)
try {
    $query = "SELECT u.userID, u.email, c.firstname, c.lastname, u.isApproved 
              FROM users u 
              JOIN citizens c ON u.citID = c.citID 
              WHERE u.isApproved IS NULL OR u.isApproved = 0
              ORDER BY u.userID DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $pendingAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pendingAccounts = [];
    $message = "Error fetching accounts. Please try again.";
    $messageType = 'error';
}

require_once('view/accounts_approve.view.php');
?>