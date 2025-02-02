<?php
// session_start();
// require PROJECT_ROOT_PATH . "/Model/UserModel.php";

class DeckController extends BaseController
{
    

    private function checkEmptyInput($data)
    {
      try
      {
        if (!$data)
        {
          throw new Exception("no data was submitted : ");

        }
        foreach ($data as $key => $value) {
          if (empty($data[$key]))
          {
            throw new Exception("the field " . $key . " can not be empty");

          }
        }
        return false;
      }catch (Exception $e){
        throw new Exception($e->getMessage());
        return false;
      }   
    }

    private function DeckNameValidate($data)
    {
      
        try
        {
          $deckName = $data['deckName__input'];

          $deckModel = new DeckModel;
          //check for existing name in decks table
          $userId = $_SESSION['user_id'];
          $params = array($deckName, $userId);
          
          $getresults = $deckModel->getDeck($params); 
                
          // $deckEl =$getresults[0]['deckName'];
          
          
          if (!empty($getresults) )
          {
            throw new Exception("you already have a deck with the name : " . $deckName);           
          }
          
          //check for existing name in user table
          $userModel = new UserModel;
          
          $userName = $_SESSION['userName'];
          
          $results = $userModel->getUser($userName);
          
          $decksArr =json_decode($results[0]['decks']);



          
          if (!$deckName)
          {
            throw new Exception("you must choose a name for deck ");

          }
          if(!preg_match("/^[ a-z0-9-\'äüößéáë]{2,31}$/i",$deckName)){
            throw new Exception("please use only numbers and alphabets for deckname ");
          }

          if (!empty($decksArr))
          {
            
            if (in_array( $deckName, $decksArr))
            {
              throw new Exception("you already have a deck with the name : " . $deckName);
            
            }

          // }else{
          //   $decksArr = [];
          }

          
          
          return true;
        }catch (Exception $e){
          throw new Exception($e->getMessage());
          return false;
        }      
    }

    public function deckValidate($data)
    {
      
      try
      {      
        // $deckModel = new DeckController;
        
        $checkEmptyInput = $this -> checkEmptyInput($data);
        
        $nameValidate = $this -> DeckNameValidate($data);
        
        if($checkEmptyInput || !$nameValidate) {
          throw new Exception("there was an unknown problem. please try again"); 
      }
        
      return $this;
      }catch (Exception $e){
        throw new Exception($e->getMessage());
        return false;
      }        
    }

    public function convertDataForDecksTbl($deckData)
    {
      $decksArr = [];
      foreach($deckData as $key => $value) {
        array_push($decksArr, $value); 
      }
      array_push($decksArr, $_SESSION['user_id']); 
      $deckId = uniqid();
      array_push($decksArr, $deckId); 

      return $decksArr;     
    }

    public function convertDataForUserTbl($deckData)
    {
      $userModel = new UserModel;
          $userName = $_SESSION['userName'];
          $results = $userModel->getUser($userName);
          $decksArr = json_decode($results[0]['decks']);

          if (empty($decksArr )) 
          {
            $decksArr =[];
          }
          
          // $deck = [];
          // $deck['deckName'] = $deckData['deck_Name'];
          // $deck['deck_id'] = uniqid();

          array_push($decksArr, $deckData['deckName__input']);  
          
          
        // Use password_hash() function to create a password hash

        // $deck['pass'] = password_hash($deckData['pass__input'],PASSWORD_BCRYPT);
        
        
        return json_encode($decksArr);



    }

    public function addDeckAction($params, $data)
    {
      
      $strErrorDesc = '';
      $requestMethod = $_SERVER["REQUEST_METHOD"];
      
      if (strtoupper($requestMethod) == 'POST') {
          try {
            // $deck = new DeckController;
            // $arrQueryStringParams = $this->getQueryStringParams();
            //check for correct action
            if (!isset($params['action']) || $params['action'] !== 'addDeck') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);

            
            $this -> deckValidate($data);

            //make the new deck array
            $deckEl = $this -> convertDataForUserTbl($data);
            
            //make the new deck paramaters for decks Table

            $deckParams = $this -> convertDataForDecksTbl($data);

            // add to deck table
            $result = $this -> addToDeckTbl($deckParams);

            if (!$result){
              throw new Exception("Could not save to database");              
            }            
            $userModel = new UserModel();
              
              $registerResult = $userModel->addDeck($deckEl , $_SESSION['user_id']);
              
              
              if ($registerResult)
              {
                $_SESSION['decks'] = json_decode($deckEl);
                $responseData = json_encode("The deck was Successfully added.");
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
    
    public function getDeckDataAction($vars=[])
    {
     
      $strErrorDesc = '';
      // $vars = $this->getQueryStringParams();
      // if (strtoupper($requestMethod) == 'GET') {
          try {
              $deckModel = new deckModel();
              
              //check for correct action
              if (!isset($vars['action']) || $vars['action'] !== 'getDeckData') {
                
                throw new Exception("Error Processing Request"); 
                    
              }
              
              //remove action from query parameters
              unset($vars['action']);
              
              // $vars = array_values($vars);
              
              if (!isset($vars['deckId']) || empty($vars['deckId'])) {
                throw new Exception("you must choose a deck");     
              }
              
              $vars['user_id'] = $_SESSION['user_id'];
              
              $dataArr = $deckModel->getDeck($vars);
              
              //send output
              $this->sendOutput(
                json_encode($dataArr),
                ['Content-Type: application/json', 'HTTP/1.1 200 OK']
            );
            return;
             
          } catch (Exception $e) {
              $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
              http_response_code(500);
          }
      // } else {
      //     $strErrorDesc = 'Method not supported';
      //     $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      // }
      // send output 
      // if (!$strErrorDesc) {
      //     $this->sendOutput(
      //         $responseData,
      //         array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      //     );
      // } else {
      //     $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
      //         array('Content-Type: application/json', $strErrorHeader)
      //     );
      // }
    }

    public function addToDeckTbl($data)
    {
      try
      {
        $deckModel = new DeckModel();
           
        $registerResult = $deckModel->addDeck($data);
        
        if (!$registerResult)
        {
         throw new Exception("Couldnt save to  database");        
        }
        return true;
        
      }catch (Exception $e){
        throw new Exception($e->getMessage());
        return false;
      }   
      
      
      
      
      
    }

   
    

}