
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Citizen Records - DocuCare</title>
  <link rel="stylesheet" href="styles/record.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>

  <style>
    th, td  {
      text-align: center;
    }
  
  </style>
</head>

<body>
<?php
require('view/partials/sidebar.php');
?>

  <div class="content">
    <div class="header">
      <!-- <h2 id="page-title">Citizen Records</h2>
      <a href="logout.php" class="logout">Logout</a> -->
    </div>

    <div class="top-bar" id="top-controls">
      <div class="search-box">
        <input type="text" placeholder="Search..." id="searchInput" onkeyup="filterTable()">
      </div>

      <div class="controls">
        <form method="GET" action="index.php">
          <input type="hidden" name="page" value="records">
          <select id="filter" name="purokID" onchange="this.form.submit()">
            <option value="all" <?= $purokID === 'all' ? 'selected' : '' ?>>All Puroks</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <option value="<?= $i ?>" <?= $purokID == $i ? 'selected' : '' ?>>Purok <?= $i ?></option>
            <?php endfor; ?>
            <option value="archived" <?= $purokID === 'archived' ? 'selected' : '' ?>>Archived</option>
          </select>
        </form>
        <button class="btn btn-primary" style="height: 42px;" onclick="showForm()">+ Add Record</button>
      </div>
    </div>

 
    <div id="records-section">
      <table>
        <thead class="hd">
          <tr>
            <th>Photo</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Purok</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody id="citizenTableBody" > 
          <?php if (!empty($citizens)): ?>
            <?php foreach ($citizens as $citizen): ?>
              <tr >
                <td style="display: flex; justify-content: center; align-items: center;">
                  <img id="profileImage_"
                  src="model/record_file_func/display_image.php?citID=<?= htmlspecialchars($citizen['citID']) ?>"
                  alt="Profile"
                  onerror="this.src='resources/defaultprofile.png'">
                </td>
                <td><?= htmlspecialchars($citizen['lastname']); ?></td>
                <td><?= htmlspecialchars($citizen['firstname']); ?></td>
                <td><?= htmlspecialchars($citizen['middlename'] ?? ''); ?></td>
                <td><?= htmlspecialchars($citizen['purokID'] ?? ''); ?></td>
                <td>
                  <!-- Archive / Unarchive -->
                  <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action"
                      value="<?= $citizen['isArchived'] == 1 ? 'unarchive_citizen' : 'archive_citizen' ?>">
                    <input type="hidden" name="citID" value="<?= htmlspecialchars($citizen['citID']) ?>">
                    <button type="submit" class="btn btn-outline"
                      onclick="return confirm('<?= $citizen['isArchived'] == 1 ? 'Remove from archive?' : 'Archive this record?' ?>');">
                      <?= $citizen['isArchived'] == 1 ? 'Unarchive' : 'Archive' ?>
                    </button>
                    <button type="button" class="btn btn-outline" onclick="showEditForm(<?= $citizen['citID']; ?>)">Edit</button>
                    <button type="button" class="btn btn-outline" onclick="showViewForm(<?= $citizen['citID']; ?>)">View</button>
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



    
    <div id="form-section" style="display:none;">
      <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="citID" id="citID">
        <input type="hidden" name="action" id="formAction" value="add_citizen">

        <div class="card">
          <div class="upload-section">
            <img id="profilePreview"
              src="resources/defaultprofile.png"
              alt="Profile"
              onerror="this.src='resources/defaultprofile.png'">
            <br>
            <button id="uploadProfileButton" class="btn btn-outline" type="button"
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
        
          
          

          <div id="medicalFilesPreview" class="viewmedicalfiles">
              <h4 id="associatedRecordsHeader">Associated Records</h4>
              <div id="medicalFilesList"></div>
          </div>
 
          

          <div class="medical-files-section" style="display: none;">
            
            <h4>Medical Records</h4>

              <div class="form-field">
                <label for="medicalFiles">Upload Medical Records</label>
                <input type="file" class="dropify" name="medical_files[]" id="medicalFiles" multiple data-allowed-file-extensions="pdf doc docx jpg jpeg png gif" data-max-file-size="5M">
              </div>

          </div>


          <!-- Your existing medical diagnosis form -->
          <div class="medical-files-section" style="margin-top: 1rem;">
              <h4>Medical Diagnosis</h4>

              <div id="illnessHistoryPreview" style="display: none; margin-top: 1rem;">
                  <h2>Illness History</h2>
                  <div id="illnessHistoryList"></div>
              </div>
              
              <div id="common_illness_select" class="form-field" style="margin-top: 1rem;">
                  <label for="common_illness_label">Common Illness</label>
                  <select name="common_illness" id="commonIllness" onchange="this.style.color = this.value ? '#333' : '#707070ff'" style="width: 40%;">
                      <option value="">Select Common Illness</option>
                      <?php foreach ($illnesses as $illness): ?>
                          <option value="<?= htmlspecialchars($illness['illness_id']) ?>">
                              <?= htmlspecialchars($illness['illness_name']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <h4 style="margin-top: 1rem;">Illness Based on Symptoms</h4>

              <div class="form-field">
                  <label for="medicalCondition" >Symptoms</label>
                  <input 
                      type="text" 
                      name="medical_condition" 
                      id="medicalCondition" 
                      placeholder="Enter symptoms (e.g., persistent headache, sensitivity to light, nausea)" 
                      autocomplete="off"
                  >
              </div>
              
              <div class="form-field">
                  <label for="medicalNotes">Additional Description</label>
                  <textarea 
                      name="medical_notes" 
                      id="medicalNotes" 
                      placeholder="Enter any additional description about the patient's condition (e.g., duration, severity, location)" 
                      rows="3"
                  ></textarea>
              </div>
              
              <div style="display: flex; gap: 10px; margin-top: 10px;">
                  <button 
                      type="button" 
                      id="diagButton"
                      class="btn btn-outline" 
                      onclick="generateDiagnosis(<?= isset($citizen['citID']) ? $citizen['citID'] : 'null' ?>)"
                  >
                    Generate Diagnosis
                  </button>
                  
              </div>
          </div>

          <script>
            function generateDiagnosis(citizenID, firstname, middlename, lastname) {
              const symptoms = document.getElementById('medicalCondition').value.trim();
              const notes = document.getElementById('medicalNotes').value.trim();
              
              if (!symptoms) {
                  alert('Please enter symptoms before generating diagnosis.');
                  document.getElementById('medicalCondition').focus();
                  return;
              }
              
              // Build URL with parameters
              let url = 'index.php?page=diagnosis';
              url += '&symptoms=' + encodeURIComponent(symptoms);
              
              if (notes) {
                  url += '&additional_description=' + encodeURIComponent(notes);
              }
              
              if (citizenID) {
                  url += '&citID=' + citizenID;
              }
              
              // Open in new window or same window
              window.location.href = url;
            }

          </script>



          <div class="actions" style="margin-top: 1rem;">
            <button type="button" class="btn btn-outline" onclick="showTable()">Cancel</button>
            <button type="submit" id="submitButton" class="btn btn-outline">Submit</button>
          </div>
        </div>
      </form>
    </div>


  </div>

  <?php require('model/scripts/record_script.php'); ?>

  
  <?php if (isset($_GET['medical_uploaded']) && $_GET['medical_uploaded'] == '1'): ?>
    <div id="alertMessage" class="alert alert-success">
      Medical files uploaded successfully! 
      <?php if (isset($_GET['files_count'])): ?>
        (<?= $_GET['files_count'] ?> file<?= $_GET['files_count'] > 1 ? 's' : '' ?> uploaded)
      <?php endif; ?>
      <script>
        setTimeout(() => {
          document.getElementById("alertMessage").style.opacity = '0';
        }, 3000);
      </script>
    </div>
  <?php endif; ?>

</body>

</html>