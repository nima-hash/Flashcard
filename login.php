<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
  require_once __DIR__ .  "/config/functions.php"; 
  
  // Determine if user is a guest
  $isGuest = !isset($_SESSION['userName']) || $_SESSION['userName'] == 'guest';
  $userName = $_SESSION['userName'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="style.css" rel="stylesheet" />
    <link href="auth.css" rel="stylesheet" />
    <title>Login - Flashcards</title>
  </head>
  <body>
    <!-- Navbar Section -->
    <nav class="navbar header navbar-expand-lg navbar-dark bg-dark-custom shadow-sm" id="mainNavbar">
      <div class="container-fluid">
        <h6 class="app-title mb-0">
          <a class="navbar-brand text-info fw-bold fs-4" href="index.php">Flashcards V1.0</a>
        </h6>

        <div class="collapse navbar-collapse" id="navbarNavContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <!-- These items are typically hidden for auth pages, but included for consistency -->
            <li class="nav-item dropdown d-none" id="categoryDropdownItem"></li>
            <li class="nav-item dropdown d-none" id="deckDropdownItem"></li>
            <li class="nav-item dropdown d-none" id="learnDropdownItem"></li>
            <li class="nav-item d-flex align-items-center d-none"></li>
            <li class="nav-item d-flex align-items-center ms-2 d-none"></li>
          </ul>

          <ul class="navbar-nav mb-2 mb-lg-0">
            <li class="nav-item dropdown" id="userAuthDropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-symbols-outlined me-1">person</span> <span id="usernameDisplay"><?= htmlspecialchars($userName) ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarUserDropdown">
                <?php if ($isGuest): ?>
                  <li><a class="dropdown-item" href="login.php">Login</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                  <li><a class="dropdown-item logoutBtn" href="#">Sign Out</a></li>
                <?php endif; ?>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Login Section -->
    <div class="auth-container d-flex flex-column align-items-center justify-content-center py-5">
      <div class="auth-card card bg-dark text-white shadow-lg p-4 p-md-5 rounded-3">
        <div class="auth-header text-center mb-4">
          <h4 class="text-info">Login to your account</h4>
        </div>

        <div class="alert alert-info text-center" role="alert" id="login_notice">
          <strong>Notice:</strong> You must log in to proceed. Please log in here:
        </div>

        <form class="auth-form" id="login__form" method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label" for="userName">Username or Email</label>
            <input class="form-control bg-secondary text-white border-secondary" required type="text" autocomplete="username" name="userName" id="userName" placeholder="Username or Email">
            <div class="invalid-feedback" id="userNameError"></div>
          </div>
          
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control bg-secondary text-white border-secondary" required type="password" name="password" autocomplete="current-password" id="password" placeholder="Password">
            <div class="invalid-feedback" id="passwordError"></div>
          </div>
          
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="togglePassword">
            <label class="form-check-label text-white-50" for="togglePassword">Show Password</label>
          </div>
          
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg" id="submitLogin">Login</button>
            <button type="reset" class="btn btn-outline-secondary btn-lg">Cancel</button>
          </div>
        </form>
        
        <p class="text-center mt-4 text-white-50">
          Have you forgotten your password? <a href="#" class="text-info" data-bs-toggle="modal" data-bs-target="#passwordResetModal">Reset now!</a>
        </p>
        <p class="text-center text-white-50">
          Don't have an account? <a href="signup.php" class="text-info">Sign up now!</a>
        </p>
      </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    </div>

    <!-- Password Reset Modal -->
    <div class="modal fade" id="passwordResetModal" tabindex="-1" aria-labelledby="passwordResetModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border border-secondary">
          <div class="modal-header border-bottom border-secondary">
            <h5 class="modal-title text-info" id="passwordResetModalLabel">Reset Your Password</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="passwordResetRequestForm">
            <div class="modal-body">
              <p class="text-white-50">Enter your username or email address below. If an account exists, we'll send you a password reset link.</p>
              <div class="mb-3">
                <label for="resetIdentifier" class="form-label">Username or Email</label>
                <input type="text" class="form-control bg-secondary text-white border-secondary" id="resetIdentifier" name="identifier" required autocomplete="username" placeholder="Your Username or Email">
                <div class="invalid-feedback" id="resetIdentifierError"></div>
              </div>
            </div>
            <div class="modal-footer border-top border-secondary">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- Define global JS variables from PHP -->
    <script>
        window.appConfig = {
            isGuest: <?php echo json_encode($isGuest); ?>,
            userName: <?php echo json_encode($userName); ?>
        };
    </script>
    <script type="module" src="utils.js"></script>
    <script type="module" src="login.js"></script>
  
  </body>
</html>