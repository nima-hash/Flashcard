<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/config/functions.php"; 

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['userName'] === 'guest') {
    header("Location: login.php"); 
    exit();
}
$userName = $_SESSION['userName'] ?? 'User';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Profile - Flashcards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>

<!-- Navbar  -->
<nav class="navbar header navbar-expand-lg navbar-dark bg-dark-custom shadow-sm" id="mainNavbar">
  <div class="container-fluid">
    <h6 class="app-title mb-0">
      <a class="navbar-brand text-info fw-bold fs-4" href="index.php">Flashcards V1.0</a>
    </h6>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavContent" aria-controls="navbarNavContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavContent">


      <!-- Guest Login / Logged In User Menu -->
      <ul class="navbar-nav mb-2 mb-lg-0 ms-auto">
        <li class="nav-item dropdown" id="userAuthDropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined me-1">person</span> <span id="usernameDisplay"><?= htmlspecialchars($userName) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarUserDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><a class="dropdown-item logoutBtn" href="#">Sign Out</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="main-Cont container-fluid d-flex flex-column align-items-center py-5">
    <div class="profile-card card bg-dark text-white shadow-lg p-4 p-md-5 rounded-3">
        <h2 class="text-info text-center mb-4">User Profile</h2>

        <!-- Profile Information Display -->
        <div class="text-center mb-5">
            <img id="profilePictureDisplay" src="/media/profile_Img/account-icon-25499.png" alt="Profile Picture" class="rounded-circle mb-3 border border-primary" style="width: 150px; height: 150px; object-fit: cover;">
            <h3 id="profileUserName" class="text-white mb-1"></h3>
            <p id="profileEmail" class="text-white-50"></p>
        </div>

        <!-- Personal Information Section -->
        <div class="mb-5">
            <h4 class="text-light border-bottom pb-2 mb-3">Personal Information</h4>
            <form id="profileInfoForm">
                <div class="mb-3">
                    <label for="userName" class="form-label">Username</label>
                    <input type="text" class="form-control bg-secondary text-white border-secondary" id="userName" name="userName" readonly required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control bg-secondary text-white border-secondary" id="email" name="email" readonly required>
                </div>
                <!-- NEW FIELDS -->
                <div class="mb-3">
                    <label for="name" class="form-label">First Name</label>
                    <input type="text" class="form-control bg-secondary text-white border-secondary" id="name" name="name" readonly>
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control bg-secondary text-white border-secondary" id="lastName" name="lastName" readonly>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control bg-secondary text-white border-secondary" id="address" name="address" readonly>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" class="form-control bg-secondary text-white border-secondary" id="phone" name="phone" readonly>
                </div>
                <div class="mb-3">
                    <label for="birthday" class="form-label">Birthday</label>
                    <input type="date" class="form-control bg-secondary text-white border-secondary" id="birthday" name="birthday" readonly>
                </div>
                <!-- END NEW FIELDS -->

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-outline-info me-2" id="editProfileInfoBtn">
                        <span class="material-symbols-outlined">edit</span> Edit Profile
                    </button>
                    <button type="submit" class="btn btn-primary d-none" id="saveProfileInfoBtn">
                        <span class="material-symbols-outlined">save</span> Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary d-none" id="cancelProfileInfoBtn">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- Profile Picture Upload Section -->
        <div class="mb-5">
            <h4 class="text-light border-bottom pb-2 mb-3">Profile Picture</h4>
            <form id="profilePictureForm">
                <div class="mb-3">
                    <label for="profilePictureInput" class="form-label">Upload New Picture</label>
                    <input type="file" class="form-control bg-secondary text-white border-secondary" id="profilePictureInput" name="profilePicture" accept="image/*">
                    <small class="form-text text-white-50">Max file size 2MB. Formats: JPG, PNG, GIF.</small>
                </div>
                <button type="button" class="btn btn-primary" >Upload Picture</button>
            </form>
        </div>

        <!-- Password Reset Section -->
        <div>
            <h4 class="text-light border-bottom pb-2 mb-3">Password Reset</h4>
            <form id="passwordResetForm">
                <input type="hidden" id="passwordFormUsername" name="username" value="<?= htmlspecialchars($userEmail) ?>" autocomplete="username">
                
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control bg-secondary text-white border-secondary" id="currentPassword" name="currentPassword" required autocomplete="current-password">
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control bg-secondary text-white border-secondary" id="newPassword" name="newPassword" required autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control bg-secondary text-white border-secondary" id="confirmNewPassword" name="confirmNewPassword" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container (for system messages) -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- Define global JS variables from PHP -->
<script>
    window.appConfig = {
        isGuest: <?php echo json_encode(!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['userName'] === 'guest'); ?>,
        userName: <?php echo json_encode($userName); ?>
    };
</script>
<script type="module" src="utils.js"></script> 
<script type="module" src="profile.js"></script> 

</body>
</html>