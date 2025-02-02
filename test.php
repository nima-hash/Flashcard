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
  <link href="test.css" rel="stylesheet" >
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css"> -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="style1.css">
  <!-- <script src="Jquerry/jquery-3.6.1.js"></script> -->
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
  <script src="script.js"></script>


</head>
<body >
<div class ='main-Cont' id="mainCenterDisplay">

  <div class ='main-Center' id="mainCenterDisplay">
<form method="post" id="emptyCardForm" class="emptyCardForm">

          <div class="emptyCardHeader">
            <button type="button" class= "Btn cardSaveBtn" id = "cardsSaveBtn"> Done </button>
            <span class="emptyCardHeader_DeckName">${deckName}</span>
            <button type="submit" class= "Btn addNewCardBtn" id= "addNewCardBtn"> + </button>
          </div>

          <div class="emptyCardFront">
            <textarea name="cardFrontText" id="emptyCardFrontText" cols="50" rows="10" placeholder="Front of the card" contenteditable="true"></textarea>
          </div>

          <div class="emptyCardBack">
          <textarea name="cardBackText" id="emptyCardBackText" cols="30" rows="10" placeholder="Back of the card"></textarea>
          </div>
          
        </form>

        </div>
</dev>
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
  </body>
  </html>