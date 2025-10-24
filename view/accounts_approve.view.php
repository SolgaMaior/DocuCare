<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Accounts - DocuCare</title>
    <link rel="stylesheet" href="styles/record.css">
    <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
    <style>
        .approval-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .approval-header {
            background: linear-gradient(135deg, #7489c5, #9FB4ED);
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        
        .approval-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .approval-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .accounts-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .accounts-table th {
            background: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 16px 20px;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .accounts-table td {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        
        .accounts-table td:last-child {
            text-align: center;
            padding: 20px 15px;
        }
        
        .accounts-table tbody tr:hover {
            background: #f8f9fa;
            transition: background-color 0.2s ease;
        }
        
        .account-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .account-email {
            font-weight: 600;
            color: #495057;
            font-size: 0.95rem;
        }
        
        .account-name {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn-approve {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 2px solid rgba(40, 167, 69, 0.3);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
            min-width: 100px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-approve:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(40, 167, 69, 0.3);
            background: rgba(40, 167, 69, 0.3);
            color: #1e7e34;
            font-weight: 700;
            border-color: rgba(40, 167, 69, 0.5);
        }
        
        .btn-deny {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 2px solid rgba(220, 53, 69, 0.3);
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
            min-width: 100px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-deny:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(220, 53, 69, 0.3);
            background: rgba(220, 53, 69, 0.3);
            color: #c82333;
            font-weight: 700;
            border-color: rgba(220, 53, 69, 0.5);
        }
        
        .no-accounts {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-accounts-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-denied {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .alert-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .form-hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php require('view/partials/sidebar.php'); ?>
    
    <div class="content">
        <div class="header">
            <h2 id="page-title">Account Approval</h2>
        </div>
        
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