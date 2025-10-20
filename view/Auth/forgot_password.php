<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | Forgot Password</title>
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
      <h2>Forgot Password</h2>
      <p class="subtitle">Enter your email to reset your password</p>

      <?php if ($error): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="forgot_password.php">
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <button type="submit" class="login-btn">Reset Password</button>

        <p class="switch-text">
          Remember your password? <a href="login.php">Login</a>
        </p>
      </form>
    </div>
  </div>

</body>
</html>