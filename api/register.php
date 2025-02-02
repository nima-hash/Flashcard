<?php

require __DIR__ . "/inc/bootstrap.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-headers: Content-Type,Authorization,X-Requested-with');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$action = strtolower($_SERVER['REQUEST_METHOD']);
$uri = explode( '/', $uri );

if ((isset($uri[1]) && $uri[1] != 'api') || !$action) {
    header("HTTP/1.1 404 Not Found");
    exit();
}
require "/Applications/MAMP/htdocs/Flashcards/api/Controller/Api/UserController.php";
$objFeedController = new UserController();

// Determine how data is being sent
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
  $data = json_decode(file_get_contents("php://input"), true);
} else {
  $data = $_POST;
}


// Sanitize inputs
if (is_array($data)) {
  foreach ($data as $key => $value) {
      $data[$key] = htmlspecialchars(strip_tags($value));
  }
}
  
$strMethodName = $action . 'Action';
$objFeedController->{$strMethodName}($data);