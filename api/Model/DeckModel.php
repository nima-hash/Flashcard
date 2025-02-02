<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";
class DeckModel extends Database
{
    // public function getDecks($limit)
    // {
    //     return $this->select("SELECT * FROM Users ORDER BY user_id ASC LIMIT ?", ["i", $limit]);
    // }

    public function getDeck($vars)
    {    
        return $this->select("SELECT * FROM Decks WHERE deckId = ? AND user_id = ?", $vars);
    }

    public function getDecks($user)
    {
        return $this->select("SELECT * FROM Users WHERE userName = ?", [$user]);
    }

    public function registerUser($userData)
    {       
        
        return $this->post("INSERT INTO Users (userName, pass, email, phone, user_id, adress) VALUES (? ,? ,? ,? ,? ,?)", $userData);
    
    }

    public function addDeck($params)
    {
        // $params = "decks = '" . $data . "'";
        // $condition = "user_id = '" . $userId . "'";
        
       
        // array_push($data, $userId);
        
        return $this->post("INSERT INTO Decks (deckName, deckCategory, deckDescription, user_id, deck_id) VALUES (? ,? ,? ,? ,?)", $params);
        
    }

    public function updateDeck($deckParams)
    {
        
        return $this->update("UPDATE Decks SET totalCards = ? WHERE deck_id = ? AND user_id = ?", $deckParams);

    }

    public function decrementCardsInDeck($deckParams)
    {
        
        return $this->update("UPDATE Decks SET totalCards = totalCards - 1 WHERE deckName = ? AND user_id = ?", $deckParams);

    }

    public function updateDeckData($deckParams)
    {
        
        return $this->update("UPDATE Decks SET mastery = ?, totalCards = ?, record = ?, rating = ? WHERE deck_id = ? AND user_id = ?", $deckParams);

    }
}