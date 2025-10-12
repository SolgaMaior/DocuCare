<?php
require_once(__DIR__ . '/../model/databases/appointmentdb.php');

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
<link rel="stylesheet" href="record.css">
<link rel="stylesheet" href="appointments.css">
</head>
<body>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <h2>Docu-care</h2>
  <ul class="menu">
    <li class="<?= ($current_page == 'dashboard.php') ? 'active' : '' ?>"><a href="dashboard.php">Dashboard</a></li>
    <li class="<?= ($current_page == 'record.php') ? 'active' : '' ?>"><a href="record.php">Citizen Records</a></li>
    <li class="<?= ($current_page == 'appointment.php') ? 'active' : '' ?>"><a href="appointment.php">Set Appointment</a></li>
    <li class="<?= ($current_page == 'schedules.php') ? 'active' : '' ?>"><a href="schedules.php">Schedules</a></li>
    <li class="<?= ($current_page == 'inventory.php') ? 'active' : '' ?>"><a href="inventory.php">Inventory</a></li>
  </ul>
</div>





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
        <tbody>
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
                                    <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
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
