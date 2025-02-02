<?php
// session_start();
// include "config/database.php";
include "config/functions.php"; 
// print_r($_SESSION);
// checks for loged in user
checkLogin();
// include "config/database.php";
// //  $defaultSetting=array("gray-100","6","black-0","New User");
// if (!$_SESSION || $_SESSION['user']=='guest'){
//   $_SESSION['user']='guest'; 
//   $_SESSION['user_access']=5;
//   $_SESSION['userName']='guest';
//   header("Location: login.php");
//   die();
// }
// else{

//   //get usersetting from db
//   $sql= 'SELECT * FROM userSetting WHERE user = "' .$_SESSION["user"]. '"';
//   $result=mysqli_query($conn,$sql);
//   $userSetting=mysqli_fetch_all($result, MYSQLI_ASSOC);
//   $keyArr=array("bgColor","fontSize","fontColor","userName");

//   // if the user has no saved setting give it the default setting and save to db
//   if ($userSetting){
   
//     for ($i=0;$i<sizeof($keyArr);$i++){
//       $defaultSetting[$i]=$userSetting[0][$keyArr[$i]];
//     }; 
//   }else{
//     $userDetails=array('bgColor'=>$defaultSetting[0], 'fontSize'=>$defaultSetting[1], 'fontColor'=>$defaultSetting[2], 'userName'=>$defaultSetting[3], 'user'=>$_SESSION["user"]);
//     save_to_db($userDetails,'userSetting');
//     //save_to_db('(bgColor, fontSize, fontColor, user)','("' .$defaultSetting[0]. '", "' .$defaultSetting[1]. '", "' .$defaultSetting[2]. '", "' .$_SESSION["user"]. '")','userSetting');
//   };

// };


// $_SESSION['bgColor']=$defaultSetting[0];
// $_SESSION['fontSize']=$defaultSetting[1];
// $_SESSION['fontColor']=$defaultSetting[2];


$name = $email = $username = $phone = $address = $birthday = $body =$pass=$verifyPass= '';
$nameErr = $usernameErr = $emailErr = $phoneErr = $addressErr = $birthdayErr = $bodyErr = $connection = $pssErr = $verifyPassErr = ''; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flashcards</title>
  <!-- CSS only -->
  <link href="sass/main.css" rel="stylesheet" >
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css"> -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="css/tingle.css">
  <!-- <script src="Jquerry/jquery-3.6.1.js"></script> -->
</head>
<body >
  
  <!-- navbar -->
<nav class="navbar header" id = "header">
    <h6 class = "app-title"> 
      <a class="link" href="#">Flashcards V1.0</a>
    </h6> 
    <!-- <button data-bs-toggle="collapse" type="button" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Toggle navigation" aria-expanded="false">
      <span >as if</span>
    </button> -->
    <div id="navbarNav" class="navbarNav <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
      <ul >
        
        <li >
        <div class=" dropdown <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
              <button class="icon__btn dropdown-toggle" type="button"  style="color: white;" >
                <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
                   category
                </span>
              </button>
              <div class="dropdown_cont" id="categoryDropdown" >             
                <ul >
                  <li >
                    <a class="link"  href="profile.php"  >Add category</a>
                  </li>  
                  <li >
                    <a class="link" type="submit" name="sign_out" href="logout.php" >Edit category</a>
                  </li>               
                </ul>
              </div>  
            </div>
        </li> 
        
        <li >
          
          <div class=" dropdown <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
            <button class="menu__btn dropdown-toggle" type="button"  style="color: white;" >
              <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
                  note_stack
              </span>
            </button>
            <div class="dropdown_cont" id="deckDropdown" >             
              <ul >
                <li >
                <button class="menu__btn" type="button"  id="add_Deck__Btn">Add Deck</button>
                </li>  
                <li >
                <button class="menu__btn"  type="button"  id="edit_Deck__Btn">Edit Deck</button>

                </li>               
              </ul>
            </div>  
          </div>
        </li>

        <li >
          <div class=" dropdown <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
            <button class="icon__btn dropdown-toggle" type="button"  style="color: white;" >
              <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
                  school
              </span>
            </button>
            <div class="dropdown_cont" id="learnDropdown" >             
              <ul >
                <li >
                  <a class="link"  href="profile.php"  >Learn a deck</a>
                </li>  
                <li >
                  <a class="link" type="submit" name="sign_out" href="logout.php" >Take a test</a>
                </li>               
              </ul>
            </div>  
          </div>
        </li>

        <li >
          <div class=" dropdown <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
            <button class="icon__btn dropdown-toggle" type="button"  style="color: white;" >
              <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
                  search
              </span>
            </button>
            <div class="dropdown_cont" id="searchDropdown" >             
              <ul >
                <li >
                  <div class="search-Header">
                    <input type="search" name="deckSearch" id="deckSearch"> 
                  </div>
                </li>  
                <li >
                  <div>
                    <input type="checkbox" name="searchCards" id="searchCardsChkBx">
                    <label for="searchCardsChkBx">Search cards</label>
                  </div>
                </li>               
              </ul>
            </div>  
          </div>
          
        </li>
        
      </ul>   
    </div>

    <div class="guest dropdown <?php echo ($_SESSION['userName']=='guest' )? "d-block" : "d-none"; ?>">
      <button class="icon__btn dropdown-toggle" type="button"  style="color: white;" >
          <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
            person
          </span>
      </button>  
      <div class="dropdown_cont">
          <a class = "link" href = "login.php" >
          <i >Login</i>
          </a>
        </div>
      
    </div>




    <div class="loged_in  dropdown <?php echo ($_SESSION['userName'] == 'guest' )? "d-none" : "d-block";?>">
      <button class="icon__btn dropdown-toggle" type="button"  style="color: white;">
        <span class="material-symbols-outlined " style="font-size: 1.2rem;" >
          person
        </span>
      </button>
      <div class="dropdown_cont" id="userDropdown" >             
        <ul >
          <li >
            <a class="link"  href="profile.php"  >Profile</a>
          </li>  
          <li >
            <a class="link" type="submit" name="sign_out" href="logout.php" >Sign Out</a>
          </li>               
        </ul>
      </div>  
    </div>
    
      
</nav>


<script>
    (function(){

      //logout event
        $('body').delegate('.logoutBtn','click',function(){
         $logout=$(this);

          $.post(
            'ajax.php',
            { action : 'logout'},
            function(data){ 
              if(data == '1'){ //hides the portfo link and shows login link
                $logout.closest('div').removeClass('d-block').addClass('d-none');
                $('body').find('div.guest').removeClass('d-none').addClass('d-block');
                window.location.href = 'index.php';

              };               
            }
          );
        });
    });
</script>
<?php
?>