<script>
  (function() {
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, null, window.location.href);
      window.history.pushState(null, null, window.location.href);
      window.addEventListener('popstate', function(event) {
        window.history.pushState(null, null, window.location.href);
      });
    }
  })();

  const citizensData = <?= json_encode($citizens); ?>;
  const citizenPhotos = {};
  const citizenMedicalFiles = {};
  const citizenDiagnoses = {};
  const citizenIllnessRecords = {};

  <?php 
  require_once('model/databases/diagnosisdb.php');
  require_once('model/databases/illnessdb.php');
  foreach ($citizens as $citizen): 
    $diagnoses = getdiagnoses($citizen['citID']);
    $illnessRecords = get_citizen_illness_records($citizen['citID']);
  ?>
    citizenPhotos[<?= $citizen['citID'] ?>] = <?= json_encode("model/record_file_func/display_image.php?citID=" . $citizen['citID']) ?>;
    citizenMedicalFiles[<?= $citizen['citID'] ?>] = <?= json_encode(get_citizen_file_data($citizen['citID'])) ?>;
    citizenDiagnoses[<?= $citizen['citID'] ?>] = <?= json_encode($diagnoses) ?>;
    citizenIllnessRecords[<?= $citizen['citID'] ?>] = <?= json_encode($illnessRecords) ?>;
  <?php endforeach; ?>

  // Set max date for birth date input to today
  document.addEventListener('DOMContentLoaded', function() {
    const birthDateInput = document.getElementById('birthDate');
    if (birthDateInput) {
      const today = new Date().toISOString().split('T')[0];
      birthDateInput.setAttribute('max', today);
    }
  });

  // Calculate age from birth date
  function calculateAge(birthDate) {
    if (!birthDate) return '';
    const birth = new Date(birthDate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--;
    }
    return age;
  }

  // Update age display when birth date changes
  const birthDateInput = document.getElementById('birthDate');
  const ageDisplay = document.getElementById('ageDisplay');
  
  if (birthDateInput && ageDisplay) {
    birthDateInput.addEventListener('change', function() {
      const age = calculateAge(this.value);
      ageDisplay.value = age ? age + ' years old' : '';
    });
  }

  function initializeDropify() {
    if (typeof $ !== 'undefined' && $.fn.dropify) {
        $('.dropify').dropify({
          messages: {
            default: 'Drag and drop medical files here or click to select',
            replace: 'Drag and drop files here or click to replace',
            remove: 'Remove',
            error: 'Sorry, file is too large or invalid format'
          },
          error: {
            'fileSize': 'The file size is too big ({{ value }} max).',
            'minWidth': 'The image width is too small ({{ value }}px min).',
            'maxWidth': 'The image width is too big ({{ value }}px max).',
            'minHeight': 'The image height is too small ({{ value }}px min).',
            'maxHeight': 'The image height is too big ({{ value }}px max).',
            'imageFormat': 'The image format is not allowed ({{ value }} only).',
            'fileExtension': 'The file is not allowed ({{ value }} only).'
          }
        });
    }
  }

  function showForm() {
    resetForm();
    document.getElementById('formAction').value = 'add_citizen';
    document.getElementById('submitButton').textContent = 'Submit';
    document.getElementById('citID').value = '';
    document.querySelectorAll('#form-section input[type="text"], #form-section input[type="number"], #form-section input[type="tel"], #form-section input[type="date"]').forEach(i => i.value = '');
    document.querySelectorAll('#form-section select').forEach(s => {
      s.selectedIndex = 0;
      s.style.color = '#707070ff';
    });
    document.getElementById('profilePreview').src = 'resources/defaultprofile.png';
    document.getElementById('uploadInput').value = '';
    document.getElementById('ageDisplay').value = '';
    
    document.getElementById('medicalCondition').value = '';
    document.getElementById('medicalNotes').value = '';
    document.getElementById('medicalFiles').value = '';
    
    document.getElementById("records-section").style.display = "none";
    document.getElementById("form-section").style.display = "block";
    document.getElementById("top-controls").style.display = "none";

    const form = document.querySelector('#form-section form');
    form.reset();
    document.getElementById('citID').value = '';
    document.getElementById('formAction').value = 'add_citizen';
    
    document.getElementById('diagButton').style.display = 'none';
    setTimeout(() => {
      initializeDropify();
      const medicalSection = document.querySelector('.medical-files-section');
      if (medicalSection) medicalSection.style.display = 'block';
      document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);
  }

  function showEditForm(citID) {
    resetForm();
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');
    
    const diagnoses = citizenDiagnoses[citID] || [];
    const latestDiagnosis = diagnoses.length > 0 ? diagnoses[diagnoses.length - 1] : null;
    
    if (latestDiagnosis) {
      document.getElementById('medicalCondition').value = latestDiagnosis.symptoms || '';
      document.getElementById('medicalNotes').value = latestDiagnosis.description || '';
      document.getElementById('medicalCondition').readOnly = false;
      document.getElementById('medicalNotes').readOnly = false;
    } else {
      document.getElementById('medicalCondition').value = '';
      document.getElementById('medicalNotes').value = '';
    }
    
    document.getElementById('medicalFiles').value = '';
    document.getElementById('profilePreview').src = citizenPhotos[citID] || 'resources/defaultprofile.png';
    
    document.getElementById('citID').value = citizen.citID;
    document.getElementById('firstName').value = citizen.firstname;
    document.getElementById('middleName').value = citizen.middlename || '';
    document.getElementById('lastName').value = citizen.lastname;
    
    // Set birth date and calculate age
    if (citizen.birth_date) {
      document.getElementById('birthDate').value = citizen.birth_date;
      const age = calculateAge(citizen.birth_date);
      document.getElementById('ageDisplay').value = age ? age + ' years old' : '';
    }

    const sexSelect = document.getElementById('sex');
    sexSelect.value = citizen.sex;
    sexSelect.style.color = sexSelect.value ? '#333' : '#707070ff';

    const civilstatusSelect = document.getElementById('civilstatus');
    civilstatusSelect.value = citizen.civilstatus;
    civilstatusSelect.style.color = civilstatusSelect.value ? '#333' : '#707070ff';

    document.getElementById('occupation').value = citizen.occupation;
    document.getElementById('contactnum').value = citizen.contactnum;

    const purokSelect = document.getElementById('purok');
    purokSelect.value = citizen.purokID;
    purokSelect.style.color = purokSelect.value ? '#333' : '#707070ff';

    const historyContainer = document.getElementById('illnessHistoryPreview');
    historyContainer.style.display = 'none';

    const illnessRecords = citizenIllnessRecords[citID] || [];
    const latestIllness = illnessRecords.length > 0 ? illnessRecords[0] : null;
    
    if (latestIllness) {
        document.getElementById('commonIllness').value = '';
        document.getElementById('commonIllness').style.color = '#333';
    } else {
        document.getElementById('commonIllness').value = '';
        document.getElementById('commonIllness').style.color = '#707070ff';
    }

    const medicalSection = document.querySelector('.viewmedicalfiles');
    if (medicalSection) {
        medicalSection.style.display = 'none';
    }
    
    document.getElementById('formAction').value = 'add_citizen';
    document.getElementById('submitButton').textContent = 'Update';
    document.getElementById("records-section").style.display = "none";
    document.getElementById("form-section").style.display = "block";
    document.getElementById('associatedRecordsHeader').style.display = 'none';
    document.getElementById('medicalFilesPreview').style.display = 'none';
    document.getElementById("top-controls").style.display = "none";
    document.getElementById('diagButton').style.display = 'none';
      
    setTimeout(() => {
      initializeDropify();
      const medicalSection = document.querySelector('.medical-files-section');
      if (medicalSection) medicalSection.style.display = 'block';
      document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);
      
    showAssociatedRecords(citID, true);
  }

  function showViewForm(citID) {
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');
    resetForm();
    
    const diagnoses = citizenDiagnoses[citID] || [];
    const latestDiagnosis = diagnoses.length > 0 ? diagnoses[diagnoses.length - 1] : null;
    
    if (latestDiagnosis) {
      document.getElementById('medicalCondition').value = latestDiagnosis.symptoms || '';
      document.getElementById('medicalNotes').value = latestDiagnosis.description || '';
      document.getElementById('medicalCondition').readOnly = true;
      document.getElementById('medicalNotes').readOnly = true;
    } else {
      document.getElementById('medicalCondition').value = 'No diagnosis recorded';
      document.getElementById('medicalNotes').value = 'No additional notes';
      document.getElementById('medicalCondition').readOnly = true;
      document.getElementById('medicalNotes').readOnly = true;
    }
    
    showIllnessHistory(citID);
    document.getElementById('profilePreview').src = citizenPhotos[citID] || 'resources/defaultprofile.png';
    document.getElementById('uploadProfileButton').style.display = 'none';

    document.getElementById('citID').value = citizen.citID;
    document.getElementById('firstName').value = citizen.firstname;
    document.getElementById('firstName').readOnly = true;
    document.getElementById('middleName').value = citizen.middlename || '';
    document.getElementById('middleName').readOnly = true;
    document.getElementById('lastName').value = citizen.lastname;
    document.getElementById('lastName').readOnly = true;
    
    // Display birth date and age (read-only)
    if (citizen.birth_date) {
      document.getElementById('birthDate').value = citizen.birth_date;
      document.getElementById('birthDate').readOnly = true;
      const age = calculateAge(citizen.birth_date);
      document.getElementById('ageDisplay').value = age ? age + ' years old' : '';
    }

    const sexSelect = document.getElementById('sex');
    sexSelect.value = citizen.sex;
    sexSelect.style.color = sexSelect.value ? '#333' : '#707070ff';
    sexSelect.disabled = true;

    const illnessRecords = citizenIllnessRecords[citID] || [];
    const latestIllness = illnessRecords.length > 0 ? illnessRecords[0] : null;
    const commonIllnessSelect = document.getElementById('commonIllness');
    commonIllnessSelect.disabled = true;
    if (latestIllness) {
        document.getElementById('commonIllness').value = latestIllness.illness_id;
        document.getElementById('commonIllness').style.color = '#333';
    } else {
        document.getElementById('commonIllness').value = '';
        document.getElementById('commonIllness').style.color = '#707070ff';
    }

    const civilstatusSelect = document.getElementById('civilstatus');
    civilstatusSelect.value = citizen.civilstatus;
    civilstatusSelect.style.color = civilstatusSelect.value ? '#333' : '#707070ff';
    civilstatusSelect.disabled = true;

    document.getElementById('occupation').value = citizen.occupation;
    document.getElementById('occupation').readOnly = true;
    document.getElementById('contactnum').value = citizen.contactnum;
    document.getElementById('contactnum').readOnly = true;

    const purokSelect = document.getElementById('purok');
    purokSelect.value = citizen.purokID;
    purokSelect.style.color = purokSelect.value ? '#333' : '#707070ff';
    purokSelect.disabled = true;

    const medicalSection = document.querySelector('.medical-files-section');
    if (medicalSection) {
        medicalSection.style.display = 'none';
    }
    
    document.getElementById('submitButton').style.display = 'none';
    document.getElementById("records-section").style.display = "none";
    document.getElementById("top-controls").style.display = "none";
    document.getElementById("form-section").style.display = "block";

    showAssociatedRecords(citID);
  }

  function showAssociatedRecords(citID, isEditMode = false) {
    const filesContainer = document.getElementById('medicalFilesList');
    const previewBlock = document.getElementById('medicalFilesPreview');
    const header = document.getElementById('associatedRecordsHeader');

    if (!filesContainer || !previewBlock || !header) return;

    filesContainer.innerHTML = '';
    const files = citizenMedicalFiles[citID] || [];

    if (files.length > 0) {
      files.forEach(file => {
        const span = document.createElement('span');
        span.className = 'medical-file-item';

        if (file.mime.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = file.path; // uses display_file.php
          img.alt = file.filename;
          img.style.width = '80px';
          img.style.height = '80px';
          img.style.objectFit = 'cover';
          img.style.borderRadius = '8px';
          img.style.marginRight = '10px';
          img.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';
          span.appendChild(img);
        }

        const a = document.createElement('a');
        a.className = 'filename';
        a.href = file.view; // opens download_file.php with favicon
        a.target = '_blank';
        a.textContent = file.filename;
        span.appendChild(a);


        if (isEditMode) {
          const deleteButton = document.createElement('button');
          deleteButton.textContent = 'Delete';
          deleteButton.className = 'delete-file-button';
          deleteButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            deleteCitizenFile(file.id, citID);
          });
          span.appendChild(deleteButton);
        }

        filesContainer.appendChild(span);
        filesContainer.appendChild(document.createElement('br'));
      });

      header.style.display = 'block';
      previewBlock.style.display = 'block';
    } else {
      header.style.display = 'none';
      previewBlock.style.display = 'none';
    }
  }

  function showCitizenFiles(citID) {
    const filesContainer = document.getElementById('medicalFilesList');
    const previewBlock = document.getElementById('medicalFilesPreview');
    const header = document.getElementById('associatedRecordsHeader');

    if (!filesContainer || !previewBlock || !header) return;

    filesContainer.innerHTML = '';
    const files = citizenMedicalFiles[citID] || [];

    if (files.length > 0) {
      files.forEach(file => {
        const fileWrapper = document.createElement('div');
        fileWrapper.className = 'medical-file-item';
        const fileType = file.mime || '';

        if (fileType.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = file.path;
          img.alt = file.filename;
          img.className = 'file-thumbnail';
          fileWrapper.appendChild(img);

          const label = document.createElement('a');
          label.href = file.path;
          label.target = '_blank';
          label.textContent = file.filename;
          fileWrapper.appendChild(label);
        } else if (fileType === 'application/pdf') {
          const pdfContainer = document.createElement('div');
          pdfContainer.className = 'pdf-preview';

          const iframe = document.createElement('iframe');
          iframe.src = file.path;
          iframe.className = 'pdf-frame';
          iframe.title = file.filename;
          pdfContainer.appendChild(iframe);

          const label = document.createElement('a');
          label.href = file.path;
          label.target = '_blank';
          label.textContent = file.filename;
          pdfContainer.appendChild(label);

          fileWrapper.appendChild(pdfContainer);
        } else {
          const link = document.createElement('a');
          link.href = file.path;
          link.target = '_blank';
          link.textContent = file.filename;
          fileWrapper.appendChild(link);
        }

        filesContainer.appendChild(fileWrapper);
      });

      header.style.display = 'block';
      previewBlock.style.display = 'block';
    } else {
      header.style.display = 'none';
      previewBlock.style.display = 'none';
    }
  }

  function showIllnessHistory(citID) {
    const historyContainer = document.getElementById('illnessHistoryList');
    const previewBlock = document.getElementById('illnessHistoryPreview');
    
    if (!historyContainer || !previewBlock) return;
    
    historyContainer.innerHTML = '';
    const records = citizenIllnessRecords[citID] || [];
    
    if (records.length > 0) {
      records.forEach(record => {
        const div = document.createElement('div');
        div.style.padding = '10px';
        div.style.marginBottom = '5px';
        div.style.border = '1px solid #ddd';
        div.style.borderRadius = '5px';
        div.innerHTML = `
          <strong>${record.illness_name}</strong><br>
          Date: ${new Date(record.record_date).toLocaleDateString()}<br>
        `;
        historyContainer.appendChild(div);
      });
      previewBlock.style.display = 'block';
    } else {
      previewBlock.style.display = 'none';
    }
  }

  function showTable() {
    document.getElementById("records-section").style.display = "block";
    document.getElementById("form-section").style.display = "none";
    document.getElementById("top-controls").style.display = "flex";
  }

  function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
      const output = document.getElementById('profilePreview');
      output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
  }

  function deleteCitizenFile(fileID, citID) {
    if (!confirm("Are you sure you want to delete this file?")) return;

    fetch('model/record_file_func/delete_file.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: fileID })
    })
    .then(response => {
      return response.text().then(text => {
        try {
          return JSON.parse(text);
        } catch (e) {
          throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
        }
      });
    })
    .then(data => {
      if (data.status === 'success') {
        alert('File deleted successfully!');
        
        if (citizenMedicalFiles[citID]) {
          citizenMedicalFiles[citID] = citizenMedicalFiles[citID].filter(f => f.id != fileID);
        }
        
        showAssociatedRecords(citID, true);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => {
      alert('Failed to delete file: ' + err.message);
    });
  }

  function resetForm() {
    const form = document.querySelector('#form-section form');
    if (form) form.reset();
    
    const citIDField = document.getElementById('citID');
    const formActionField = document.getElementById('formAction');
    if (citIDField) citIDField.value = '';
    if (formActionField) formActionField.value = 'add_citizen';
    
    const profilePreview = document.getElementById('profilePreview');
    const uploadButton = document.getElementById('uploadProfileButton');
    if (profilePreview) profilePreview.src = 'resources/defaultprofile.png';
    if (uploadButton) uploadButton.style.display = 'block';
    
    const firstName = document.getElementById('firstName');
    const middleName = document.getElementById('middleName');
    const lastName = document.getElementById('lastName');
    const birthDate = document.getElementById('birthDate');
    const ageDisplay = document.getElementById('ageDisplay');
    const occupation = document.getElementById('occupation');
    const contactnum = document.getElementById('contactnum');
    const medicalCondition = document.getElementById('medicalCondition');
    const medicalNotes = document.getElementById('medicalNotes');
    
    if (firstName) firstName.readOnly = false;
    if (middleName) middleName.readOnly = false;
    if (lastName) lastName.readOnly = false;
    if (birthDate) birthDate.readOnly = false;
    if (ageDisplay) ageDisplay.value = '';
    if (occupation) occupation.readOnly = false;
    if (contactnum) contactnum.readOnly = false;
    if (medicalCondition) medicalCondition.readOnly = false;
    if (medicalNotes) medicalNotes.readOnly = false;
    
    const sexSelect = document.getElementById('sex');
    if (sexSelect) {
      sexSelect.disabled = false;
      sexSelect.value = '';
      sexSelect.style.color = '#707070ff';
    }
    
    const civilstatusSelect = document.getElementById('civilstatus');
    if (civilstatusSelect) {
      civilstatusSelect.disabled = false;
      civilstatusSelect.value = '';
      civilstatusSelect.style.color = '#707070ff';
    }
    
    const purokSelect = document.getElementById('purok');
    if (purokSelect) {
      purokSelect.disabled = false;
      purokSelect.value = '';
      purokSelect.style.color = '#707070ff';
    }

    const commonIllnessSelect = document.getElementById('commonIllness');
    if (commonIllnessSelect) {
      commonIllnessSelect.disabled = false;
      commonIllnessSelect.value = '';
      commonIllnessSelect.style.color = '#707070ff';
    }
    
    const medicalSection = document.querySelector('.medical-files-section');
    if (medicalSection) {
      medicalSection.style.display = 'block';
    }
    
    const previewBlock = document.getElementById('medicalFilesPreview');
    const header = document.getElementById('associatedRecordsHeader');
    const illnessHistoryPreview = document.getElementById('illnessHistoryPreview');
    if (previewBlock) previewBlock.style.display = 'none';
    if (header) header.style.display = 'none';
    if (illnessHistoryPreview) illnessHistoryPreview.style.display = 'none';
    
    const submitButton = document.getElementById('submitButton');
    const diagButton = document.getElementById('diagButton');
    if (submitButton) submitButton.style.display = 'block';
    if (diagButton) diagButton.style.display = 'inline-block';
    
    try {
      const dropifyElement = $('#medicalFiles').data('dropify');
      if (dropifyElement) {
        dropifyElement.resetPreview();
        dropifyElement.clearElement();
      }
    } catch (e) {
      console.log('Dropify reset failed:', e);
    }
  }

  function removeNumbersandSymbols(input) {
    input.value = input.value.replace(/[0-9!@#$%^&*(),.?":{}|<>]/g, '');
  }

  ['firstName', 'lastName', 'middleName', 'occupation', 'medicalCondition', 'medicalNotes'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
      element.addEventListener('input', function() {
        removeNumbersandSymbols(this);
      });
    }
  });

  function removeLetters(input) {
    input.value = input.value.replace(/[a-zA-Z]/g, '');
  }
  const contactnumElement = document.getElementById('contactnum');
  if (contactnumElement) {
    contactnumElement.addEventListener('input', function() {
      removeLetters(this);
    });
  }
  

  function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll("#citizenTableBody tr");

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchValue) ? "" : "none";
    });
  }

  const alertMessage = document.getElementById('alertMessage');
  if (alertMessage) {
    setTimeout(() => {
      alertMessage.style.opacity = '0';
      setTimeout(() => alertMessage.remove(), 500);
    }, 3000);
  }
</script>