<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

require __DIR__ . "/inc/bootstrap.php";

echo "<pre>";
echo "RAW php://input:\n";
print_r(file_get_contents("php://input")); // Debug raw input
echo "\n\n\$_POST Data:\n";
print_r($_POST);
echo "\n\n\$_FILES Data:\n";
print_r($_FILES);
echo "</pre>";

exit;