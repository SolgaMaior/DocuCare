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
                            <td><?= date("F j, Y \\a\\t h:i A", strtotime($app['schedule'])) ?></td>
                            <td>
                                <span style="color:<?= $app['status'] === 'Approved' ? 'green' : ($app['status'] === 'Denied' ? 'red' : '#333') ?>">
                                    <?= htmlspecialchars($app['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['status'] !== 'Approved' && $app['status'] !== 'Denied'): ?>
                                    <button style="padding-top: 5px; padding-bottom: 5px;" class="btn btn-outline" onclick="editAppointment(<?= $app['id'] ?>)">Edit</button>
                                    <a href="index.php?page=appointments&delete_id=<?= $app['id'] ?>" onclick="return confirm('Delete this appointment?');" class="btn btn-outline">Delete</a>
                                <?php else: ?>
                                    <button style="padding-top: 5px; padding-bottom: 5px; opacity: 0.5; cursor: not-allowed;" class="btn btn-outline" disabled title="Cannot edit approved appointment">Edit</button>
                                    <a style="opacity: 0.5; cursor: not-allowed;" class="btn btn-outline" disabled title="Cannot delete approved appointment">Delete</a>
                                <?php endif; ?>

                                
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
    const appointments = <?= json_encode($appointments ?: []) ?>;

    function editAppointment(id) {
        const app = appointments.find(a => a.id == id);
        if (!app) return;

        // Prevent editing approved or denied appointments
        if (app.status === 'Approved' || app.status === 'Denied') {
            alert('Cannot edit an approved or denied appointment.');
            return;
        }

        const scheduleField = document.getElementById('scheduleInput');
        const appointmentID = document.getElementById('appointmentID');
        const appointmentStatus = document.getElementById('appointmentStatus');

        // Convert "November 14, 2025 at 08:59 PM" → "2025-11-14T20:59"
        let formattedValue = "";

        try {
            // Remove the "at" and parse the date
            const cleaned = app.schedule.replace(' at ', ' ');
            const parsedDate = new Date(cleaned);

            if (!isNaN(parsedDate)) {
                // Convert to local ISO format for datetime-local input
                const local = new Date(parsedDate.getTime() - parsedDate.getTimezoneOffset() * 60000);
                formattedValue = local.toISOString().slice(0, 16);
            }
        } catch (e) {
            console.error("Date parsing error:", e);
        }
        


        // Fill the fields
        appointmentID.value = app.id;
        appointmentStatus.value = app.status;
        scheduleField.value = formattedValue;

        // Enable editing
        scheduleField.disabled = false;
        scheduleField.style.backgroundColor = '';
        scheduleField.style.cursor = '';

        // Scroll to the form
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }


    form.addEventListener('submit', (e) => {
        const raw = scheduleInput.value; // e.g. "2025-11-14T20:59"
        if (!raw) {
            e.preventDefault();
            alert("Please select a schedule before submitting.");
            return;
        }

        const date = new Date(raw);
        if (isNaN(date.getTime())) {
            e.preventDefault();
            alert("Invalid date format.");
            return;
        }
        formattedSchedule.value = date.toISOString();
    });



    function clearForm() {
        document.getElementById('appointmentID').value = '';
        document.getElementById('appointmentStatus').value = '';
        const scheduleField = document.getElementById('scheduleInput');
        scheduleField.value = '';
        scheduleField.disabled = false;
        scheduleField.style.backgroundColor = '';
        scheduleField.style.cursor = '';
    }
</script>

</body>
</html>