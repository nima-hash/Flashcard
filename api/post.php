<?php

session_start();
require __DIR__ . "/inc/bootstrap.php";

header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');
header('Content-Type: multipart/formdata');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-headers: Access-Control-Allow-Origin,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-with');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = strtolower($_SERVER['REQUEST_METHOD']);
$uri = explode( '/', $uri );


if ((isset($uri[1]) && $uri[1] != 'api') || !$method) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// include the deck controller file 

//get Raw data
// $data = json_decode(file_get_contents("php://input"));

//determin action

// $actionArr = $objFeedController->getQueryStringParams();

// if (!isset($actionArr['action'])) {
//   header("HTTP/1.1 404 Invalid Request");
//   exit();
// }
// $action = 'addDeck';
// $action = htmlspecialchars(strip_tags($actionArr['action']));;
// print_r(json_encode($_SERVER['CONTENT_TYPE']));
// exit;
if (strpos($_SERVER['CONTENT_TYPE'], 'pplication/json')){
  $data = json_decode(file_get_contents("php://input"));
  
  // array_walk_recursive($data, 'test' );
  // function test($value, $key){
  //   $key = htmlspecialchars($key);
  //   $value = htmlspecialchars($value);
  // };

  // for ($i=0; $i <sizeof($data) ; $i++) { 
  //   $data[$i]=htmlspecialchars(strip_tags($data[$i]));
  //   print_r(json_encode($data));
  //   exit;
  // }
  // print_r(json_encode($data));
  //   exit;
} else if (strpos($_SERVER['CONTENT_TYPE'], 'form-data')) {
  
  $data = $_POST;
  //get post data
if (is_array($data))
{
  //sanitize inputs
  foreach ($data as $key=> $value){
   
    $data[$key] = htmlspecialchars(strip_tags($value));
    
  }
  
}
}


parse_str($_SERVER['QUERY_STRING'], $params);
$action=$params['action'];
unset($params['action']);
// $action = parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT);
$strMethodName = $action . 'Action';

if (stripos($action, 'Card') > 0)
{
  require_once PROJECT_ROOT_PATH . "/Controller/Api/CardController.php";

  $objFeedController = new CardController();
  
  if ($data){
    
    $objFeedController->{$strMethodName}($params, $data);

  }else{
    $objFeedController->{$strMethodName}($params);

  }
}
if (stripos($action, 'Deck') > 0)
{
  require_once PROJECT_ROOT_PATH . "/Controller/Api/DeckController.php";

  $objFeedController = new DeckController();
  
  $objFeedController->{$strMethodName}($data);
}
