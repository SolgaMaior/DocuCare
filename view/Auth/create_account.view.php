<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | Create Account</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/create_account.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  
  <div class="main-container">
    <div class="left-panel">
      <h1 class="title">DOCUCARE</h1>
      <img src="resources/images/Logo.svg" alt="DocuCare Logo" class="logo-inside">
    </div>

    <div class="create-box">
      <h2>Create Account</h2>
      <p class="subtitle">Fill in your details to get started</p>

      <?php if ($error): ?>
        <div class="alert error" style="background-color: #f8d7da; color: #91212c; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert success" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>


      <form method="POST" action="index.php?page=signup">
        <div class="name-group">
          <div class="input-group half">
            <label for="firstname">First Name</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter first name" required>
          </div>

          <div class="input-group half">
            <label for="lastname">Last Name</label>
            <input type="text" id="lastname" name="lastname" placeholder="Enter last name" required>
          </div>
        </div>

        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password (min. 8 characters)" required>
        </div>

        <div class="input-group">
          <label for="confirm">Confirm Password</label>
          <input type="password" id="confirm" name="confirm" placeholder="Confirm your password" required>
        </div>

        <button type="submit" class="create-btn">Create Account</button>

        <p class="login-text">
          Already have an account? <a href="index.php?page=login">Login</a>
        </p>
      </form>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
      event.preventDefault(); // prevent auto-submit

      const password = document.getElementById('password').value.trim();
      const confirmField = document.getElementById('confirm').value.trim();

      // Check password match
      if (password !== confirmField) {
        alert('Passwords do not match. Please try again.');
        return;
      }

      // Ask for confirmation using window.confirm (ensures global scope)
      if (window.confirm('Are you sure you want to create this account?')) {
        form.submit(); // proceed
      }
    });
  });
  </script>




</body>
</html>