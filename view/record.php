<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Records - DocuCare</title>
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="resources/dropify/dist/css/dropify.min.css">
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
            <div class="form-field">
              <label for="lastName">Last Name</label>
              <input type="text" name="last_name" id="lastName" required autocomplete="off">
            </div>
            <div class="form-field">
              <label for="firstName">First Name</label>
              <input type="text" name="first_name" id="firstName" required autocomplete="off">
            </div>
            <div class="form-field">
              <label for="middleName">Middle Name</label>
              <input type="text" name="middle_name" id="middleName" autocomplete="off">
            </div>
            <div class="form-field">
              <label for="sex">Sex</label>
              <select name="sex" id="sex" required onchange="this.style.color = this.value ? '#333' : '#707070ff'">
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="form-field">
              <label for="age">Age</label>
              <input type="number" name="age" id="age" required autocomplete="off" min="0" max="150">
            </div>
            <div class="form-field">
              <label for="civilstatus">Civil Status</label>
              <select name="civilstatus" id="civilstatus" required onchange="this.style.color = this.value ? '#333' : '#707070ff'">
                <option value="">Select Civil Status</option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
                <option value="Separated">Separated</option>
              </select>
            </div>
            <div class="form-field">
              <label for="occupation">Occupation</label>
              <input type="text" name="occupation" id="occupation" required autocomplete="off">
            </div>
            <div class="form-field">
              <label for="contactnum">Contact Number</label>
              <input type="tel" name="contactnum" id="contactnum" required autocomplete="off" pattern="[0-9]{10,11}">
            </div>
            <div class="form-field">
              <label for="purok">Purok</label>
              <select name="purok" id="purok" required onchange="this.style.color = this.value ? '#333' : '#707070ff'">
                <option value="">Select Purok</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?= $i ?>">Purok <?= $i ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>

          <div id="addFile">
            <h3>Medical Records</h3>
            <div>
              <label for="medicalCondition">Medical Condition</label>
              <input type="text" name="medical_condition[]" id="medicalCondition" autocomplete="off">
            </div>
            <div>
              <h2>Upload Medical Records</h2>
              <input type="file" class="dropify" data-default-file="url_of_your_file" multiple />
              <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
              <script src="resources/dropify/dist/js/dropify.min.js"></script>
              <script>
                $(document).ready(function() {
                  // Basic
                  $('.dropify').dropify();

                  // Translated
                  $('.dropify-fr').dropify({
                    messages: {
                      default: 'Glissez-déposez un fichier ici ou cliquez',
                      replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                      remove: 'Supprimer',
                      error: 'Désolé, le fichier trop volumineux'
                    }
                  });

                  // Used events
                  var drEvent = $('#input-file-events').dropify();

                  drEvent.on('dropify.beforeClear', function(event, element) {
                    return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
                  });

                  drEvent.on('dropify.afterClear', function(event, element) {
                    alert('File deleted');
                  });

                  drEvent.on('dropify.errors', function(event, element) {
                    console.log('Has Errors');
                  });

                  var drDestroy = $('#input-file-to-destroy').dropify();
                  drDestroy = drDestroy.data('dropify')
                  $('#toggleDropify').on('click', function(e) {
                    e.preventDefault();
                    if (drDestroy.isDropified()) {
                      drDestroy.destroy();
                    } else {
                      drDestroy.init();
                    }
                  })
                });
              </script>
            </div>
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