<script>
  const citizensData = <?= json_encode($citizens); ?>;
  const citizenPhotos = {};

  <?php foreach ($citizens as $citizen): ?>
    citizenPhotos[<?= $citizen['citID'] ?>] = <?= json_encode(get_profile_image_path($citizen['firstname'], $citizen['lastname']) ?: 'resources/defaultprofile.png') ?>;
  <?php endforeach; ?>

  function showForm() {
    document.getElementById('formAction').value = 'add_citizen';
    document.getElementById('submitButton').textContent = 'Submit';
    document.getElementById('citID').value = '';
    document.querySelectorAll('#form-section input[type="text"], #form-section input[type="number"], #form-section input[type="tel"]').forEach(i => i.value = '');
    document.querySelectorAll('#form-section select').forEach(s => {
      s.selectedIndex = 0;
      s.style.color = '#999';
    });
    document.getElementById('profilePreview').src = 'resources/defaultprofile.png';
    document.getElementById('uploadInput').value = '';
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

  function showEditForm(citID) {
    const citizen = citizensData.find(c => c.citID == citID);
    if (!citizen) return alert('Citizen not found.');

    document.getElementById('citID').value = citizen.citID;
    document.getElementById('firstName').value = citizen.firstname;
    document.getElementById('middleName').value = citizen.middlename || '';
    document.getElementById('lastName').value = citizen.lastname;
    document.getElementById('age').value = citizen.age;

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

  const alertMessage = document.getElementById('alertMessage');
  if (alertMessage) {
    setTimeout(() => {
      alertMessage.style.opacity = '0';
      setTimeout(() => alertMessage.remove(), 500);
    }, 3000);
  }
</script>