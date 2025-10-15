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
    citizenPhotos[<?= $citizen['citID'] ?>] = <?= json_encode("model/display_image.php?citID=" . $citizen['citID']) ?>;
    citizenMedicalFiles[<?= $citizen['citID'] ?>] = <?= json_encode(get_citizen_file_data($citizen['citID'])) ?>;
  <?php endforeach; ?>














  function showForm() {
    resetForm();
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

    // Reset the form
    const form = document.querySelector('#form-section form');
    form.reset();
    document.getElementById('citID').value = '';
    document.getElementById('formAction').value = 'add_citizen';
    
    // Initialize Dropify after form is shown
    setTimeout(() => {
      initializeDropify();
      const medicalSection = document.querySelector('.medical-files-section');
      if (medicalSection) medicalSection.style.display = 'block';
      document.getElementById('submitButton').style.display = 'inline-block';
    }, 100);

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
    resetForm();
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');
    
    // Clear medical files section for editing
    document.getElementById('medicalCondition').value = '';
    document.getElementById('medicalNotes').value = '';
    document.getElementById('medicalFiles').value = '';
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
    resetForm();
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

          // Create link
          const a = document.createElement('a');
          a.href = file.path;
          a.target = '_blank';
          a.textContent = file.filename;

          // If the file is an image, add a thumbnail preview
          if (file.mime.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = file.path;
            img.alt = file.filename;
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.marginRight = '10px';
            img.style.boxShadow = '0 0 5px rgba(0,0,0,0.3)';
            span.appendChild(img);
          }

          // Add the link next to the thumbnail
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
          // --- Image Preview ---
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
          // --- PDF Preview ---
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
          // --- Other Files (DOCX, TXT, etc.) ---
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








  function resetForm() {
      // Reset form values
      const form = document.querySelector('#form-section form');
      if (form) form.reset();
      
      // Clear hidden fields
      const citIDField = document.getElementById('citID');
      const formActionField = document.getElementById('formAction');
      if (citIDField) citIDField.value = '';
      if (formActionField) formActionField.value = 'add_citizen';
      
      // Reset profile image
      const profilePreview = document.getElementById('profilePreview');
      const uploadButton = document.getElementById('uploadProfileButton');
      if (profilePreview) profilePreview.src = 'resources/defaultprofile.png';
      if (uploadButton) uploadButton.style.display = 'block';
      
      // Enable all fields
      const firstName = document.getElementById('firstName');
      const middleName = document.getElementById('middleName');
      const lastName = document.getElementById('lastName');
      const age = document.getElementById('age');
      const occupation = document.getElementById('occupation');
      const contactnum = document.getElementById('contactnum');
      
      if (firstName) firstName.readOnly = false;
      if (middleName) middleName.readOnly = false;
      if (lastName) lastName.readOnly = false;
      if (age) age.readOnly = false;
      if (occupation) occupation.readOnly = false;
      if (contactnum) contactnum.readOnly = false;
      
      // Enable and reset select fields
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
      
      // Show medical files section
      const medicalSection = document.querySelector('.medical-files-section');
      if (medicalSection) {
          medicalSection.style.display = 'block';
      }
      
      // Hide medical files preview
      const previewBlock = document.getElementById('medicalFilesPreview');
      const header = document.getElementById('associatedRecordsHeader');
      if (previewBlock) previewBlock.style.display = 'none';
      if (header) header.style.display = 'none';
      
      // Show submit button
      const submitButton = document.getElementById('submitButton');
      if (submitButton) submitButton.style.display = 'block';
      
      // Reset dropify if you're using it
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








































    // Remove numbers as user types
  function removeNumbers(input) {
      input.value = input.value.replace(/[0-9]/g, '');
  }

  // Apply to fields
  ['firstName', 'lastName', 'middleName', 'occupation'].forEach(id => {
      const element = document.getElementById(id);
      element.addEventListener('input', function() {
          removeNumbers(this);
      });
  });

  // Remove letters as user types
  function removeLetters(input) {
      input.value = input.value.replace(/[a-zA-Z]/g, '');
  }

  // Apply to fields
  ['age'].forEach(id => {
      const element = document.getElementById(id);
      element.addEventListener('input', function() {
          removeLetters(this);
      });
  });


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