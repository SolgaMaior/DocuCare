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
            // Fetch recipient email and associated citizen id before making changes
            $userQuery = "SELECT email, citID FROM users WHERE userID = :user_id";
            $userStmt = $db->prepare($userQuery);
            $userStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $userStmt->execute();
            $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$userRow || empty($userRow['email'])) {
                throw new PDOException('User email not found.');
            }

            $email = $userRow['email'];
            $citID = isset($userRow['citID']) ? (int)$userRow['citID'] : null;

            if ($action === 'approve') {
                $query1 = "UPDATE users SET isApproved = 1 WHERE userID = :user_id";
                $stmt = $db->prepare($query1);
                $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $query2 = "UPDATE citizens SET isArchived = 0 WHERE citID = :cit_id";
                $stmt = $db->prepare($query2);
                $stmt->bindValue(':cit_id', $citID, PDO::PARAM_INT);
                $stmt->execute();
                $message = "Account approved successfully!";
                $messageType = 'success';
                send_account_approval_status($email, 'approved');

            } elseif ($action === 'deny') {
                // Remove account and associated citizen record
                $db->beginTransaction();

                // Delete the user first to satisfy potential FK constraints
                $delUser = $db->prepare("DELETE FROM users WHERE userID = :user_id");
                $delUser->bindValue(':user_id', $user_id, PDO::PARAM_INT);
                $delUser->execute();

                // Then delete the citizen record if present
                if (!empty($citID)) {
                    $delCitizen = $db->prepare("DELETE FROM citizens WHERE citID = :cit_id");
                    $delCitizen->bindValue(':cit_id', $citID, PDO::PARAM_INT);
                    $delCitizen->execute();
                }

                $db->commit();

                $message = "Account denied successfully!";
                $messageType = 'success';
                send_account_approval_status($email, 'denied');
            }
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
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