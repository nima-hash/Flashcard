<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config/functions.php";

// Initialize variables for error messages
$userErr = $verPasErr = $emailErr = $phoneErr = $addressErr = $birthdayErr = $passErr = ''; 

// Determine if user is a guest 
$isGuest = !isset($_SESSION['userName']) || $_SESSION['userName'] == 'guest';
$userName = $_SESSION['userName'] ?? 'guest'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign Up - Flashcards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link href="style.css" rel="stylesheet" />
  <link href="auth.css" rel="stylesheet" />
</head>
<body>

  <!-- Navbar Section  -->
  <nav class="navbar header navbar-expand-lg navbar-dark bg-dark-custom shadow-sm" id="mainNavbar">
    <div class="container-fluid">
      <h6 class="app-title mb-0">
        <a class="navbar-brand text-info fw-bold fs-4" href="index.php">Flashcards V1.0</a>
      </h6>

      <div class="collapse navbar-collapse" id="navbarNavContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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

 <!-- Sign up Form -->
  <div class="auth-container d-flex flex-column align-items-center justify-content-center py-5">
    <div class="auth-card card bg-dark text-white shadow-lg p-4 p-md-5 rounded-3">
      <div class="auth-header text-center mb-4">
        <h4 class="text-info">Create your account</h4>
      </div>

      <form class="auth-form" id="signup__form" method="post" novalidate>

        <div class="mb-3">
          <label class="form-label" for="userName">Username</label>
          <input class="form-control bg-secondary text-white border-secondary" required type="text" name="userName" id="userName" autocomplete="username" placeholder="Choose a username">
          <div class="invalid-feedback"><?php echo $userErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input class="form-control bg-secondary text-white border-secondary" required type="email" name="email" id="email" autocomplete="email" placeholder="you@example.com">
          <div class="invalid-feedback"><?php echo $emailErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <input class="form-control bg-secondary text-white border-secondary" required type="password" name="password" id="password" autocomplete="new-password" placeholder="Create a password">
          <div class="invalid-feedback"><?php echo $passErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="confirmPassword">Confirm Password</label>
          <input class="form-control bg-secondary text-white border-secondary" required type="password" name="confirmPassword" id="confirmPassword" autocomplete="new-password" placeholder="Confirm your password">
          <div class="invalid-feedback"><?php echo $verPasErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="name">First Name (optional)</label>
          <input class="form-control bg-secondary text-white border-secondary" type="text" name="name" id="name" autocomplete="given-name" placeholder="Your first name">
        </div>

        <div class="mb-3">
          <label class="form-label" for="lastName">Last Name (optional)</label>
          <input class="form-control bg-secondary text-white border-secondary" type="text" name="lastName" id="lastName" autocomplete="family-name" placeholder="Your last name">
        </div>

        <div class="mb-3">
          <label class="form-label" for="phone">Phone (optional)</label>
          <input class="form-control bg-secondary text-white border-secondary" type="tel" name="phone" id="phone" autocomplete="tel" placeholder="e.g. +49 123 456789">
          <div class="invalid-feedback"><?php echo $phoneErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="address">Address (optional)</label>
          <input class="form-control bg-secondary text-white border-secondary" type="text" name="address" id="address" autocomplete="street-address" placeholder="Your address">
          <div class="invalid-feedback"><?php echo $addressErr; ?></div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="birthday">Birthday (optional)</label>
          <input class="form-control bg-secondary text-white border-secondary" type="date" name="birthday" id="birthday" autocomplete="bday">
          <div class="invalid-feedback"><?php echo $birthdayErr; ?></div>
        </div>

        <!-- Show password toggle -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="togglePassword">
          <label class="form-check-label text-white-50" for="togglePassword">Show Passwords</label>
        </div>

        <div class="d-grid gap-2">
          <button id="submit_Btn" type="submit" class="btn btn-primary btn-lg">Register</button>
          <button type="reset" class="btn btn-outline-secondary btn-lg">Cancel</button>
        </div>
      </form>

      <p class="text-center mt-4 text-white-50">Already have an account? <a href="login.php" class="text-info">Log in here</a>.</p>
    </div>
  </div>

<!-- Toast Container (for system messages) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
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
<script type="module" src="signup.js"></script>

</body>
</html>