<?php

if (isset($_POST) ){
  // echo(json_encode($_POST));
echo (json_encode($_SERVER['REQUEST_URI']));
  // echo($_POST['deck_Name']);
  // echo ("<br>");
  // print_r ($_POST);
  // echo "why";
  // $deck = $_POST['deck_Name'];
  // $deckId = uniqid();
  // if ($_SESSION['user_Decks']){
  //   $userDecks = $_SESSION['user_Decks'];
  //   $userDecks[$deckId] = $deck;
  // }else {
  //   $userDecks = array($deckId => $deck);
  // }
  
  // $userDecks_json = json_encode($userDecks);

  // $connection = new connection;
  // $conn = $connection->connect();
  // $sql = "UPDATE  Users SET decks = '" . $userDecks_json . "' WHERE user_id = '" . $_SESSION['user_id'] . "'";
  // $result = $conn->query($sql);
  // if ($result) {
  //   echo json_encode($userDecks);
  // }else { 
  //   echo "failed" . $conn->connect_error;
    
  // }
}


?>