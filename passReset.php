<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get token and email from URL query parameters
$token = htmlspecialchars($_GET['token'] ?? '');
$email = htmlspecialchars($_GET['email'] ?? '');

// Initial state for JS
$isValidToken = 'false';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Flashcards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link href="style.css" rel="stylesheet" />
  <link href="auth.css" rel="stylesheet" />
</head>
<body>

<!-- Navbar-->
<nav class="navbar header navbar-expand-lg navbar-dark bg-dark-custom shadow-sm">
  <div class="container-fluid">
    <h6 class="app-title mb-0">
      <a class="navbar-brand text-info fw-bold fs-4" href="index.php">Flashcards V1.0</a>
    </h6>
    <div class="auth-actions">
        <a class="link text-white-50" href="login.php">Login</a>
        <span class="divider text-white-50">|</span>
        <a class="link text-white-50" href="signup.php">Sign Up</a>
    </div>
  </div>
</nav>

<div class="auth-container d-flex flex-column align-items-center justify-content-center py-5">
  <div class="auth-card card bg-dark text-white shadow-lg p-4 p-md-5 rounded-3">
    <div class="auth-header text-center mb-4">
      <h4 class="text-info" id="resetHeader">Verifying Token...</h4>
    </div>

    <!-- Message Area -->
    <div id="messageArea" class="alert alert-info text-center" role="alert">
      Please wait while we verify your reset link...
    </div>

    <!-- Password Reset Form -->
    <form class="auth-form d-none" id="passwordResetForm" novalidate>
      <input type="hidden" id="resetToken" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="hidden" id="resetEmail" name="email" value="<?= htmlspecialchars($email) ?>">
      
      <div class="mb-3">
        <label class="form-label" for="newPassword">New Password</label>
        <input class="form-control bg-secondary text-white border-secondary" required type="password" name="newPassword" id="newPassword" placeholder="Enter new password">
        <div class="invalid-feedback" id="newPasswordError"></div>
      </div>
      
      <div class="mb-3">
        <label class="form-label" for="confirmNewPassword">Confirm New Password</label>
        <input class="form-control bg-secondary text-white border-secondary" required type="password" name="confirmNewPassword" id="confirmNewPassword" placeholder="Confirm new password">
        <div class="invalid-feedback" id="confirmNewPasswordError"></div>
      </div>
      
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="togglePassword">
        <label class="form-check-label text-white-50" for="togglePassword">Show Passwords</label>
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">Reset Password</button>
      </div>
    </form>
    
    <p class="text-center mt-4 text-white-50">
      Remembered your password? <a href="login.php" class="text-info">Log in here!</a>
    </p>
  </div>
</div>

<!-- Toast Container (for system messages) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Define global JS variables from PHP -->
<script>
    window.resetConfig = {
        token: <?php echo json_encode($token); ?>,
        email: <?php echo json_encode($email); ?>
    };
</script>
<script type="module" src="utils.js"></script>
<script type="module" src="passReset.js"></script>

</body>
</html>