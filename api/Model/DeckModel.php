<?php
require_once __DIR__ . "/Database.php";
class DeckModel extends Database
{

    public function getDeckById($vars)
    {    
        return $this->select("SELECT * FROM Decks WHERE id = ? AND user_id = ?", $vars);
    }

    public function getAllDecksForUser($userId)
    {
        return $this->select("SELECT * FROM Decks WHERE user_id = ?", [$userId]);
    }

    public function registerUser($userData)
    {       
        
        return $this->post("INSERT INTO Users (userName, pass, email, phone, user_id, adress) VALUES (? ,? ,? ,? ,? ,?)", $userData);
    
    }

    public function createDeck($params)
    {
    
        return $this->post("INSERT INTO Decks (deckName, category_id, deckDescription, user_id) VALUES (? ,? ,? ,?)", $params);
        
    }

    public function updateDeck($deckParams)
    {
        
        return $this->update("UPDATE Decks SET totalCards = ? WHERE id = ? AND user_id = ?", $deckParams);

    }

    public function updateDeckRecord($deckParams)
    {
        
        return $this->update("UPDATE Decks SET record = ? WHERE id = ? AND user_id = ?", $deckParams);

    }

    public function decrementCardsInDeck($deckParams)
    {
        
        return $this->update("UPDATE Decks SET totalCards = totalCards - 1 WHERE deckName = ? AND user_id = ?", $deckParams);

    }

    public function updateDeckData($deckParams)
    {
        
        return $this->update("UPDATE Decks SET mastery = ?, totalCards = ?, record = ?, rating = ? WHERE id = ? AND user_id = ?", $deckParams);

    }

    public function deleteDeck ($deckId) {
        return $this->delete("DELETE FROM Decks WHERE id=?", $deckId);
    }

    public function getDeckId($params) {
        return $this->select("SELECT * FROM Decks WHERE id = ?", [$params['deckId']]);
    }

    public function incrementTotalCards($params) {
        try {

            return $this->update("UPDATE Decks SET totalCards = totalCards + 1 WHERE id = ?", $params);

        } catch (PDOException $e) {
            error_log("Error in DeckModel::incrementTotalCards: " . $e->getMessage());
            throw new Exception("Database error incrementing total cards: " . $e->getMessage());
        }
    }

    public function decrementTotalCards($params)
    {
        try {
           
            return $this->update("UPDATE Decks SET totalCards = totalCards - 1 WHERE id = ?", $params);
        } catch (PDOException $e) {
            error_log("Error in DeckModel::decrementTotalCards: " . $e->getMessage());
            throw new Exception("Database error decrementing total cards: " . $e->getMessage());
        }
    }
    
}