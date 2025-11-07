<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments - DocuCare</title>
<link rel="stylesheet" href="styles/record.css">
 <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
</head>
<body>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
require('view/partials/sidebar.php');
?>

<div class="content">

    <?php if ($message): ?>
    <?php  
       include('view/partials/alert-bar.php');
       addalert($message);
    ?>
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
                                <?php if ($app['status'] !== 'Approved' && $app['status'] !== 'Denied'): ?>
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
            <label for="schedule">Schedule</label>
            <input 
                type="datetime-local" 
                id="scheduleInput"
                required 
                style="color: #333;"
            >
            <!-- Hidden field for formatted version -->
            <input type="hidden" name="schedule" id="formattedSchedule">
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-outline" type="reset" onclick="clearForm()">Reset</button>
            <button class="btn btn-outline" type="submit">Submit</button>
        </div>
        </form>
    </div>
    </div>

    <script>
    const form = document.getElementById('appointmentForm');
    const scheduleInput = document.getElementById('scheduleInput');
    const formattedSchedule = document.getElementById('formattedSchedule');

    form.addEventListener('submit', (e) => {
        const raw = scheduleInput.value; // e.g., "2025-11-13T00:30"
        if (!raw) return;

        const date = new Date(raw);
        const formatted = date.toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });

        formattedSchedule.value = formatted;
    });
    </script>

</div>

<script>
const appointments = <?= json_encode($appointments) ?>;
function editAppointment(id) {
    const app = appointments.find(a => a.id == id);
    if (!app) return;

    // Disallow editing approved appointments
    if (app.status === 'Approved') {
        alert('Cannot edit an approved appointment. The schedule is locked.');
        return;
    }

    // Set hidden values
    document.getElementById('appointmentID').value = app.id;
    document.getElementById('appointmentStatus').value = app.status;

    // Get references
    const scheduleField = document.getElementById('scheduleInput');
    const formattedField = document.getElementById('formattedSchedule');

    // Attempt to convert the saved schedule (string) to datetime-local format
    let parsedDate;
    if (app.schedule.includes('T')) {
        // Already looks like "2025-11-13T00:30"
        parsedDate = app.schedule;
    } else {
        // Convert a string like "November 13, 2025, 12:30 AM"
        parsedDate = new Date(app.schedule);
        if (!isNaN(parsedDate)) {
            const local = new Date(parsedDate.getTime() - (parsedDate.getTimezoneOffset() * 60000))
              .toISOString().slice(0,16);
            scheduleField.value = local; // "2025-11-13T00:30"
        } else {
            scheduleField.value = ''; // fallback
        }
    }

    // Set hidden formatted field (optional)
    formattedField.value = app.schedule;

    // Enable schedule field for editing (unless denied)
    scheduleField.disabled = app.status === 'Approved';
    scheduleField.style.backgroundColor = scheduleField.disabled ? '#f0f0f0' : '';
    scheduleField.style.cursor = scheduleField.disabled ? 'not-allowed' : '';

    // Scroll smoothly to the form
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}
</script>

</body>
</html>