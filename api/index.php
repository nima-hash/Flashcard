<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/inc/bootstrap.php"; 
require_once __DIR__ . "/Controller/Api/BaseController.php"; 

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Allow-headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- API Routing Logic ---

$requestUri = $_SERVER['REQUEST_URI'];

// Find the position of '/api/' in the URI to get the relative path
$apiPos = strpos($requestUri, '/api/');
if ($apiPos === false) {
    http_response_code(404);
    echo json_encode(["error" => "API base path not found in URI."]);
    exit();
}

// Get the part of the URI after '/api/'
$pathAfterApi = substr($requestUri, $apiPos + strlen('/api/'));
// Remove query string
$pathAfterApi = explode('?', $pathAfterApi)[0]; 

// Split into segments and filter empty ones
$uriSegments = array_values(array_filter(explode('/', $pathAfterApi)));

// Remove .php extension from the last segment if present
if (!empty($uriSegments)) {
    $lastSegmentIndex = count($uriSegments) - 1;
    $uriSegments[$lastSegmentIndex] = str_replace('.php', '', $uriSegments[$lastSegmentIndex]);
}

$controllerName = $uriSegments[0] ?? null;
$methodName = $uriSegments[1] ?? null;

// Sanitize query parameters
parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
$params = sanitizeInput($params);

// Sanitize a given input value 
function sanitizeInput($input) {
    return is_array($input) ? array_map('sanitizeInput', $input) : htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validate the request and map to a controller
$controllerMap = [
    'decks' => [
        'className' => 'DeckController',
        'allowedMethods' => ['get', 'create', 'edit', 'delete', 'updateRecord']
    ],
    'cards' => [
        'className' => 'CardController',
        'allowedMethods' => ['get', 'create', 'edit', 'delete', 'rate']
    ],
    'categories' => [
        'className' => 'CategoryController',
        'allowedMethods' => ['get', 'create', 'edit', 'delete']
    ],
    'study' => [
        'className' => 'StudyController',
        'allowedMethods' => ['score']
    ],
    'search' => [
        'className' => 'SearchController',
        'allowedMethods' => ['get']
    ],
    'users' => [
        'className' => 'UserController',
        'allowedMethods' => ['get', 'create', 'update', 'uploadPicture', 'resetPassword', 'login', 'logout', 'passwordResetEmailRequest', 'resetByToken', 'verifyToken']
    ],
];

// Check if a valid controller and method were requested
if (!isset($controllerMap[$controllerName]) || !in_array($methodName, $controllerMap[$controllerName]['allowedMethods'])) {
    $baseController = new BaseController(); 
    $baseController->sendOutput(json_encode(["error" => "Invalid API endpoint or method: /api/{$controllerName}/{$methodName}"]), 404);
    exit();
}

// Load the controller file dynamically
$controllerClassName = $controllerMap[$controllerName]['className'];
require_once __DIR__ . "/Controller/Api/" . $controllerClassName . ".php";
$objController = new $controllerClassName();


$requestBody = [];
if (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $json = file_get_contents("php://input");
    if ($json) {
        $requestBody = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $baseController = new BaseController(); 
            $baseController->sendOutput(json_encode(['success' => false, 'message' => 'Invalid JSON in request body.']), 400);
            exit();
        }
    }
}
// For non-JSON data
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestBody = $_POST;
}

// Sanitize the request body
$requestBody = sanitizeInput($requestBody);

// Dynamically call the controller method
$strMethodName = $methodName . 'Action';

// Pass the request data to the controller method
try {
    if (method_exists($objController, $strMethodName)) {
        $objController->{$strMethodName}($params, $requestBody);
    } else {
        $baseController = new BaseController(); 
        $baseController->sendOutput(json_encode(['success' => false, 'message' => 'Controller method not found: ' . $strMethodName]), 500);
    }
} catch (Exception $e) {
    $baseController = new BaseController();
    error_log("Caught exception in " . $controllerClassName . "::" . $strMethodName . ": " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile());
    $baseController->sendOutput(json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]), 500);
}