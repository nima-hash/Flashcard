<?php
// if (!isset($_SESSION)) {
  // session_start();
  $dotenv = file(__DIR__ . "/databankconfig.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($dotenv as $line) {
    putenv(trim($line));
}


class Connection{

  protected $connection = null;

  public function __construct()
  {
        // $connection = null;

        $DB_HOST = getenv("DB_HOST");
        $DB_USERNAME = getenv("DB_USERNAME");
        $DB_PASSWORD = getenv("DB_PASSWORD");
        $DB_DATABASE_NAME = getenv("DB_DATABASE_NAME");
        $DB_PORT = getenv("DB_PORT");
        try {
            $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE_NAME;charset=utf8mb4"; 
            $this->connection = new PDO(
                $dsn,
                $DB_USERNAME,
                $DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
        } catch (PDOException $e) {
            // throw new Exception($e->getMessage()); 
            die("Database connection failed: " . $e->getMessage());
        }			
    }

    public function prepareStatement($query) {
      $stmt = $this -> connection -> prepare($query);
      return $stmt;
    }

    public function insertedId($stmt) {
      $id = $stmt -> connection -> lastInsertId();
      return $id;
    }
}


class Users extends Connection{
  
  private $connect;

  public function __construct(){
    $this -> connect = new Connection;
  }

  public function getuser($userName){
    
    $query = "SELECT * FROM Users WHERE userName = :userName LIMIT 1";
    $stmt= $this -> connect -> prepareStatement($query);
    // $stmt = $this -> connection -> prepare($query);
    $stmt->execute(['userName' => $userName]);
    $userObject = $stmt -> fetch(); 
    return $userObject ?: false;
  }

  public function add_user($userdata){

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

  public function check_password($username, $password){

    try{
      $user_data = $this->getuser($username);
      $userCards = $this -> getAllUserCards ($user_data['user_id']);
      
      $arrangedCards = arrangeCardsInDecks($userCards);
      
      if ($user_data){
        if (password_verify($password, $user_data['pass'])){

          $this -> assignUserdataToSession($user_data, $arrangedCards);          
          return $userCards;
          return true;
        }
        else {
          return false;
        }
  
      }
    }catch (Exception $e){
      throw new Exception ('error:' . $e->getMessage());
    }  
  }
  
  public function getAllUserCards ($userId){
    $query = "SELECT * FROM Content WHERE userId = :userId";
    $stmt = $this -> connect -> prepareStatement($query);
    $stmt -> execute(['userId' => $userId]);
    $cards = $stmt -> fetchAll();
    return $cards;
  }

  public function assignUserdataToSession($user_data, $arrangedCards){
    
    list  ('user_id' => $_SESSION['user_id'], 'access' => $_SESSION['access'], 'userName' => $_SESSION['userName']) = $user_data;      
    $_SESSION['decks'] = $arrangedCards;

  }
}


// $_SESSION['user_id'] = $user_data['user_id'];
          // $_SESSION['user_access'] = $user_data['access'];
          // $_SESSION['userName'] = $user_data['userName'];

  
?>