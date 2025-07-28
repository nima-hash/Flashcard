<?php
require_once __DIR__ .  "/config/functions.php"; 

$name = $email = $username = $phone = $address = $birthday = $body = $pass = $verifyPass = '';
$nameErr = $usernameErr = $emailErr = $phoneErr = $addressErr = $birthdayErr = $bodyErr = $connection = $pssErr = $verifyPassErr = ''; 
$isGuest = $_SESSION['userName'] == 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Flashcards</title>
  <link href="sass/main.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="css/tingle.css" />
</head>
<body>

<!-- Navbar -->
<nav class="navbar header" id="header">
  <h6 class="app-title">
    <a class="link" href="#">Flashcards V1.0</a>
  </h6>

  <!-- Authenticated User Navigation -->
  <div id="navbarNav" class="navbarNav <?= $isGuest ? 'd-none' : 'd-block'; ?>">
    <ul>
      <!-- Category Dropdown -->
      <?= !$isGuest ? dropdownItem('category', [
        ['profile.php', 'Add category'],
        ['logout.php', 'Edit category']
      ]) : '' ?>

      <!-- Deck Dropdown -->
      <?= !$isGuest ? dropdownItem('note_stack', [
        ['#', 'Add Deck', 'add_Deck__Btn'],
        ['#', 'Edit Deck', 'edit_Deck__Btn']
      ], true) : '' ?>

      <!-- Learn Dropdown -->
      <?= !$isGuest ? dropdownItem('school', [
        ['profile.php', 'Learn a deck'],
        ['logout.php', 'Take a test']
      ]) : '' ?>

      <!-- Search Dropdown -->
      <?= !$isGuest ? searchDropdown() : '' ?>
    </ul>
  </div>

  <!-- Guest Login -->
  <div class="guest dropdown <?= $isGuest ? 'd-block' : 'd-none'; ?>">
    <button class="icon__btn dropdown-toggle" type="button">
      <span class="material-symbols-outlined">person</span>
    </button>
    <div class="dropdown_cont">
      <a class="link" href="login.php"><i>Login</i></a>
    </div>
  </div>

  <!-- Logged In User Menu -->
  <div class="loged_in dropdown <?= $isGuest ? 'd-none' : 'd-block'; ?>">
    <button class="icon__btn dropdown-toggle" type="button">
      <span class="material-symbols-outlined">person</span>
    </button>
    <div class="dropdown_cont" id="userDropdown">
      <ul>
        <li><a class="link" href="profile.php">Profile</a></li>
        <li><a class="link" href="logout.php" name="sign_out">Sign Out</a></li>
      </ul>
    </div>
  </div>
</nav>

<script>
  (function(){
    $('body').on('click', '.logoutBtn', function(){
      $.post('ajax.php', { action: 'logout' }, function(data){
        if(data == '1'){
          $('.loged_in').removeClass('d-block').addClass('d-none');
          $('.guest').removeClass('d-none').addClass('d-block');
          window.location.href = 'index.php';
        }
      });
    });
  })();
</script>

<?php
// --- Helper function for dropdowns ---
function dropdownItem($icon, $items, $isButton = false) {
  ob_start(); ?>
  <li>
    <div class="dropdown">
      <button class="icon__btn dropdown-toggle" type="button">
        <span class="material-symbols-outlined"><?= $icon ?></span>
      </button>
      <div class="dropdown_cont">
        <ul>
          <?php foreach ($items as $item): ?>
            <li>
              <?php if ($isButton): ?>
                <button class="menu__btn" id="<?= $item[2] ?? '' ?>"><?= $item[1] ?></button>
              <?php else: ?>
                <a class="link" href="<?= $item[0] ?>"><?= $item[1] ?></a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </li>
  <?php return ob_get_clean();
}

function searchDropdown() {
  return <<<HTML
  <li>
    <div class="dropdown">
      <button class="icon__btn dropdown-toggle" type="button">
        <span class="material-symbols-outlined">search</span>
      </button>
      <div class="dropdown_cont" id="searchDropdown">
        <ul>
          <li>
            <div class="search-Header">
              <input type="search" name="deckSearch" id="deckSearch" placeholder="Search deck">
            </div>
          </li>
          <li>
            <div>
              <input type="checkbox" name="searchCards" id="searchCardsChkBx">
              <label for="searchCardsChkBx">Search cards</label>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </li>
  HTML;
}
?>