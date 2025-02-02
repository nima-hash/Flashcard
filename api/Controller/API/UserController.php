<?php
class UserController 
{
    /** 
    * "/user/list" Endpoint - Get list of users 
    */

    protected function sendOutput($data, $httpHeaders = [])
    {
    header_remove('Set-Cookie'); // Remove if session-based authentication is needed
    
    if (!empty($httpHeaders) && is_array($httpHeaders)) {
        foreach ($httpHeaders as $httpHeader) {
            header($httpHeader);
        }
    }

    // If $data is an array, convert it to JSON
    // if (is_array($data) || is_object($data)) {
    //     header('Content-Type: application/json'); // Ensure JSON response
    //     echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    // } else {
        echo $data;
    // }

    exit;
    }

    public function listAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        parse_str($_SERVER['QUERY_STRING'], $arrQueryStringParams);
        if (strtoupper($requestMethod) == 'GET') {
            try {
                $userModel = new UserModel();
                $intLimit = 10;
                if (isset($arrQueryStringParams['limit']) && $arrQueryStringParams['limit']) {
                    $intLimit = $arrQueryStringParams['limit'];
                }
                $arrUsers = $userModel->getUsers($intLimit);
                $responseData = json_encode($arrUsers);
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
        }
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    public function postAction($userData)
    {
        
      $strErrorDesc = '';
      $requestMethod = $_SERVER["REQUEST_METHOD"];
        //   $arrQueryStringParams = $this->getQueryStringParams();
        
      if (strtoupper($requestMethod) == 'POST') {
          try {
            $user = new UserVerify;
            
            $user -> validateRegData($userData);
           
            //   if ($user->checkEmptyInput($userData)) {
            //     throw new Exception("all input fieldsmust be filled.");
            //   }  
            //   if (!($user -> userValidate($userData))) {
                
            //     throw new Exception("this username already exists.");
            //   } 
              
            //   if (!($user -> passVerify($userData))) {
            //     throw new Exception("verify password doesnot match");
            //   }
              
            //   if (!($user -> emailValidate($userData))) {
            //     throw new Exception("enter a valid email");
            //   }
              
            //   if (!($user -> birthdayValidate($userData))) {
            //     throw new Exception("user must be at least 10 years old");
            //   }
            //   if (!($user -> phoneValidate($userData))) {
            //     throw new Exception("enter a valid phone in the 0XX XXXXXX format");
            //   } 
            //   if (!($user -> adressValidate($userData))) {
            //     throw new Exception("enter a valid address");
            //   } 

              $userEl = $user -> convertDataToDbFormat($userData);
              $userModel = new UserModel();

              
             
              $registerResult = $userModel->registerUser($userEl);

              
              if ($registerResult)
              {
                $responseData = json_encode("The user was Successfully added.");
              }
            
            
          } catch (Exception $e) {
            
              $strErrorDesc = $e->getMessage().' Something went wrong! Please contact support.';
              $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
          }
          
      } else {
          $strErrorDesc = 'Method not supported';
          $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      }
      
      // send output 
      if (!$strErrorDesc) {
            
          $this->sendOutput(
              $responseData,
              array('Content-Type: application/json', 'HTTP/1.1 200 OK')
          );
      } else {
        $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
              array('Content-Type: application/json', $strErrorHeader)
          );
      }
    }
}

class UserVerify extends UserController
{
    
    private function checkEmptyInput($userData)
    {
        try
        {

        
        foreach($userData as $key => $value) {
            $trimmedValue = trim($value);
            if (empty($trimmedValue)) {
                
                throw new Exception( $key . " can not be empty.please enter a valid input");
            } else {
                $userData[$key] = htmlspecialchars(strip_tags($trimmedValue));
            }
        }
        return true;
        } catch (Exception $e){
            return $e -> getMessage();            
        }
    }
    
    private function userValidate($userData)
    {
        
        $userModel = new UserModel;

        $userName = $userData['user__input'];
        $results = $userModel->getUser($userName);

        try 
        {
            if($results)
            {
                throw new Exception("this user already exists please choose a new user");
            }
            if(!preg_match("/^[ a-z0-9-\'äüößéáë]{2,31}$/i",$userData['user__input'])){
                throw new Exception("please use only numbers and alphabets for username ");
            }
            return true;
        // if($results)
        // {
        //         return false;
            
        // }else {

        //     return true;
        // }
        } catch (Exception $e){
            throw new Exception($e->getMessage());
            return false;
        }
    }

    private function passVerify($userData)
    {
        try
        {
            if($userData['pass__input'] !== $userData['pass-verify__input']) {
                throw new Exception('Password and confirm Password fields do not match.please try again');
            }
            $pass = $userData['pass__input'];
            $uppercase = preg_match('@[A-Z]@', $pass);
            $lowercase = preg_match('@[a-z]@', $pass);
            $number    = preg_match('@[0-9]@', $pass);
            $specialChars = preg_match('@[^\w]@', $pass);
          
            if(!($uppercase && $lowercase && $number && $specialChars && strlen($pass)>8)){
                throw new Exception('the password must contain at least one special character, one number, one small and one large alphabet and be at least 8 charachters long.');
            }
            
            
            return true;
        } catch (Exception $e){
            throw new Exception($e->getMessage());
            return false;
        }
    }

    private function emailValidate($userData)
    {
        try
        {
            // if(!preg_match('/^[a-z0-9-@_.äüößéáë]{2,31}$/i',$userData['email__input']) && !filter_var($userData['email__input'],FILTER_VALIDATE_EMAIL)) {
        if (!filter_var($userData['email__input'], FILTER_VALIDATE_EMAIL)) {

            throw new Exception('Invalid email format');
            }
            return true;
        } catch (Exception){
            return false;
        }
    }

    public function validateRegData($userData)
    {   
        try
        {       
        // $user = new UserVerify;
       
        $checkEmptyInput = $this -> checkEmptyInput($userData);
        print_r($checkEmptyInput);
        die;
        if (!$checkEmptyInput) {
            throw new Exception("Some required fields are empty");
        }
        $validateuser = $this -> userValidate($userData);
        $passVerify = $this -> passVerify($userData);
        // print_r($passVerify);
        // die;
        $emailValidate = $this -> emailValidate($userData);

        // If any of the validations failed, throw an exception
        if (!$validateuser || !$passVerify || !$emailValidate) {
            throw new Exception("There was an issue with the provided data. Please try again.");
        }
        // if($checkEmptyInput || !$validateuser || !$passVerify || !$emailValidate) {
        //     throw new Exception("there was an unknown problem. please try again"); 
        // }
        return true;
        } catch (Exception){
            // throw new Exception($e -> getMessage());
            return false;
        }
        
    }

    public function convertDataToDbFormat($userData)
    {
        $userObj = [];
        $userObj['userName'] = $userData['user__input'];

        // Use password_hash() function to create a password hash

        $userObj['pass'] = password_hash($userData['pass__input'],PASSWORD_BCRYPT);
        $userObj['email'] = $userData['email__input'];
        $userObj['phone'] = $userData['phone__input'];
        // $userObj['birthday'] = $userData['birth__input'];
        $userObj['user_id'] = uniqid();
        $userObj['adress'] = $userData['address__input'];
        
        return $userObj;



    }
}

