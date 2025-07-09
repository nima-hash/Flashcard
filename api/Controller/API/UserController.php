<?php
class UserController extends BaseController
{
   

    // protected function sendOutput($data, $httpHeaders = [])
    // {
    //     header_remove('Set-Cookie'); // Remove if session-based authentication is needed
        
    //         if (!empty($httpHeaders) && is_array($httpHeaders)) {
    //             foreach ($httpHeaders as $httpHeader) {
    //                 header($httpHeader);
    //             }
    //         }

    //     // If $data is an array, convert it to JSON
    //     // if (is_array($data) || is_object($data)) {
    //     //     header('Content-Type: application/json'); // Ensure JSON response
    //     //     echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    //     // } else {
    //         echo $data;
    //     // }

    //     exit;
    // }
    private $connect;

    public function __construct()
    {
        $this -> connect = new Connection;
    }

    public function getuser($userName)
    {
    
        // $query = "SELECT * FROM Users WHERE userName = :userName LIMIT 1";
        // $stmt= $this -> connect -> prepareStatement($query);
        // // $stmt = $this -> connection -> prepare($query);
        // $stmt->execute(['userName' => $userName]);
        // $userObject = $stmt -> fetch(); 
        $userModel = new UserModel;
        $userObject = $userModel -> getUser($userName);
        return $userObject ?: false;
    }

    public function add_user($userdata)
    {

        try{
        $user = test_input($userdata["user__input"]);
        $email = test_input($userdata["email__input"]);
        $phone = test_input($userdata["phone__input"]);
        $comment = test_input($userdata["comment"]);
        $birth = test_input($userdata["birth__input"]);
        $address = test_input($userdata["address__input"]);
        $password = $userdata["pass__input"];
        $access = 1;
        // $hash = password_hash($userdata['pass'], PASSWORD_DEFAULT);
        
        if (check_duplicate_user($user)){

            $id = check_duplicate_user($user);
            //validate password
            if ($userdata["pass__input"] !== $userdata["pass-verify__input"]) {
            $verifyPassErr = "Password and confirm Password fields do not match.please try again.";
            }else{
            if (validate_Pass($password)){
                // Use password_hash() function to create a password hash
                $hashedPassword = password_hash($password,PASSWORD_DEFAULT);
                // save to db
        
                // $connection = new Connection;
                // $conn = $connection->connect();
                $query = "INSERT INTO Users (userName, pass, email, phone, user_id, access) VALUES ( :user, :hashedPassword, :email, :phone, :id, :access)";
                $stmt= $this -> connect -> prepareStatement($query); 
                // $stmt = $this -> connection -> prepare($query);
                $stmt -> execute ([
                'userName' => $user,
                'pass' => $hashedPassword,
                'email' => $email,
                'phone' => $phone,
                'user_id' => $id, 
                'access' => $access]);
                
                // Return inserted row ID

                return $this->connect->insertedId($stmt);
                }
            }
        }
        } catch (Exception $e) {
        throw new Exception("Insert Query Error: " . $e->getMessage());
        }
    }

    public function check_password($username, $password)
    {

        $user_data = $this->getuser($username);
        if (!$user_data) {
            throw new Exception("Error: This user does not exist. Do you want to sign up?", 400);
        }
       
        $userCards = $this -> getAllUserCards ($user_data[0]['user_id']);
        
        $arrangedCards = arrangeCardsInDecks($userCards);
        
        if ($user_data[0]){
            if (password_verify($password, $user_data[0]['pass'])){

            $this -> assignUserdataToSession($user_data[0], $arrangedCards);          
            return $userCards;
            return true;
            }
            else {
            return false;
            }
    
        }
        // }catch (Exception $e){
            
        // throw new Exception ('error:' . $e->getMessage());
        // }  
    }
    
    public function getAllUserCards ($userId)
    {
        $query = "SELECT * FROM Content WHERE userId = :userId";
        $stmt = $this -> connect -> prepareStatement($query);
        $stmt -> execute(['userId' => $userId]);
        $cards = $stmt -> fetchAll();
        return $cards;
    }

