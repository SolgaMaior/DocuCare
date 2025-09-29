<?php if (isset($_GET['success'])): ?>
  <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border: 1px solid #c3e6cb;">
    ✅ Citizen added successfully!
  </div>
<?php endif; ?>


<?php
$purokID = isset($_GET['purokID']) ? $_GET['purokID'] : null;
$citizens = get_citizens_by_purok($purokID);

// Handle form submission for adding new citizen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_citizen'])) {
  $firstname = $_POST['first_name'];
  $middlename = $_POST['middle_name'];
  $lastname = $_POST['last_name'];
  $purokID = $_POST['purok'];
  $age = $_POST['age'];
  $sex = $_POST['sex'];
  $civilstatus = $_POST['civilstatus'];
  $occupation = $_POST['occupation'];
  $contactnum = $_POST['contactnum'];

  add_citizen(null, $firstname, $middlename, $lastname, $purokID, $age, $sex, $civilstatus, $occupation, $contactnum);
  header("Location: index.php?success=1");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Records - DocuCare</title>
  <link rel="stylesheet" href="styles/record.css">
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Docu-care</h2>
    <ul class="menu">
      <li>🏠 Dashboard</li>
      <li class="active">📑 Citizen Records</li>
      <li>📅 Appointments</li>
      <li>📦 Inventory</li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="header">
      <h2 id="page-title">Citizen Records</h2>
      <a href="logout.php" class="logout">Logout</a>
    </div>

    <!-- Top Bar -->
    <div class="top-bar" id="top-controls">
      <div class="search-box">
        <input type="text" placeholder="Search...">
      </div>
      <div class="controls">
        <form method="GET" action="record.php">
          <select name="purokID" onchange="this.form.submit()">
            <option value="">All Puroks</option>
            <option value="1" <?= $purokID == 1 ? 'selected' : '' ?>>Purok 1</option>
            <option value="2" <?= $purokID == 2 ? 'selected' : '' ?>>Purok 2</option>
            <option value="3" <?= $purokID == 3 ? 'selected' : '' ?>>Purok 3</option>
            <option value="4" <?= $purokID == 4 ? 'selected' : '' ?>>Purok 4</option>
            <option value="5" <?= $purokID == 5 ? 'selected' : '' ?>>Purok 5</option>
          </select>
        </form>
        <button class="btn btn-outline">Filter</button>
        <button class="btn btn-primary" onclick="showForm()">+ Add Record</button>
      </div>
    </div>

    <!-- Records Table -->
    <div id="records-section">
      <table>
        <thead>
          <tr>
            <th>Photo</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Purok</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="records-table">
          <?php if (!empty($citizens)): ?>
            <?php foreach ($citizens as $citizen): ?>
              <tr>
                <td><img src="https://via.placeholder.com/50" alt="Photo"></td>
                <td><?= htmlspecialchars($citizen['lastname']); ?></td>
                <td><?= htmlspecialchars($citizen['firstname']); ?></td>
                <td><?= htmlspecialchars($citizen['middlename']); ?></td>
                <td><?= htmlspecialchars($citizen['purokID']); ?></td>
                <td>
                  <form method="POST" action="delete_citizen.php" style="display:inline;">
                    <input type="hidden" name="citID" value="<?= $citizen['citID']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                  </form>
                  <button class="btn btn-outline" onclick="editCitizen(<?= $citizen['citID']; ?>)">Edit</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align:center;">No records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Add Record Form -->
    <div id="form-section" style="display: none;">
      <form method="POST" enctype="multipart/form-data">
        <div class="card">
          <div class="upload-section">
            <img id="profilePreview" src="https://via.placeholder.com/100" alt="Profile">
            <button class="btn btn-primary" type="button" onclick="document.getElementById('uploadInput').click()">Upload new</button>
            <input type="file" id="uploadInput" accept="image/*" onchange="previewImage(event)">
          </div>

          <h3>Personal details</h3>
          <div class="form-grid">
            <input type="text" name="last_name" id="lastName" placeholder="Last Name" required>
            <input type="text" name="first_name" id="firstName" placeholder="First Name" required>
            <input type="text" name="middle_name" id="middleName" placeholder="Middle Name">
            <input type="text" name="sex" id="sex" placeholder="Sex" required>
            <input type="number" name="age" id="age" placeholder="Age" required>
            <input type="text" name="civilstatus" id="civilstatus" placeholder="Civil Status">
            <input type="text" name="occupation" id="occupation" placeholder="Occupation">
            <input type="text" name="contactnum" id="contactnum" placeholder="Contact Number">
            <input type="text" name="purok" id="purok" placeholder="Purok ID" required>
          </div>

          <div class="actions">
            <button type="button" class="btn btn-outline" onclick="showTable()">Cancel</button>
            <button type="submit" name="add_citizen" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    function showForm() {
      document.getElementById("records-section").style.display = "none";
      document.getElementById("form-section").style.display = "block";
    }

    function showTable() {
      document.getElementById("form-section").style.display = "none";
      document.getElementById("records-section").style.display = "block";
    }

    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        document.getElementById('profilePreview').src = reader.result;
      }
      reader.readAsDataURL(event.target.files[0]);
    }

    function editCitizen(id) {
      alert("Edit function for citizen ID " + id + " not implemented yet.");
    }
  </script>
</body>

</html>