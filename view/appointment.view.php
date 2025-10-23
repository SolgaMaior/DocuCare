<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments - DocuCare</title>
<link rel="stylesheet" href="styles/record.css">
</head>
<body>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
require('view/partials/sidebar.php');
?>

<div class="content">
    <div class="header">
        <!-- <a href="logout.php" class="logout">Logout</a> -->
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div>
        <table>
            <thead class="hd">
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
                <?php if ($appointments): ?>
                    <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['lastname']) ?></td>
                            <td><?= htmlspecialchars($app['firstname']) ?></td>
                            <td><?= htmlspecialchars($app['middlename'] ?? '') ?></td>
                            <td><?= htmlspecialchars($app['sex'] ?? '') ?></td>
                            <td><?= htmlspecialchars($app['age'] ?? '') ?></td>
                            <td><?= htmlspecialchars($app['purokID'] ?? '') ?></td>
                            <td><?= htmlspecialchars($app['schedule']) ?></td>
                            <td>
                                <span style="color:<?= $app['status'] === 'Approved' ? 'green' : ($app['status'] === 'Denied' ? 'red' : '#333') ?>">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['status'] !== 'Approved'): ?>
                                    <button style="padding-top: 5px; padding-bottom: 5px;" class="btn btn-outline" onclick="editAppointment(<?= $app['id'] ?>)">Edit</button>
                                <?php else: ?>
                                    <button style="padding-top: 5px; padding-bottom: 5px; opacity: 0.5; cursor: not-allowed;" class="btn btn-outline" disabled title="Cannot edit approved appointment">Edit</button>
                                <?php endif; ?>
                                <a href="index.php?page=appointments&delete_id=<?= $app['id'] ?>" onclick="return confirm('Delete this appointment?');" class="btn btn-outline">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="text-align:center;">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:2rem;">
        <div class="card">
            <h3>Appointment Details</h3>
            <form method="POST" action="index.php?page=appointments" id="appointmentForm">
                <input type="hidden" name="appointmentID" id="appointmentID">
                <input type="hidden" name="appointmentStatus" id="appointmentStatus">
                <div class="form-grid">

                    <div class="form-field full-width">
                        <label>Schedule</label>
                        <select name="schedule" id="schedule" required onchange="this.style.color = this.value ? '#333' : '#707070ff'">
                            <option value="">Select</option>
                            <option>Monday - 9:00 AM</option>
                            <option>Tuesday - 10:00 AM</option>
                            <option>Wednesday - 1:00 PM</option>
                            <option>Thursday - 3:00 PM</option>
                            <option>Friday - 9:00 AM</option>
                        </select>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-outline" type="reset" onclick="clearForm()">Reset</button>
                    <button class="btn btn-outline" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const appointments = <?= json_encode($appointments) ?>;

function editAppointment(id) {
    const app = appointments.find(a => a.id == id);
    if (app) {
        // Check if appointment is approved
        if (app.status === 'Approved') {
            alert('Cannot edit an approved appointment. The schedule is locked.');
            return;
        }
        
        document.getElementById('appointmentID').value = app.id;
        document.getElementById('appointmentStatus').value = app.status;
        document.getElementById('schedule').value = app.schedule;
        
        // Disable schedule field if approved
        const scheduleField = document.getElementById('schedule');
        if (app.status === 'Approved') {
            scheduleField.disabled = true;
            scheduleField.style.backgroundColor = '#f0f0f0';
            scheduleField.style.cursor = 'not-allowed';
        } else {
            scheduleField.disabled = false;
            scheduleField.style.backgroundColor = '';
            scheduleField.style.cursor = '';
        }
        
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }
}

function clearForm() {
    document.getElementById('appointmentID').value = '';
    document.getElementById('appointmentStatus').value = '';
    document.getElementById('schedule').disabled = false;
    document.getElementById('schedule').style.backgroundColor = '';
    document.getElementById('schedule').style.cursor = '';
}
</script>

</body>
</html>