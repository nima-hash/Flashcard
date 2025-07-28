<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';
require_once __DIR__ . "/../../Model/DeckModel.php"; 
require_once __DIR__ . "/../../Model/CategoriesModel.php"; 


class DeckController extends BaseController
{
    // Handles the api/decks/create request
    // Creates a new deck

    public function createAction($queryParams, $requestBody) 
    {
        try {
            if (empty($requestBody['name']) || empty($requestBody['category_id'])) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck name and category are required.']), 400);
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $deckModel = new DeckModel();
            $newDeckId = $deckModel->createDeck([$requestBody['name'], $requestBody['category_id'], $requestBody['description'], $userId]); 
            
            if ($newDeckId) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Deck created successfully!']), 201);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to create deck.']), 500);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error creating deck: ' . $e->getMessage()]), 500);
        }
    }
    
    // Handles the api/decks/updateRecord request
    // Updates the best record on a deck

    public function updateRecordAction($queryParams, $requestBody) 
    {
        try {
            if (empty($requestBody['record']) || empty($requestBody['deck_id'])) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck name and category are required.']), 400);
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }
            
            $deckModel = new DeckModel();
            $result = $deckModel->updateDeckRecord([$requestBody['record'], $requestBody['deck_id'], $userId]);
            
            if ($result) {

                $this->sendOutput(json_encode(['success' => true, 'message' => 'Record updated successfully!']), 201);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to update Record.']), 500);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error updating deck: ' . $e->getMessage()]), 500);
        }
    }

    // Handles the api/decks/get request
    // Returns all list of decks of a user 

    public function getAction($queryParams)
    {
        try {
            $deckModel = new DeckModel();

            if (isset($queryParams['deckId']) && !empty($queryParams['deckId'])) {
              
              $deckId = $queryParams['deckId'];
                
                $userId = $_SESSION['user_id'] ?? null; 
                if ($userId === null) {
                    $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                    return;
                }

                $deckData = $deckModel->getDeckById([$deckId, $userId]);
                if ($deckData) {
                    $this->sendOutput(json_encode(['success' => true, 'deck' => $deckData]), 200);
                } else {
                    $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck not found or access denied.']), 404);
                }
                return;

            } else {

                $userId = $_SESSION['user_id'] ?? null;
                if ($userId === null) {
                    $this->sendOutput(json_encode(['success' => true, 'decks' => [], 'message' => 'Guest mode. No decks available.']), 200);
                    return;
                }

                $decks = $deckModel->getAllDecksForUser($userId); 
                $this->sendOutput(json_encode(['success' => true, 'decks' => $decks]), 200);
                return;
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to retrieve decks: ' . $e->getMessage()]), 500);
        }
    }

    // Handles the api/decks/delete request
    // Deletes a deck and its cards

   public function deleteAction($queryParams) 
   {
        try {
            $deckId = $queryParams['deckId'] ?? null;
            if (!$deckId) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck ID is required for deletion.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $deckModel = new DeckModel();
            $cardModel = new CardModel();
            $cardsDeleted = $cardModel -> deleteCards([$deckId, $userId]);

            $deckModel->startTransaction();

            if (!$cardsDeleted) {
                throw new Exception("Failed to delete all associated cards for the deck.");
            }
            
            $deckDeleted = $deckModel->deleteDeck([$deckId]); 
            if (!$deckDeleted) {
                throw new Exception("Failed to delete the deck itself (might not exist or user doesn't own it).");
            }

            $deckModel->commitTransaction();

            if ($deckDeleted && $cardsDeleted) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Deck deleted successfully!']), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to delete deck or deck not found/owned.']), 404);
            }
        } catch (Exception $e) {
            if (isset($deckModel)) { 
                $deckModel->rollBackTransaction(); 
            }
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error deleting deck: ' . $e->getMessage()]), 500);
        }
    }

    // Handles the api/decks/edit request
    // Updates the properties of a deck 
    public function editAction($queryParams, $requestBody)
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

            $deckId = $requestBody['id'] ?? null;
            $deckName = $requestBody['name'] ?? null;
            $categoryId = $requestBody['category_id'] ?? null;
            $deckDescription = $requestBody['description'] ?? null;

            $errors = [];
            if (empty($deckId)) {
                $errors['id'] = 'Deck ID is required for editing.';
            }
            if (empty($deckName)) {
                $errors['name'] = 'Deck name is required.';
            }
            if (empty($categoryId)) {
                $errors['category_id'] = 'Category is required.';
            }

            // Validate category_id exists
            $categoryModel = new CategoryModel();
            if (!$categoryModel->getCategoryById([$categoryId, $userId])) {
                $errors['category_id'] = 'Invalid category selected.';
            }

            if (!empty($errors)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]), 400, array('HTTP/1.1 400 Bad Request'));
                return;
            }

            $deckModel = new DeckModel();

            // Verify deck ownership
            $existingDeck = $deckModel->getDeckById($deckId);
            if (!$existingDeck || $existingDeck['user_id'] !== $userId) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Deck not found or you do not have permission to edit this deck.']), 403, array('HTTP/1.1 403 Forbidden'));
                return;
            }

            $updated = $deckModel->updateDeck($deckId, $deckName, $categoryId, $deckDescription);

            if ($updated) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Deck updated successfully!']), 200, array('HTTP/1.1 200 OK'));
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to update deck or no changes were made.']), 400, array('HTTP/1.1 400 Bad Request'));
            }

        } catch (Exception $e) {
            error_log("Error in DeckController::editAction: " . $e->getMessage());
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Server error updating deck: ' . $e->getMessage()]), 500, array('HTTP/1.1 500 Internal Server Error'));
        }
    }
    


  
}