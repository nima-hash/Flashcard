<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ .  "/config/functions.php"; 

$isGuest = !isset($_SESSION['userName']) || $_SESSION['userName'] == 'guest';
$userName = $_SESSION['userName'] ?? 'guest'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Flashcards</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link href="style.css" rel="stylesheet" />
</head>
<body>

<!-- Navbar -->
<nav class="navbar header navbar-expand-lg navbar-dark bg-dark-custom shadow-sm" id="mainNavbar">
  <div class="container-fluid">
    <h6 class="app-title mb-0">
      <a class="navbar-brand text-info fw-bold fs-4" href="#">Flashcards v1.1</a>
    </h6>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavContent" aria-controls="navbarNavContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <!-- Category Dropdown -->
        <li class="nav-item dropdown" id="categoryDropdownItem">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarCategoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined me-1">category</span> Categories
          </a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarCategoryDropdown">
            <li><a class="dropdown-item" href="#" id="addCategoryBtn">Add Category</a></li>
            <li><a class="dropdown-item" href="#" id="manageCategoriesBtn">Manage Categories</a></li>
          </ul>
        </li>

        <!-- Deck Dropdown -->
        <li class="nav-item dropdown" id="deckDropdownItem">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDeckDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined me-1">note_stack</span> Decks
          </a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDeckDropdown">
            <li><a class="dropdown-item" href="#" id="addDeckBtn">Add Deck</a></li>
          </ul>
        </li>

        <!-- Learn Dropdown -->
        <li class="nav-item dropdown" id="learnDropdownItem">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarLearnDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="material-symbols-outlined me-1">school</span> Learn
          </a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarLearnDropdown">
            <li><a class="dropdown-item" href="#" id="learnDeckBtn">Learn a Deck</a></li>
          </ul>
        </li>

        <!-- Search Input (integrated into navbar) -->
        <li class="nav-item d-flex align-items-center">
          <div class="input-group">
            <input type="search" class="form-control form-control-sm bg-light  border-secondary rounded-pill ps-3" placeholder="Search decks..." id="deckSearchInput">
            <button class="btn btn-outline-secondary rounded-pill ms-2" type="button" id="searchBtn">
              <span class="material-symbols-outlined">search</span>
            </button>
          </div>
        </li>
        <li class="nav-item d-flex align-items-center ms-2">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="searchCardsChkBx">
            <label class="form-check-label text-white-50 small" for="searchCardsChkBx">Search cards</label>
          </div>
        </li>
      </ul>

      <!-- Guest Login / Logged In User Menu -->
      <ul class="navbar-nav mb-2 mb-lg-0">
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

<div class="main-Cont container-fluid">
  <div class="row ">
    <!-- Main Left: Decks List -->
    <div class="main-Left col-lg-2 col-md-4 p-3 border-end border-secondary bg-secondary-custom rounded-start shadow-sm">
      <div class="main-Left_Header d-flex justify-content-between align-items-center mb-3">
        <p class="h5 text-info mb-0" id="decksListTitle"><?= htmlspecialchars($userName) !== 'guest' ? htmlspecialchars($userName) . "'s Decks" : "Guest Mode" ?></p>
        <button type="button" class="btn btn-sm btn-outline-info" id="addDeckBtnSmall">
          <span class="material-symbols-outlined">add</span>
        </button>
      </div>
      <div class="main-Left_Cards overflow-auto" id="decksNameList">
        <!-- Decks will be dynamically loaded here -->
        <p class="text-white-50 text-center mt-5" id="noDecksMessage">Loading decks...</p>
      </div>
    </div>
    
    <!-- Main Center: Cards Display / Study Mode -->
    <div class="main-Center col-lg-10 col-md-8 p-3 bg-dark-custom rounded-end shadow-sm d-flex flex-column" id="mainCenterDisplay">
      <div id="welcomeMessage" class="text-center text-white-50 mt-5">
        <h2 class="text-info">Welcome to Flashcards!</h2>
        <p class="lead">Select a deck from the left to view cards or start studying.</p>
        <p>Use the navigation bar to add new decks or categories.</p>
      </div>
      <!-- Cards or Study UI will be dynamically loaded here -->
    </div>
  </div>
</div>

<!-- Modals -->

<!-- Add/Edit Deck Modal -->
<div class="modal fade" id="deckModal" tabindex="-1" aria-labelledby="deckModalLabel" >
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white card-glass">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-info" id="deckModalLabel">Add New Deck</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="deckForm">
          <input type="hidden" id="deckId" name="deckId">
          <div class="mb-3">
            <label for="deckName" class="form-label">Deck Name</label>
            <input type="text" class="form-control bg-secondary text-white border-secondary" id="deckName" name="deckName" required>
          </div>
          <div class="mb-3">
            <label for="deckCategory" class="form-label">Category</label>
            <select class="form-select bg-secondary text-white border-secondary" id="deckCategory" name="deckCategory" required>
              <!-- Categories will be loaded here dynamically -->
            </select>
          </div>
          <div class="mb-3">
            <label for="deckDescription" class="form-label">Description</label>
            <input type="text" class="form-control bg-secondary text-white border-secondary" id="deckDescription" name="deckDescription">
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" id="saveDeckBtn">Save Deck</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Card Modal -->
<div class="modal fade" id="cardModal" tabindex="-1" aria-labelledby="cardModalLabel">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white card-glass">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-info" id="cardModalLabel">Add New Card</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="cardForm">
          <input type="hidden" id="cardId" name="cardId">
          <input type="hidden" id="cardDeckId" name="cardDeckId">
          <div class="mb-3">
            <label for="cardFront" class="form-label">Front (Question)</label>
            <textarea class="form-control bg-secondary text-white border-secondary" id="cardFront" name="cardFront" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="cardBack" class="form-label">Back (Answer)</label>
            <textarea class="form-control bg-secondary text-white border-secondary" id="cardBack" name="cardBack" rows="3" required></textarea>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary" id="saveCardBtn">Save Card</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Category Management Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white card-glass">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title text-info" id="categoryModalLabel">Manage Categories</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addCategoryForm" class="mb-4">
          <div class="input-group">
            <input type="text" class="form-control bg-secondary text-white border-secondary" placeholder="New Category Name" id="newCategoryName" required>
            <button class="btn btn-outline-primary" type="submit">Add</button>
          </div>
        </form>
        <ul class="list-group" id="categoryList">
          <!-- Categories will be loaded here -->
          <li class="list-group-item bg-secondary text-white-50 border-secondary">No categories found.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container-->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Define global JS variables from PHP before script.js -->
<script>
    window.appConfig = {
        isGuest: <?php echo json_encode($isGuest); ?>,
        userName: <?php echo json_encode($userName); ?>
    };
</script>
<script type="module" src="utils.js"></script>
<script type="module" src="script.js"></script>


</body>
</html>