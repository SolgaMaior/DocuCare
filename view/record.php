<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Records - DocuCare</title>
  <link rel="stylesheet" href="styles/record.css">
</head>

<body>

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
          <select id="filter" name="purokID" onchange="this.form.submit()">
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
                <td style="display: flex; gap: 5px; border: none; align-content: center; margin-top: .6rem;">
                  <!-- Archive / Unarchive -->
                  <form method="POST" action="index.php" style="display: inline;">
                    <input type="hidden" name="action"
                      value="<?= $citizen['isArchived'] == 1 ? 'unarchive_citizen' : 'archive_citizen' ?>">
                    <input type="hidden" name="citID" value="<?= htmlspecialchars($citizen['citID']) ?>">
                    <button type="submit" class="btn btn-outline"
                      onclick="return confirm('<?= $citizen['isArchived'] == 1 ? 'Remove from archive?' : 'Archive this record?' ?>');">
                      <?= $citizen['isArchived'] == 1 ? 'Unarchive' : 'Archive' ?>
                    </button>
                    <button type="button" class="btn btn-outline" onclick="showEditForm(<?= $citizen['citID']; ?>)">Edit</button>
                    <button type="button" class="btn btn-outline" onclick="viewRecord(<?= $citizen['citID']; ?>)">View</button>
                  </form>

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
              src="<?= get_profile_image_path($citizen['firstname'], $citizen['lastname']) ?: 'resources/defaultprofile.png' ?>"
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
            <select name="sex" id="sex" required style="width: 100%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
              <option value="">Select Sex</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
            <input type="number" name="age" id="age" placeholder="Age" required autocomplete="off" min="0" max="150">
            <select name="civilstatus" id="civilstatus" required style="width: 100%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
              <option value="">Select Civil Status</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
            </select>
            <input type="text" name="occupation" id="occupation" placeholder="Occupation" required autocomplete="off">
            <input type="tel" name="contactnum" id="contactnum" placeholder="Contact Number" required autocomplete="off" pattern="[0-9]{10,11}">
            <select name="purok" id="purok" required style="width: 100%; padding: 10px; border: 1px solid #ccc; background-color: #fff; border-radius: 6px; font-family: Arial, sans-serif; font-size: 14px; box-sizing: border-box; color: #707070ff;" onchange="this.style.color = this.value ? '#333' : '#707070ff'">
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

  <?php require('model/scripts/record_script.php'); ?>

</body>

</html>