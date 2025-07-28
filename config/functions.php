<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ .  "/database.php";

function page_redirect($url){
  header("location: $url");
  die;
}

function checkLogin(){

  if (!isset($_SESSION['user_id'])) {
    //redirect to login
    header("location: " . __DIR__ . "/../login.php");
    exit();
  }

}

function console_log($variable) {
  echo "<script>console.log(" . json_encode($variable, JSON_PRETTY_PRINT) . ");</script>";
}

//gives correct dateformat    
function validateDate($date, $format = 'Y-m-d'){
  $d = DateTime::createFromFormat($format, $date);
  return $d && $d->format($format) === $date;
}





  // Make Object Values INT
  function convertNumericStringsToInt(array $array): array
{
    foreach ($array as $key => $value) {
        if (is_string($value)) {
            // Check if string is numeric 
            if (is_numeric($value)) {
                // Check if the numeric string is an integer
                if (strpos($value, '.') === false && strpos($value, ',') === false) {
                    $array[$key] = (int)$value;
                }
            }
        } elseif (is_array($value)) {
            // Recursively process nested arrays
            $array[$key] = convertNumericStringsToInt($value);
        }
    }
    
    return $array;
}

//Sets the conditions for password strength
function validate_password_strength(string $password): ?string
{
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    // At least one special character
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/' , $password)) {
        return "Password must contain at least one special character.";
    }
    // At least one number
    if (!preg_match('/\d/', $password)) {
        return "Password must contain at least one number.";
    }
    // At least one capital letter
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one capital letter.";
    }
    // At least one small letter
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one small letter.";
    }
    return null;
}
?>