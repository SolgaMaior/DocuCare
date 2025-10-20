<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | Reset Password</title>
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
      <h2>Reset Password</h2>
      <p class="subtitle">Enter the code sent to your email</p>
      
      <!-- FOR TESTING ONLY - Remove in production -->
      <div style="background-color: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <strong>Testing Code:</strong> <?= htmlspecialchars($displayToken) ?>
      </div>

      <?php if ($error): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="reset_password.php">
        <div class="input-group">
          <label for="code">Verification Code</label>
          <input type="text" id="code" name="code" placeholder="Enter the code" required>
        </div>

        <button type="submit" class="login-btn">Confirm Code</button>
      </form>

      <p class="create-account">
        Remembered your password? <a href="login.php">Login</a>
      </p>
    </div>
  </div>

</body>
</html>