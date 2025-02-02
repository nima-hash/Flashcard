<?php
include "header.php";
// print_r($_SESSION);
?>
<div class="main-Cont">
  <div class="main-Left">
    <div class="main-Left_Header">
      <!-- <img src="" alt="">  -->
      <p><?php echo ($_SESSION['userName'] !== 'guest' ) ? ($_SESSION['userName'])."'s Decks" : "guest" ?> </p>
      <!-- <div class="search-Header">
        <input type="search" name="deckSearch" id="deckSearch"> 
        <button type="button" class="icon__btn" id="add_Deck__Btn">
          <span class="material-symbols-outlined">
            add
          </span>
        </button>
      </div>
      <div>
        <input type="checkbox" name="searchCards" id="searchCardsChkBx">
        <label for="searchCardsChkBx">Search cards for...</label>
      </div> -->
    </div>
    <div class="main-Left_Cards" id="decksNameList">
      
    </div>
    

  </div>
  
  <div class="main-Center" id = "mainCenterDisplay">

  </div>

  <!-- <div class="main-Right">
  </div> -->
  
  <!-- <div class="main-Right"></div> -->

</div>
<!-- <input type="text"> -->
<script>
  const getDecksArr = ()=> 
  {
    return <?php echo json_encode($_SESSION['decks']);?>;
  }
</script>

<?php
include "footer.php"
?>