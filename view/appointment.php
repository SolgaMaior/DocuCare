<?php
require_once(__DIR__ . '/../model/databases/appointmentdb.php');

$message = '';

// Handle form submission (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname   = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $firstname  = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $middlename = trim(filter_input(INPUT_POST, 'middlename', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $sex        = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $age        = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $purok      = filter_input(INPUT_POST, 'purok', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $schedule   = filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if ($lastname && $firstname && $sex && $age !== false && $purok && $schedule) {
        if (!empty($_POST['appointmentID'])) {
            $id = (int)$_POST['appointmentID'];
            update_appointment($id, $lastname, $firstname, $middlename, $sex, $age, $purok, $schedule);
            $message = "Appointment updated successfully!";
        } else {
            add_appointment($lastname, $firstname, $middlename, $sex, $age, $purok, $schedule);
            $message = "Appointment set successfully!";
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    delete_appointment($delete_id);
    $message = "Appointment deleted successfully!";
}

// Fetch all appointments
$appointments = get_appointments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments - DocuCare</title>
<link rel="stylesheet" href="record.css">

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
        <h2>Set Appointment</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Appointments Table -->
    <div>
        <h3>Existing Appointments</h3>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments): ?>
                    <?php foreach ($appointments as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['lastname']) ?></td>
                            <td><?= htmlspecialchars($app['firstname']) ?></td>
                            <td><?= htmlspecialchars($app['middlename']) ?></td>
                            <td><?= htmlspecialchars($app['sex']) ?></td>
                            <td><?= htmlspecialchars($app['age']) ?></td>
                            <td><?= htmlspecialchars($app['purok']) ?></td>
                            <td><?= htmlspecialchars($app['schedule']) ?></td>
                            <td>
                                <button onclick="editAppointment(<?= $app['id'] ?>)">Edit</button>
                                <a href="appointment.php?delete_id=<?= $app['id'] ?>" onclick="return confirm('Delete this appointment?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center;">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Form -->
    <div style="margin-top:2rem;">
        <div class="card">
            <h3>Appointment Details</h3>
            <form method="POST" id="appointmentForm">
                <input type="hidden" name="appointmentID" id="appointmentID">
                <div class="form-grid">
                    <div class="form-field">
                        <label>Last Name</label>
                        <input type="text" name="lastname" id="lastname" required>
                    </div>
                    <div class="form-field">
                        <label>First Name</label>
                        <input type="text" name="firstname" id="firstname" required>
                    </div>
                    <div class="form-field">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" id="middlename">
                    </div>
                    <div class="form-field">
                        <label>Sex</label>
                        <select name="sex" id="sex" required>
                            <option value="">Select</option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Age</label>
                        <input type="number" name="age" id="age" min="0" max="120" required>
                    </div>
                    <div class="form-field">
                        <label>Purok</label>
                        <select name="purok" id="purok" required>
                            <option value="">Select</option>
                            <option value="1">Purok 1</option>
                            <option value="2">Purok 2</option>
                            <option value="3">Purok 3</option>
                            <option value="4">Purok 4</option>
                            <option value="5">Purok 5</option>
                        </select>
                    </div>
                    <div class="form-field full-width">
                        <label>Schedule</label>
                        <select name="schedule" id="schedule" required>
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
                    <button type="reset">Cancel</button>
                    <button type="submit">Submit</button>
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
        document.getElementById('appointmentID').value = app.id;
        document.getElementById('lastname').value = app.lastname;
        document.getElementById('firstname').value = app.firstname;
        document.getElementById('middlename').value = app.middlename;
        document.getElementById('sex').value = app.sex;
        document.getElementById('age').value = app.age;
        document.getElementById('purok').value = app.purok;
        document.getElementById('schedule').value = app.schedule;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

</body>
</html>
