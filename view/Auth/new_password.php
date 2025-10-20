<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | New Password</title>
  <link rel="icon" type="image/svg" href="../../resources/images/Logo.svg">
  <link rel="stylesheet" href="../../styles/forgot_pass.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="main-container">
    <div class="left-panel">
      <h1 class="title">DOCUCARE</h1>
      <img src="resources/images/Logo.svg" alt="DocuCare Logo" class="logo-inside">
    </div>

    <div class="login-box">
      <h2>Create New Password</h2>
      <p class="subtitle">Create your new password below</p>

      <?php if ($error): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="new_password.php">
        <div class="input-group">
          <label for="new_password">New Password</label>
          <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 8 characters)" required>
        </div>

        <div class="input-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
        </div>

        <button type="submit" class="login-btn">Confirm</button>
      </form>

      <p class="create-account">
        Remembered your password? <a href="login.php">Login</a>
      </p>
    </div>
  </div>

</body>
</html>