<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Accounts - DocuCare</title>
    <link rel="stylesheet" href="styles/record.css">
    <link rel="stylesheet" href="styles/accounts_approve.css">
    <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
</head>
<body>
    <?php require('view/partials/sidebar.php'); ?>
    
    <div class="content">
        
        <?php if ($message): ?>
            <div class="alert-message alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <script>
                setTimeout(() => {
                    document.querySelector('.alert-message').style.opacity = '0';
                    setTimeout(() => {
                        document.querySelector('.alert-message').remove();
                    }, 300);
                }, 3000);
            </script>
        <?php endif; ?>
        
        <div class="approval-container">
            <div class="approval-header">
                <h2>Pending Account Approvals</h2>
                <p>Review and approve or deny new account requests</p>
            </div>
            
            <?php if (empty($pendingAccounts)): ?>
                <div class="no-accounts">
                    <div class="no-accounts-icon">👥</div>
                    <h3>No Pending Accounts</h3>
                    <p>All accounts have been reviewed. New account requests will appear here.</p>
                </div>
            <?php else: ?>
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>Email Address</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th>Status</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingAccounts as $account): ?>
                            <tr>
                                <td>
                                    <div class="account-info">
                                        <div class="account-email"><?= htmlspecialchars($account['email']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="account-name"><?= htmlspecialchars($account['lastname']) ?></div>
                                </td>
                                <td>
                                    <div class="account-name"><?= htmlspecialchars($account['firstname']) ?></div>
                                </td>
                                <td>
                                    <?php if ($account['isApproved'] === null || $account['isApproved'] == 0): ?>
                                        <span class="status-badge status-pending">Pending</span>
                                    <?php elseif ($account['isApproved'] == 1): ?>
                                        <span class="status-badge status-approved">Approved</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $account['userID'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn-approve" 
                                                    onclick="return confirm('Are you sure you want to approve this account?')">
                                                ✓ Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?= $account['userID'] ?>">
                                            <input type="hidden" name="action" value="deny">
                                            <button type="submit" class="btn-deny" 
                                                    onclick="return confirm('Are you sure you want to deny this account?')">
                                                ✗ Deny
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>