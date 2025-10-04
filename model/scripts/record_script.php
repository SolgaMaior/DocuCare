<script>
  (function() {
    // Clear browser history and replace current state
    if (window.history && window.history.replaceState) {
      // Replace current state with a new one
      window.history.replaceState(null, null, window.location.href);

      // Add a new state to the history
      window.history.pushState(null, null, window.location.href);

      // Listen for back button attempts
      window.addEventListener('popstate', function(event) {
        // Push the current state again to prevent going back
        window.history.pushState(null, null, window.location.href);
      });
    }
  })();




  const citizensData = <?= json_encode($citizens); ?>;
  const citizenPhotos = {};
  const citizenMedicalFiles = {};

  <?php foreach ($citizens as $citizen): ?>
    citizenPhotos[<?= $citizen['citID'] ?>] = <?= json_encode(get_profile_image_path($citizen['firstname'], $citizen['lastname']) ?: 'resources/defaultprofile.png') ?>;
    citizenMedicalFiles[<?= $citizen['citID'] ?>] = <?= json_encode(get_medical_files_path($citizen['firstname'], $citizen['lastname'])) ?>;
  <?php endforeach; ?>


  function showForm() {
    document.getElementById('formAction').value = 'add_citizen';
    document.getElementById('submitButton').textContent = 'Submit';
    document.getElementById('citID').value = '';
    document.querySelectorAll('#form-section input[type="text"], #form-section input[type="number"], #form-section input[type="tel"]').forEach(i => i.value = '');
    document.querySelectorAll('#form-section select').forEach(s => {
      s.selectedIndex = 0;
      s.style.color = '#707070ff';
    });
    document.getElementById('profilePreview').src = 'resources/defaultprofile.png';
    document.getElementById('uploadInput').value = '';
    
    // Clear medical files section
    document.getElementById('medicalCondition').value = '';
    document.getElementById('medicalNotes').value = '';
    document.getElementById('medicalFiles').value = '';
    
    document.getElementById("records-section").style.display = "none";
    document.getElementById("form-section").style.display = "block";
    document.getElementById("top-controls").style.display = "none";
    
    // Initialize Dropify after form is shown
    setTimeout(() => {
      initializeDropify();
      const medicalSection = document.querySelector('.medical-files-section');
      if (medicalSection) medicalSection.style.display = 'block';
      document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);

  }



  function showTable() {
    document.getElementById("form-section").style.display = "none";
    document.getElementById("records-section").style.display = "block";
    document.getElementById("top-controls").style.display = "flex";
    setTimeout(() => {
        initializeDropify();
        const medicalSection = document.querySelector('.medical-files-section');
        if (medicalSection) medicalSection.style.display = 'block';
        document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);
  }
  


  function getFile(event) {
    const file = event.target.files[0];
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword'];

    if (!validTypes.includes(file.type)) {
      alert('Please select a valid file (JPG, PNG, GIF, WebP, PDF, or DOC)');
      event.target.value = '';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      alert('File is too large. Maximum size is 5MB');
      event.target.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      document.getElementById('filePreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
  }





  function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
      const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
      if (!validTypes.includes(file.type)) {
        alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
        event.target.value = '';
        return;
      }

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


  // Initialize Dropify when form is shown
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




  function showEditForm(citID) {
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');

    document.getElementById('profilePreview').src = citizenPhotos[citID] || 'resources/defaultprofile.png';

    document.getElementById('citID').value = citizen.citID;
    document.getElementById('firstName').value = citizen.firstname;
    document.getElementById('middleName').value = citizen.middlename || '';
    document.getElementById('lastName').value = citizen.lastname;
    document.getElementById('age').value = citizen.age;

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

    const medicalSection = document.querySelector('.viewmedicalfiles');
    if (medicalSection) {
        medicalSection.style.display = 'none';
    }

    // Clear medical files section for editing
    document.getElementById('medicalCondition').value = '';
    document.getElementById('medicalNotes').value = '';
    document.getElementById('medicalFiles').value = '';

    document.getElementById('formAction').value = 'add_citizen';
    document.getElementById('submitButton').textContent = 'Update';
    document.getElementById("records-section").style.display = "none";
    document.getElementById("form-section").style.display = "block";
    document.getElementById('associatedRecordsHeader').style.display = 'none';
    document.getElementById('medicalFilesPreview').style.display = 'none';
    document.getElementById("top-controls").style.display = "none";
  
    


    setTimeout(() => {
        initializeDropify();
        const medicalSection = document.querySelector('.medical-files-section');
        if (medicalSection) medicalSection.style.display = 'block';
        document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);


  }

  function showViewForm(citID) {
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');

    document.getElementById('profilePreview').src = citizenPhotos[citID] || 'resources/defaultprofile.png';
    document.getElementById('uploadProfileButton').style.display = 'none';

    document.getElementById('citID').value = citizen.citID;
    document.getElementById('firstName').value = citizen.firstname;
    document.getElementById('firstName').readOnly = true;
    document.getElementById('middleName').value = citizen.middlename || '';
    document.getElementById('middleName').readOnly = true;
    document.getElementById('lastName').value = citizen.lastname;
    document.getElementById('lastName').readOnly = true;
    document.getElementById('age').value = citizen.age;
    document.getElementById('age').readOnly = true;

    const sexSelect = document.getElementById('sex');
    sexSelect.value = citizen.sex;
    sexSelect.style.color = sexSelect.value ? '#333' : '#707070ff';
    sexSelect.disabled = true;

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



    // Hide medical files section using class selector
    const medicalSection = document.querySelector('.medical-files-section');
    if (medicalSection) {
        medicalSection.style.display = 'none';
    }
    
    // Hide submit button in view mode
    document.getElementById('submitButton').style.display = 'none';
    document.getElementById("records-section").style.display = "none";
    document.getElementById("top-controls").style.display = "none";
    document.getElementById("form-section").style.display = "block";

    // Render associated medical files for this citizen
    const filesContainer = document.getElementById('medicalFilesList');
    const previewBlock = document.getElementById('medicalFilesPreview');
    const header = document.getElementById('associatedRecordsHeader');
    if (filesContainer && previewBlock && header) {
      filesContainer.innerHTML = '';
      const files = citizenMedicalFiles[citID] || [];
      if (files.length > 0) {
        files.forEach(file => {
          const span = document.createElement('span');
          span.className = 'medical-file-item';
          const a = document.createElement('a');
          a.href = file.path;
          a.target = '_blank';
          a.textContent = file.filename;
          span.appendChild(a);
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