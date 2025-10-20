<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | Login</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/loginstyle.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

  <div class="main-container">
    <div class="left-panel">
      <h1 class="title">DOCUCARE</h1>
      <img src="resources/images/Logo.svg" alt="DocuCare Logo" class="logo-inside">
    </div>

    <div class="login-box">
      <h2>Welcome to DocuCare</h2>
      <p class="subtitle">Sign in to continue</p>

      <?php if ($error): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="index.php">
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="forgot-password">
          <a href="forgot_password.php">Forgot password?<br><br></a>
        </div>

        <button type="submit" class="login-btn">Login</button>

        <p class="create-account">
          Don't have an account? <a href="index.php?page=signup">Create Account</a>
        </p>
      </form>
    </div>
  </div>

</body>
</html>