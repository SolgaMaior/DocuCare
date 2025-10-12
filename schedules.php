<?php
require('model/databases/appointmentdb.php');



$message = '';

// Handle status update
if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $id = (int)$_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        update_appointment_status($id, 'Approved');
        $message = "Appointment approved!";
    } elseif ($action === 'deny') {
        update_appointment_status($id, 'Denied');
        $message = "Appointment denied!";
    }
}

// Fetch all appointments ordered by schedule
$appointments = get_appointments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Schedules - DocuCare</title>
<link rel="stylesheet" href="styles/record.css">
</head>
<body>

<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<?php
require('view/partials/sidebar.php');
?>



<div class="content">
    <div class="header">
        <h2>Schedules</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

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
        <tbody >
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $app): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['lastname']) ?></td>
                        <td><?= htmlspecialchars($app['firstname']) ?></td>
                        <td><?= htmlspecialchars($app['middlename']) ?></td>
                        <td><?= htmlspecialchars($app['sex']) ?></td>
                        <td><?= htmlspecialchars($app['age']) ?></td>
                        <td><?= htmlspecialchars($app['purok']) ?></td>
                        <td><?= htmlspecialchars($app['schedule']) ?></td>
                        <td><?= htmlspecialchars($app['status']) ?></td>
                        <td style="display:flex; gap:5px;">
                            <?php if ($app['status'] === 'Pending'): ?>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-outline">Approve</button>
                                </form>
                                <form method="POST" style="margin:0;">
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
</div>

</body>
</html>