<?php

require __DIR__ . "/inc/bootstrap.php";
$data ='';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST,GET,OPTIONS');
header('Access-Control-Allow-headers: Content-Type,Authorization,X-Requested-With');

$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
$uri = explode( '/', $parsedUrl['path'] );
parse_str($_SERVER['QUERY_STRING'], $params);

//sanitize input
function sanitizeInput($input){
  return is_array($input) ? array_map('sanitizeInput', $input) : htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
$params = sanitizeInput($params);

//validate request
if ((isset($uri[1]) && $uri[1] != 'api') || !isset($params['action'])) {
    header("HTTP/1.1 404 Not Found");
    exit(json_encode(["error" => "Invalid request"]));
}

// Define allowed actions
$action = $params['action'];
$allowedActions = ['getDeckData', 'getCard', 'updateDeck', 'addCard', 'getCards', 'deleteCard']; 
if (!in_array($action, $allowedActions)) {
  header("HTTP/1.1 400 Bad Request");
  die(json_encode(["error" => "Invalid action."]));
}
$strMethodName = $action . 'Action';

if (strpos($_SERVER['CONTENT_TYPE'], 'application/json'))
  {
    $data = json_decode(file_get_contents("php://input"), true);    }elseif (strpos($_SERVER['CONTENT_TYPE'], 'form-data')) {    
      $data = $_POST;
  }
   
$data = sanitizeInput($data);
    
// Load the correct controller dynamically
switch (true) {
  case stripos($action, 'Card') !== false:
      require_once PROJECT_ROOT_PATH . "/Controller/Api/CardController.php";
      $objFeedController = new CardController();
      break;
  
  case stripos($action, 'Deck') !== false:
      require_once PROJECT_ROOT_PATH . "/Controller/Api/DeckController.php";
      $objFeedController = new DeckController();
      break;

  default:
      header("HTTP/1.1 400 Bad Request");
      die(json_encode(["error" => "Invalid action"]));
}


//call the method dynamically
if ($data){
    
    $objFeedController->{$strMethodName}($params, $data);

  }else{
    // if ($action == 'getCards'){
    //   print_r($params) ;
    //   // print_r($data) ;
    //   print_r($strMethodName) ;
    //   die;
    // }
    $objFeedController->{$strMethodName}($params);
    

  }  
?>