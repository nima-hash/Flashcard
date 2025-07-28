<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';

class CardController extends BaseController
{
    

    public function createAction($params, $requestBody) {

      if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405); // 405 Method Not Allowed
            return;
        }
        
        try {
            $deckId = $requestBody['deck_id'] ?? null;
            $cardFront = $requestBody['frontContent'] ?? null;
            $cardBack = $requestBody['backContent'] ?? null;
            
            // Validate required input fields
            if (empty($deckId) || empty($cardFront) || empty($cardBack)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck ID, card front, and card back are required.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $cardModel = new CardModel();
            $deckModel = new DeckModel();

            // --- Start Database Transaction ---
            $cardModel->startTransaction();

            // Create the new card
            $newCardId = $cardModel->createCard([$deckId, $userId, $cardFront, $cardBack]);
            
            if (!$newCardId) {
                throw new Exception("Failed to create the new card in the database.");
            }

            // Increment the totalCards count for the associated deck
            $deckUpdated = $deckModel->incrementTotalCards([$deckId]);

            if (!$deckUpdated) {
                // If the deck update fails, triggers rollback
                throw new Exception("Failed to update the total cards count for the deck. Deck might not exist or user doesn't own it.");
            }

            // --- Commit if both operations were successful ---
            $cardModel->commitTransaction();

            $this->sendOutput(
                json_encode(['success' => true, 'message' => 'Card added successfully!', 'cardId' => $newCardId]),
                201
            );

        } catch (Exception $e) {
            // --- Rollback Transaction on Error ---
            if (isset($cardModel)) {
                $cardModel->rollBackTransaction();
            }

            $this->sendOutput(
                json_encode(['success' => false, 'message' => 'Error creating card: ' . $e->getMessage()]),
                500
            );
      }
    }


    public function editAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $cardId = $requestBody['id'] ?? null;
            $deckId = $requestBody['deck_id'] ?? null;
            $frontContent = $requestBody['frontContent'] ?? null;
            $backContent = $requestBody['backContent'] ?? null;

            // Validate required input fields
            if (empty($cardId) || empty($deckId) || empty($frontContent) || empty($backContent)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Card ID, Deck ID, front content, and back content are required for update.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $cardModel = new CardModel();

            // Attempt to update the card
            $updated = $cardModel->updateCard([$frontContent, $backContent, $cardId, $deckId]);

            if (!$updated) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Card not found or you have not any changes to it.']), 404); // 404 Not Found or 403 Forbidden
                return;
            }

            $this->sendOutput(
                json_encode(['success' => true, 'message' => 'Card updated successfully!']),
                200 
            );

        } catch (Exception $e) {
            $this->sendOutput(
                json_encode(['success' => false, 'message' => 'Error updating card: ' . $e->getMessage()]),
                500 
            );
        }
    }

    public function deleteAction($queryParams, $requestBody)
    {

      if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405);
            return;
        }

        try {
            $cardId = $requestBody['id'] ?? null;
            $deckId = $requestBody['deck_id'] ?? null;
            // Validate required input
            if (empty($cardId)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Card ID is required for deletion.']), 400);
                return;
            }
            if (empty($deckId)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck ID is required for deletion.']), 400);
                return;
            }
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $cardModel = new CardModel();
            $deckModel = new DeckModel();

            // --- ensures atomicity ---
            $cardModel->startTransaction();

            // Delete the card and get its deck_id
            $result = $cardModel->deleteCard([$cardId, $deckId]);

            if (!$result) {
                throw new Exception("Card not found or you do not have permission to delete it.");
            }

            // Decrement the totalCards count for the associated deck
            $deckUpdated = $deckModel->decrementTotalCards([$deckId]);

            if (!$deckUpdated) {
                // If the deck update fails, triggers rollback
                throw new Exception("Failed to update the total cards count for the deck.");
            }

            // Commit if both operations were successful
            $cardModel->commitTransaction(); 

            $this->sendOutput(
                json_encode(['success' => true, 'message' => 'Card deleted successfully!']),
                200 
            );

        } catch (Exception $e) {
            // --- Rollback Transaction on Error ---
            if (isset($cardModel)) {
                $cardModel->rollBackTransaction();
            }
            $this->sendOutput(
                json_encode(['success' => false, 'message' => 'Error deleting card: ' . $e->getMessage()]),
                500 
            );
        }
    }

    public function getAction ($params){

      if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'GET') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405); 
            return;
        }

      try{
       
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
            return;
        }

        $deckId = $params['deckId'] ?? null;
        if ($deckId === null) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Did not recieve the Deck identifier.']), 401);
            return;
        }
        
        
        $cardModel = new CardModel;
        $result = $cardModel -> getCards([$deckId, $userId]);
        
        // Ensures $cards is always an array, even if no cards are found
            if (empty($result)) {
                $result = [];
            }

        if (is_array($result)) {
            $this->sendOutput(
              json_encode(['success' => true, 'cards' => $result]),
              200,                  
              ['Content-Type: application/json']
            );
            return;
        }


      } catch(Exception $e){
        throw new Exception ('error : could not get the desired deck');
      }

    }
   
    public function rateAction($queryParams, $requestBody)
    {
        if (strtoupper($_SERVER["REQUEST_METHOD"]) !== 'POST') {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Method not supported.']), 405, array('HTTP/1.1 405 Method Not Allowed'));
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401, array('HTTP/1.1 401 Unauthorized'));
                return;
            }

            $cardId = $requestBody['card_id'] ?? null;
            $rating = $requestBody['rating'] ?? null;

            // Basic validation
            if (empty($cardId) || !is_numeric($rating) || $rating < 1 || $rating > 5) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Invalid card ID or rating. Rating must be an integer between 1 and 5.']), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            
            $cardModel = new CardModel();

            $cardId = $cardModel->updateCardRating($cardId, $rating); 
            if (!$cardId) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Card not found or you do not have permission to score this card.']), 403, array('HTTP/1.1 403 Forbidden'));
                return;
            } else {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Card score saved successfully!']), 200, array('HTTP/1.1 200 OK'));

            }

        } catch (Exception $e) {
            error_log("Error in StudyController::scoreAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error saving card score: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }
}