    public function assignUserdataToSession($user_data, $arrangedCards)
    {
        
        list  ('user_id' => $_SESSION['user_id'], 'access' => $_SESSION['access'], 'userName' => $_SESSION['userName']) = $user_data;      
        $_SESSION['decks'] = $arrangedCards;

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

    public function loginAction($userData){
        try{
            if ($this->check_password($userData['user__input'], $userData['pass__input'])){

                    header("Location: index.php");
                    die();
                  } else {
                    echo "Wrong password !!"; //errormodule
                   
                
                  }
                  
        }
        catch(Exception $e){
            $this->sendOutput(json_encode(array('error' => $e->getMessage())),
            array('Content-Type: application/json', $e->getCode() ?: 500)
        );
        }
    }


    public function postAction($userData)
    {
        try {
            // $strErrorDesc = '';
            // $requestMethod = $_SERVER["REQUEST_METHOD"];
              
            // if (strtoupper($requestMethod) == 'POST') {
                // try {

                    $user = new UserVerify; 
                    $user -> validateRegData($userData); 
      
                    $userEl = $user -> convertDataToDbFormat($userData);
                    $userModel = new UserModel();
                   
                    $registerResult = $userModel->registerUser($userEl);
                     
                    if ($registerResult)
                    {
                      $responseData = json_encode("The user was Successfully added.");
                    }
                  
                  
                // } catch (Exception $e) {
                  
                //     $strErrorDesc = $e->getMessage().' Something went wrong! Please contact support.';
                //     $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                // }
                
            // } else {
            //     $strErrorDesc = 'Method not supported';
            //     $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            // }
            
            // send output 
            // if (!$strErrorDesc) {
                // http_response_code(200);
                
                $this->sendOutput(
                    $responseData,
                    array('Content-Type: application/json', 200)
                );
            // } else {
            //   $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
            //         array('Content-Type: application/json', $strErrorHeader)
            //     );
            // }
        } catch (Exception $e) {
            $this->sendOutput(json_encode(array('error' => $e->getMessage())),
                    array('Content-Type: application/json', $e->getCode() ?: 500)
                );
            // $strErrorDesc = $e->getMessage().' Something went wrong! Please contact support.';
            // $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
    //   $strErrorDesc = '';
    //   $requestMethod = $_SERVER["REQUEST_METHOD"];
        
    //   if (strtoupper($requestMethod) == 'POST') {
    //       try {
    //         $user = new UserVerify;
            
    //         $user -> validateRegData($userData);
           

    //           $userEl = $user -> convertDataToDbFormat($userData);
    //           $userModel = new UserModel();

              
             
    //           $registerResult = $userModel->registerUser($userEl);

              
    //           if ($registerResult)
    //           {
    //             $responseData = json_encode("The user was Successfully added.");
    //           }
            
            
    //       } catch (Exception $e) {
    //         var_dump($e);
    //         die;
                
    //           $strErrorDesc = $e->getMessage().' Something went wrong! Please contact support.';
    //           $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
    //       }
          
    //   } else {
    //       $strErrorDesc = 'Method not supported';
    //       $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
    //   }
      
    //   // send output 
    //   if (!$strErrorDesc) {
            
    //       $this->sendOutput(
    //           $responseData,
    //           array('Content-Type: application/json', 'HTTP/1.1 200 OK')
    //       );
    //   } else {
    //     $this->sendOutput(json_encode(array('error' => $strErrorDesc)),
    //           array('Content-Type: application/json', $strErrorHeader)
    //       );
    //   }
    }
}

class UserVerify extends UserController
{
    
    private function checkEmptyInput($userData)
    {
       
        foreach($userData as $key => $value) {
            $trimmedValue = trim($value);
            if (!$trimmedValue) {
                
                throw new Exception( $key . " can not be empty.please enter a valid input", 400);
            } else {
                $userData[$key] = htmlspecialchars(strip_tags($trimmedValue));
            }
        }
  
    }
    
    private function userValidate($userData)
    {
        
        $userModel = new UserModel;

        $userName = $userData['user__input'];
        $results = $userModel->getUser($userName);
        
        
            if($results)
            {
                throw new Exception("this user already exists please choose a new user", 400);
            } else if(!preg_match("/^[ a-z0-9-\'äüößéáë]{2,31}$/i",$userData['user__input'])){
                throw new Exception("please use only numbers and alphabets for username ", 400);
            }
           
    }

    private function passVerify($userData)
    {
        
            if($userData['pass__input'] !== $userData['pass-verify__input']) {
                throw new Exception('Password and confirm Password fields do not match.please try again', 400);
            }
            $pass = $userData['pass__input'];
            $uppercase = preg_match('@[A-Z]@', $pass);
            $lowercase = preg_match('@[a-z]@', $pass);
            $number    = preg_match('@[0-9]@', $pass);
            $specialChars = preg_match('@[^\w]@', $pass);
          
            if(!($uppercase && $lowercase && $number && $specialChars && strlen($pass)>8)){
                throw new Exception('the password must contain at least one special character, one number, one small and one large alphabet and be at least 8 charachters long.', 400);
            }
    }

    private function emailValidate($userData)
    {   
        
        if (!filter_var($userData['email__input'], FILTER_VALIDATE_EMAIL)) {

            throw new Exception('Invalid email format', 400);
            }
      
    }

    public function phoneValidate($userData){
        if (!preg_match('/^\d+$/', $userData['phone__input'])){
            throw new Exception ('Please Enter a valid phone number!!', 400);
        }
    }


    
    public function validateRegData($userData)
    {   
    
        $this -> checkEmptyInput($userData);
        $this -> userValidate($userData);
        $this -> passVerify($userData);
        $this -> emailValidate($userData);
        $this -> phoneValidate($userData);

    }

    public function convertDataToDbFormat($userData)
    {
        [
            'user__input' => $userName,
            'pass__input' => $pass,
            'email__input' => $email,
            'phone__input' => $phone, 
            'address__input' => $address
        ]= $userData;

        return [
            'userName' => $userName,
            'pass' => password_hash($pass, PASSWORD_BCRYPT),
            'email' => $email,
            'phone' => $phone,
            'user_id' => uniqid(),
            'address' => $address
        ];
        
    }
}

