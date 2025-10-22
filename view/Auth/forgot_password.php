<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DocuCare | Reset Password</title>
  <link rel="icon" type="image/svg" href="resources/images/Logo.svg">
  <link rel="stylesheet" href="styles/forgot_pass.css">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+SC:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="main-container">
    <div class="left-panel">
      <h1 class="title">DOCUCARE</h1>
      <img src="resources/images/Logo.svg" alt="DocuCare Logo" class="logo-inside">
    </div>
    <div class="login-box">
      
      <?php if ($step === 'email'): ?>
        <!-- STEP 1: Enter Email -->
        <h2>Forgot Password</h2>
        <p class="subtitle">Enter your email to reset your password</p>
        <?php if ($error): ?>
          <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=forgot_password&step=email">
          <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>
          <button type="submit" class="login-btn">Send Reset Code</button>
          <p class="switch-text">
            Remember your password? <a href="index.php?page=login">Login</a>
          </p>
        </form>
      
      <?php elseif ($step === 'verify'): ?>
        <!-- STEP 2: Enter Code -->
        <h2>Verify Code</h2>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
       
        <?php if ($error): ?>
          <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=forgot_password&step=verify">
          <div class="input-group">
            <label for="code">Verification Code</label>
            <input type="text" id="code" name="code" placeholder="Enter 6-digit code" required maxlength="6" pattern="[0-9]{6}">
          </div>
          <button type="submit" class="login-btn">Verify Code</button>
          <p class="switch-text">
            <a href="index.php?page=forgot_password">Request new code</a> | 
            <a href="index.php?page=login">Back to Login</a>
          </p>
        </form>
      
      <?php elseif ($step === 'reset'): ?>
        <!-- STEP 3: Set New Password -->
        <h2>Create New Password</h2>
        <p class="subtitle">Enter your new password below</p>
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
        <form method="POST" action="index.php?page=forgot_password&step=reset">
          <div class="input-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Minimum 8 characters" required minlength="8">
          </div>
          <div class="input-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
          </div>
          <button type="submit" class="login-btn">Reset Password</button>
        </form>
      
      <?php endif; ?>
      
    </div>
  </div>
</body>
</html>