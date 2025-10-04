<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Records - DocuCare</title>
  <link rel="stylesheet" href="styles/record.css">
</head>

<body>

  <?php if (!empty($successMessage)) echo $successMessage; ?>

  <div class="sidebar">
    <h2>Docu-care</h2>
    <ul class="menu">
      <li>🏠 Dashboard</li>
      <li class="active">📑 Citizen Records</li>
      <li>📅 Appointments</li>
      <li>📦 Inventory</li>
    </ul>
  </div>

  <div class="content">
    <div class="header">
      <h2 id="page-title">Citizen Records</h2>
      <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="top-bar" id="top-controls">
      <div class="search-box">
        <input type="text" placeholder="Search..." id="searchInput" onkeyup="filterTable()">
      </div>

      <div class="controls">
        <form method="GET" action="index.php">
          <select name="purokID" onchange="this.form.submit()">
            <option value="all" <?= $purokID === 'all' ? 'selected' : '' ?>>All Puroks</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <option value="<?= $i ?>" <?= $purokID == $i ? 'selected' : '' ?>>Purok <?= $i ?></option>
            <?php endfor; ?>
            <option value="archived" <?= $purokID === 'archived' ? 'selected' : '' ?>>Archived</option>
          </select>
        </form>

        <button class="btn btn-primary" onclick="showForm()">+ Add Record</button>
      </div>
    </div>

    <!-- Citizens Table -->
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

        <tbody id="citizenTableBody">
          <?php if (!empty($citizens)): ?>
            <?php foreach ($citizens as $citizen): ?>
              <tr>
                <td>
                  <img src="<?= get_profile_image_path($citizen['firstname'], $citizen['lastname']) ?: 'resources/defaultprofile.png' ?>"
                    alt="Photo"
                    onerror="this.src='resources/defaultprofile.png'">
                </td>
                <td><?= htmlspecialchars($citizen['lastname']); ?></td>
                <td><?= htmlspecialchars($citizen['firstname']); ?></td>
                <td><?= htmlspecialchars($citizen['middlename']); ?></td>
                <td><?= htmlspecialchars($citizen['purokID']); ?></td>
                <td>
                  <!-- Archive / Unarchive -->
                  <form method="POST" action="index.php" style="display:inline;">
                    <input type="hidden" name="action"
                      value="<?= $citizen['isArchived'] == 1 ? 'unarchive_citizen' : 'archive_citizen' ?>">
                    <input type="hidden" name="citID" value="<?= htmlspecialchars($citizen['citID']) ?>">
                    <button type="submit" class="btn btn-outline"
                      onclick="return confirm('<?= $citizen['isArchived'] == 1 ? 'Remove from archive?' : 'Archive this record?' ?>');">
                      <?= $citizen['isArchived'] == 1 ? 'Unarchive' : 'Archive' ?>
                    </button>
                  </form>

                  <!-- Edit -->
                  <button type="button" class="btn btn-outline"
                    onclick="showEditForm(<?= $citizen['citID']; ?>)">Edit</button>
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

    <!-- Add/Edit Form -->
    <div id="form-section" style="display:none;">
      <form method="POST" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="citID" id="citID">
        <input type="hidden" name="action" id="formAction" value="add_citizen">

        <div class="card">
          <!-- Profile Image Upload -->
          <div class="upload-section">
            <img id="profilePreview"
              src="resources/defaultprofile.png"
              alt="Profile"
              onerror="this.src='resources/defaultprofile.png'">
            <br>
            <button class="btn btn-primary" type="button"
              onclick="document.getElementById('uploadInput').click()">Upload Photo</button>
            <input type="file" name="profileImage" id="uploadInput" accept="image/*"
              onchange="previewImage(event)" hidden>
            <br><br>
          </div>

          <h3>Personal Details</h3>
          <div class="form-grid">
            <input type="text" name="last_name" id="lastName" placeholder="Last Name" required autocomplete="off">
            <input type="text" name="first_name" id="firstName" placeholder="First Name" required autocomplete="off">
            <input type="text" name="middle_name" id="middleName" placeholder="Middle Name" autocomplete="off">
            <select name="sex" id="sex" required style="width: 84%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
              <option value="">Select Sex</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
            <input type="number" name="age" id="age" placeholder="Age" required autocomplete="off" min="0" max="150">
            <select name="civilstatus" id="civilstatus" required style="width: 84%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
              <option value="">Select Civil Status</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
            </select>
            <input type="text" name="occupation" id="occupation" placeholder="Occupation" required autocomplete="off">
            <input type="tel" name="contactnum" id="contactnum" placeholder="Contact Number" required autocomplete="off" pattern="[0-9]{10,11}">
            <select name="purok" id="purok" required style="width: 84%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
              <option value="">Select Purok</option>
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>">Purok <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="actions">
            <button type="button" class="btn btn-outline" onclick="showTable()">Cancel</button>
            <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    const citizensData = <?= json_encode($citizens); ?>;

    function showForm() {
      // Reset form for new entry
      document.getElementById('formAction').value = 'add_citizen';
      document.getElementById('submitButton').textContent = 'Submit';
      document.getElementById('citID').value = '';

      // Clear all inputs
      document.querySelectorAll('#form-section input[type="text"], #form-section input[type="number"], #form-section input[type="tel"]').forEach(i => i.value = '');

      // Reset selects and set color to gray
      document.querySelectorAll('#form-section select').forEach(s => {
        s.selectedIndex = 0;
        s.style.color = '#999';
      });

      // Reset image preview
      document.getElementById('profilePreview').src = 'resources/defaultprofile.png';
      document.getElementById('uploadInput').value = '';

      // Toggle sections
      document.getElementById("records-section").style.display = "none";
      document.getElementById("form-section").style.display = "block";
      document.getElementById("top-controls").style.display = "none";
    }

    function showTable() {
      document.getElementById("form-section").style.display = "none";
      document.getElementById("records-section").style.display = "block";
      document.getElementById("top-controls").style.display = "flex";
    }

    function previewImage(event) {
      const file = event.target.files[0];
      if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
          alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
          event.target.value = '';
          return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('File is too large. Maximum size is 5MB');
          event.target.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
          document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    }

    function showEditForm(citID) {
      const citizen = citizensData.find(c => c.citID == citID);
      if (!citizen) return alert('Citizen not found.');

      // Fill form with existing data
      document.getElementById('citID').value = citizen.citID;
      document.getElementById('firstName').value = citizen.firstname;
      document.getElementById('middleName').value = citizen.middlename || '';
      document.getElementById('lastName').value = citizen.lastname;
      document.getElementById('age').value = citizen.age;

      // Set select values and update color
      const sexSelect = document.getElementById('sex');
      sexSelect.value = citizen.sex;
      sexSelect.style.color = sexSelect.value ? '#333' : '#999';

      const civilstatusSelect = document.getElementById('civilstatus');
      civilstatusSelect.value = citizen.civilstatus;
      civilstatusSelect.style.color = civilstatusSelect.value ? '#333' : '#999';

      document.getElementById('occupation').value = citizen.occupation;
      document.getElementById('contactnum').value = citizen.contactnum;

      const purokSelect = document.getElementById('purok');
      purokSelect.value = citizen.purokID;
      purokSelect.style.color = purokSelect.value ? '#333' : '#999';

      // Load existing photo
      if (citizen.photo) {
        document.getElementById('profilePreview').src = citizen.photo;
      } else {
        document.getElementById('profilePreview').src = 'resources/defaultprofile.png';
      }

      // Switch to edit mode
      document.getElementById('formAction').value = 'add_citizen';
      document.getElementById('submitButton').textContent = 'Update';
      document.getElementById("records-section").style.display = "none";
      document.getElementById("form-section").style.display = "block";
      document.getElementById("top-controls").style.display = "none";
    }

    function filterTable() {
      const searchValue = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll("#citizenTableBody tr");

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? "" : "none";
      });
    }

    // Auto-hide alert after 3 seconds
    const alertMessage = document.getElementById('alertMessage');
    if (alertMessage) {
      setTimeout(() => {
        alertMessage.style.opacity = '0';
        setTimeout(() => alertMessage.remove(), 500);
      }, 3000);
    }
  </script>
</body>

</html>