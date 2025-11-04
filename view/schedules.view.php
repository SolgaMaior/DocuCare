
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedules - DocuCare</title>
<link rel="stylesheet" href="styles/record.css">
<link rel="icon" type="image/svg" href="resources/images/Logo.svg">
</head>
<body>

<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
require('view/partials/sidebar.php');
?>

<div class="content">
    <div class="header"></div>
    

    <?php if ($message): ?>
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Sex</th>
                <th>Age</th>
                <th>Purok</th>
                <th>Schedule</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['lastname']) ?></td>
                        <td><?= htmlspecialchars($app['firstname']) ?></td>
                        <td><?= htmlspecialchars($app['middlename'] ?? '') ?></td>
                        <td><?= htmlspecialchars($app['sex'] ?? '') ?></td>
                        <td><?= htmlspecialchars($app['age'] ?? '') ?></td>
                        <td><?= htmlspecialchars($app['purokID'] ?? '') ?></td>
                        <td><?= htmlspecialchars($app['schedule'] ?? '') ?></td>
                        <td><?= htmlspecialchars($app['status'] ?? '') ?></td>
                        <td style="display:flex; gap:5px;">
                            <?php if ($app['status'] === 'Pending'): ?>
                                <form method="POST" action="index.php?page=schedules" style="margin:0;">
                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-outline">Approve</button>
                                </form>
                                <form method="POST" action="index.php?page=schedules" style="margin:0;">
                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                    <button type="submit" name="action" value="deny" class="btn btn-outline">Deny</button>
                                </form>
                                <?php else: ?>
                                    <span style="color:<?= $app['status'] === 'Approved' ? 'green' : 'red' ?>">
                                        <?= $app['status'] ?>
                                    </span>
                                    
                            <?php endif; ?>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center;">No appointments found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
      <div class="pagination" style="
          margin-top: 1rem;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 1rem;
      ">
          <?php if ($page > 1): ?>
              <a href="?page=schedules&paging=<?= $page - 1 ?>" 
                class="btn btn-outline pagination-btn">← Previous</a>
          <?php else: ?>
              <button class="btn btn-outline pagination-btn" disabled>← Previous</button>
          <?php endif; ?>

          <span class="page-info">
              Page <?= $page ?> of <?= $totalPages ?> 
          </span>

          <?php if ($page < $totalPages): ?>
              <a href="?page=schedules&paging=<?= $page + 1 ?>" 
                class="btn btn-outline pagination-btn">Next →</a>
          <?php else: ?>
              <button class="btn btn-outline pagination-btn" disabled>Next →</button>
          <?php endif; ?>
      </div>
    <?php endif; ?>
</div>

</body>
</html>