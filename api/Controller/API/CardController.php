<?php
// session_start();
// require PROJECT_ROOT_PATH . "/Model/UserModel.php";

class CardController extends BaseController
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
        return true;
      }   
    }



    public function cardValidate($data)
    {
      
      try
      {      
        // $deckModel = new DeckController;
        
        $checkEmptyInput = $this -> checkEmptyInput($data);
        
        // $nameValidate = $this -> DeckNameValidate($data);
        
        if($checkEmptyInput) {
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
      try {
        $decksArr = [];
        // foreach($deckData as $key => $value) {
        //   array_push($decksArr, $value); 
        // }
        $totalCards= (int)$deckData['totalCards'] + 1;
        if (!is_int($totalCards))
        {
          throw new Exception("total number of cards is invalid");          
        }
  
        array_push($decksArr, $totalCards); 
        array_push($decksArr, $deckData['deck_id']); 
        array_push($decksArr, $_SESSION['user_id']); 
  
        return $decksArr;     
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
      
    }

    public function convertDataForCardsTbl($cardData, $deckEl)
    {
      
      try {
        
        // $cardModel = new CardModel;
        $userId = $_SESSION['user_id'];
        if ($userId !== $deckEl['user_id']){
          throw new Exception("Error getting the deck information");
          
        }
        // $results = $cardModel->getUser($userId);
        // $decksArr = json_decode($results[0]['decks']);

        // if (empty($decksArr )) 
        // {
        //   $decksArr =[];
        // }
        
        $cardEl = [];
        // $deck['deckName'] = $deckData['deck_Name'];
        // $deck['deck_id'] = uniqid();
        $cardId = uniqid();
        array_push($cardEl, $cardId); 
        array_push($cardEl, $deckEl['deck_id']); 
        array_push($cardEl, $deckEl['user_id']); 
        array_push($cardEl, $cardData['cardFrontText']); 
        array_push($cardEl, $cardData['cardBackText']); 
        array_push($cardEl, $deckEl['deckName']); 

        // Use password_hash() function to create a password hash

        // $deck['pass'] = password_hash($deckData['pass__input'],PASSWORD_BCRYPT);
        
        
        return $cardEl;

      } catch (Exception $e){
        throw new Exception($e->getMessage());
      }   
      
    }

    private function deckDataUpdateConverter($deckData, $cards)
    {
      try {
        // foreach($deckData as $key => $value) {
        //   array_push($decksArr, $value); 
        // }
        $decksArr = [];
        $totalCards= sizeof($cards);
        
        $mastery = $this->calculateMastery($cards);
        
        if (!empty('record')){
          $record = 0;
        }
        if (!empty('rating')){
          $rating = 0;
        }

        
        array_push($decksArr, $mastery); 
        array_push($decksArr, $totalCards); 
        array_push($decksArr, $record); 
        array_push($decksArr, $rating); 
        array_push($decksArr, $deckData['deck_id']); 
        array_push($decksArr, $_SESSION['user_id']); 
  
        return $decksArr;     
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
      
    }

    private function cardDatadeleteConverter($deckEl, $params)
    {
      
      try {
        // foreach($deckData as $key => $value) {
        //   array_push($decksArr, $value); 
        // }
        $Arr = [];
        $cardId= $params['cardId'];
        $deckId= $deckEl['deck_id'];
        
        
        
        
        
        array_push($Arr, $cardId); 
        array_push($Arr, $deckId); 
        
        return $Arr;     
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    private function cardDataUpdateConverter($card)
    {
      try {
        // foreach($deckData as $key => $value) {
        //   array_push($decksArr, $value); 
        // }
        $cardArr = [];
        
        $frontside = $card->frontContent;
        $backside = $card->backContent;
        $fav = $card->fav;
        $difficulty = $card->difficulty;
        $correctCounter = $card->correctCounter;
        $cardId = $card->cardId;
        
        array_push($cardArr, $frontside); 
        array_push($cardArr, $backside); 
        array_push($cardArr, $fav); 
        array_push($cardArr, $difficulty); 
        array_push($cardArr, $correctCounter); 
        array_push($cardArr, $cardId); 
  
        
        return $cardArr;     
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }
    
    private function parameterUpdateConverter($card, $parameter)
    {
      try {
        // foreach($deckData as $key => $value) {
        //   array_push($decksArr, $value); 
        // }
        $cardArr = [];
        
        // $parameter = $card->frontContent;
        // $backside = $card->backContent;
        // $fav = $card->fav;
        
        $value = $card[$parameter];
        $userId = $_SESSION['user_id'];
        $cardId = $card['cardId'];
        
        // array_push($cardArr, $frontside); 
        // array_push($cardArr, $backside); 
        // array_push($cardArr, $fav); 
        // array_push($cardArr, $difficulty); 
        array_push($cardArr, $value); 
        array_push($cardArr, $cardId); 
        array_push($cardArr, $userId); 

  
        
        return $cardArr;     
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    private function searchCards($allCardsArr, $searchTerm)
    {
      $results = ['frontContent'=>[], 'backContent'=>[]];
      // $results['frontContent']=$results['backContent']= array();
      array_walk($allCardsArr, function($cards, $deckName, $searchTerm) use (&$results){
        $resultFrontContentSearch = array_values(array_filter($cards, function($value) use ($searchTerm){

          return strpos($value['frontContent'], $searchTerm) !== false;
        }));
        
        $resultBackContentSearch = array_values(array_filter($cards, function($value) use ($searchTerm){
          
          return strpos($value['backContent'], $searchTerm) !== false;
        }));
        // if (!is_array($results['frontContent'][$deckName]))
        // {
        //   $results['frontContent'][$deckName]=array();
        // }
        // print_r(json_encode(sizeof($resultFrontContentSearch)));
        // exit;
        if (sizeof($resultFrontContentSearch) > 0)
        {
         
          $results['frontContent'][$deckName] = array_map(null, $resultFrontContentSearch);
          
        }
        // if (!is_array($results['backContent'][$deckName]))
        // {
        //   $results['backContent'][$deckName]=array();
        // }
        if (sizeof($resultBackContentSearch) > 0)
        {
          $results['backContent'][$deckName] = array_map(null, $resultBackContentSearch);

        }
        

       
      },$searchTerm);
            
            return $results;
            

    }

    public function calculateMastery($cards)
    {
      try {
        if (!is_array($cards))
        {
          throw new Exception("Invalid deck cards");         
        }
        $sumOfDiff = 0;
        
        foreach ($cards as $key => $card){
          
          if ($card->difficulty == 0)
          {
            $sumOfDiff += 3;
          }else
          {
            $sumOfDiff += (int)$card->difficulty;
          }
        }
        
        $mastery = floor((-(($sumOfDiff)/sizeof($cards)-5))*25);
        return $mastery;
      } catch (Exception $e) {
        throw new Exception($e->getMessage());
      }
    }

    public function addCardActiond($params, $data)
    {
      try{
        if(!isset($params['action']) || $params['action'] !== 'addCard'){
          throw new Exception('error: action not approved');
        }else{
          unset($params['action']);
        };
        $model = new CardModel;
        $deckModel = new DeckModel;
        if (!$model -> addcard($params, $data)){
          throw new Exception('could not add to Table. please try again');
        }
        else if(!$deckModel -> updateDeck($params, $data)){
          $model -> deleteCard($params);
          throw new Exception('could not add to Table. please try again');
        }
       

      }catch(Exception $e){

      }
    }

    public function addCardAction($params, $data)
    {
          
      $strErrorDesc = '';
      $requestMethod = $_SERVER["REQUEST_METHOD"];
      
      if (strtoupper($requestMethod) == 'POST') {
          try {
            // $deck = new DeckController;
            // $params = $this->getQueryStringParams();
            //check for correct action
            if (!isset($params['action']) || $params['action'] !== 'addCard') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);


            // $arrQueryStringParams['deckName'] = htmlspecialchars(strip_tags($arrQueryStringParams['deckName']));
            $deckModel = new DeckModel;
            //remove action from query parameters
            // unset($arrQueryStringParams['action']);
              
            
            if (!isset($params['deckName']) || empty($params['deckName'])) {
              throw new Exception("you must choose a deck");     
            }
            
            $params['user_id'] = $_SESSION['user_id'];
            
            
            $deckArr = $deckModel -> getDeck($params);
            
            $deckEl = $deckArr[0];
            $this -> cardValidate($data);
            
            //make the new deck array
            $cardEl = $this -> convertDataForCardsTbl($data, $deckEl);
            
            //make the  deck paramaters to update decks Table

            $deckParams = $this -> convertDataForDecksTbl($deckEl);
            
            // add to card table
            $result = $this -> addToCardTbl($cardEl);
            
            if (!$result){
              throw new Exception("Could not save to database");              
            }            
            // $userModel =/ new UserModel();
              
              $registerResult = $deckModel-> updateDeck($deckParams);
              
              
              if (!$registerResult || !$result)
              {
                throw new Exception("Error saving changes in DB ");
                
              }
              $responseData = json_encode("The deck was Successfully added.");
            
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
    

    public function updateCardsAction($params, $data)
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod == "POST")
        {
          try
          {
            if (!isset($params['action']) || $params['action'] !== 'updateCards') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);

            $deckName = $params['deckName'];
            $userId = $_SESSION['user_id'];
            $deckVars = array($deckName, $userId);
                        
            $deckModel = new DeckModel();
            
            $deckArr = $deckModel->getDeck($deckVars);
            if (!isset($deckArr[0]) || empty($deckArr[0]))
            {
              throw new Exception("could not find" . $deckName . "deck");
              
            }
            
            $deckEl = $deckArr[0];
            
            $newDeckData = $this->deckDataUpdateConverter($deckEl,$data);
            
            if(!$deckModel->updateDeckData($newDeckData))
            {
              throw new Exception("could not update" . $deckName . "deck");
            }
            
            $cardModel = new CardModel();
            
            foreach($data as $key=>$card)               
            {
              
              $newCardData = $this->cardDataUpdateConverter($card);
              
              if (!($cardModel -> updateCard($newCardData))){
                throw new Exception("could not find" . $deckName . "deck");                  
              }

            }

            
            
            $responseData = json_encode("The cards were updated");
             
               
          } catch (Error $e) {
            $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
          }
        }else {
          $strErrorDesc = 'Method not supported';
          $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      }  
         // $arrQueryStringParams = $this->getQueryStringParams();
        
        
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
        //     $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
        //         array('Content-Type: application/json', $strErrorHeader)
        //     );
        } 
      
    }

    public function updateCardAction($params, $data)
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod == "POST")
        {
          try
          {
            if (!isset($params['action']) || $params['action'] !== 'updateCard') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);
            $card = $data['card'];

            $cardId = htmlspecialchars(strip_tags($card['cardId']));
            $userId = $_SESSION['user_id'];
            $updatedParameter = htmlspecialchars(strip_tags($data['variable']));
            $value = htmlspecialchars(strip_tags($data['value']));
            // if ($card[$updatedParameter] !== $value || $userId !== $card['userId']){
            //   throw new Exception("Error in value");
              
            // }
            // $deckVars = array($deckName, $userId);
                        
            // $deckModel = new DeckModel();
            
            // $deckArr = $deckModel->getDeck($deckVars);
            // if (!isset($deckArr[0]) || empty($deckArr[0]))
            // {
            //   throw new Exception("could not find" . $deckName . "deck");
              
            // }
            
            // $deckEl = $deckArr[0];
            
            // $newDeckData = $this->deckDataUpdateConverter($deckEl,$data);
            
            // if(!$deckModel->updateDeckData($newDeckData))
            // {
            //   throw new Exception("could not update" . $deckName . "deck");
            // }
            
            $cardModel = new CardModel();
            
            // foreach($data as $key=>$card)               
            // {
              
              $newCardData = $this->parameterUpdateConverter($card, $updatedParameter);
              
              if (!($cardModel -> updateParameter($newCardData, $updatedParameter))){
                throw new Exception("could not find card");                  
              // }

            }

            
            
            $responseData = json_encode("The card was updated");
             
               
          } catch (Error $e) {
            $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
          }
        }else {
          $strErrorDesc = 'Method not supported';
          $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      }  
         // $arrQueryStringParams = $this->getQueryStringParams();
        
        
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
        //     $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
        //         array('Content-Type: application/json', $strErrorHeader)
        //     );
        } 
      
    }

    public function deleteCardAction($params, $data=[])
    {
      
      $strErrorDesc = '';
      
          try {
            
            //check for correct action
            if (!isset($params['action']) || $params['action'] !== 'deleteCard') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);
            if (!isset($params['deckName']) || empty($params['deckName'])) {
              throw new Exception("you must choose a deck");     
            }
            $deckName = $params['deckName'];
            $userId = $_SESSION['user_id'];
            $deckVars = array($deckName, $userId);
                        
            $deckModel = new DeckModel();

            $deckModel -> updateDeck($deckVars);
            
            $deckArr = $deckModel->getDeck($deckVars);
            
            if (!isset($deckArr[0]) || empty($deckArr[0]))
            {
              throw new Exception("could not find" . $deckName . "deck");
              
            }
            
            $deckEl = $deckArr[0];

            //remove deleted card from cards
            // $cards = array();
            // array_walk($data,function($card , $key, $cardId) use (&$cards){
            //   $var = $card->cardId;
              
            //   if ( $var !== $cardId){
            //     array_push($cards, $card);
                
            //   }
            // },$params['cardId']);

            // unset($data[$key]);
                       
            // $newDeckData = $this->deckDataUpdateConverter($deckEl,$cards);
            // $deckUpdateResult = $deckModel->updateDeckData($newDeckData);
            // if(!$deckUpdateResult)
            // {
            //   throw new Exception("could not update" . $deckName . "deck");
            // }
            
            // $arrQueryStringParams['deckName'] = htmlspecialchars(strip_tags($arrQueryStringParams['deckName']));
            // $deckModel = new DeckModel;
            //remove action from query parameters
            // unset($arrQueryStringParams['action']);
                      
            $cardModel = new CardModel;
            

            $delVars = $this -> cardDatadeleteConverter($deckEl, $params);
            
            // add to card table
            $result = $cardModel -> deleteCard($delVars);

            if (!$result){
              throw new Exception("Could not save to database");              
            }            
            // $userModel =/ new UserModel();
              
              // $registerResult = $deckModel-> updateDeck($deckParams);
              
              
              // if (!$deckUpdateResult || !$result)
              // {
              //   throw new Exception("Error saving changes in DB ");
                
              // }
              $responseData = json_encode("delete successfull");
            
          } catch (Exception $e) {
            
              $strErrorDesc = $e->getMessage().' Something went wrong! Please contact support.';
              $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
          }
          
      // } else {
      //     $strErrorDesc = 'Method not supported';
      //     $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      // }
      
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

    public function searchCardsAction($params)
    {
        
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        if ($requestMethod == "GET")
        {
          try
          {
            if (!isset($params['action']) || $params['action'] !== 'searchCards') {
                
              throw new Exception("Error Processing Request"); 
                  
            }
            unset($params['action']);
            
            if (!isset($params['searchTerm']) || !strlen($params['searchTerm'])) {
                
              throw new Exception("no input detected"); 
                  
            }


          $userModel = new UserModel;
          $cardModel = new CardModel();
          $userName = $_SESSION['userName'];
          $results = $userModel->getUser($userName);
          $decksArr = json_decode($results[0]['decks']);
          
          if (empty($decksArr )) 
          {
            throw new Exception("You did not add any cards yet"); 
          }
          
          $userId = $_SESSION['user_id'];
          $deckModel = new DeckModel();

          $allCardsArr = [];
          for ($i=0; $i < sizeof($decksArr); $i++) { 
            $deckName = $decksArr[$i];
            $deckVars = array($deckName, $userId);
            $deck = $deckModel->getDeck($deckVars);
            if (!isset($deck[0]) || empty($deck[0]))
            {
              throw new Exception("could not find" . $deckName . "deck");
              
            }
            $deckEl = $deck[0];

            $vars = array ($deckEl['deck_id']);
            $cardArr = $cardModel -> getcards($vars);
            
            if (!empty($cardArr))
            {
              foreach ($cardArr as $key => $card){
                unset($cardArr[$key]['id']);
                // unset($cardArr[$key]['cardId']);
                unset($cardArr[$key]['userId']);
                unset($cardArr[$key]['deckId']);
              }
            } 
            $allCardsArr[$deckName] = $cardArr;          
          }
          
            $response = $this->searchCards($allCardsArr, $params['searchTerm']);
            $responseData = json_encode($response);
             
               
          } catch (Error $e) {
            $strErrorDesc = $e->getMessage().'Something went wrong! Please contact support.';
            $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
          }
        }else {
          $strErrorDesc = 'Method not supported';
          $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
      }  
         // $arrQueryStringParams = $this->getQueryStringParams();
        
        
        // send output 
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
        //     $this->sendOutput(json_encode(array('error' => $strErrorDesc)), 
        //         array('Content-Type: application/json', $strErrorHeader)
        //     );
        } 
      
    
    }

    public function getCardsAction ($params){
      try{
        if (!isset($prams['action']) || $params['$action'] !== 'getCards'){
          throw new Exception("Error Processing Request. unknown action.", 1);          
        } else {
          unset($params['action']);
        }
        $params['user_id'] = $_SESSION['user_id'];
        $cardModel = new CardModel;
        $result = $cardModel -> getCards($params);
        $this->sendOutput(
          $result,
          array('Content-Type: application/json', 'HTTP/1.1 200 OK')
      );
      } catch(Exception $e){
        throw new Exception ('error : could not get the desired deck');
      }

    }
   
}
