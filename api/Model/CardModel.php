<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";
class CardModel extends Database
{
    // public function getDecks($limit)
    // {
    //     return $this->select("SELECT * FROM Users ORDER BY user_id ASC LIMIT ?", ["i", $limit]);
    // }

    public function getDeck($vars)
    {    
        
        return $this->select("SELECT * FROM Decks WHERE deckName = ? AND user_id = ?", $vars);
    }

    public function getcards($vars)
    {
       
        return $this->select("SELECT * FROM Content WHERE deckId = ?", $vars);
    }

    public function getAllCards($vars)
    {
       
        return $this->select("SELECT * FROM Content WHERE userId = ?", $vars);
    }

    public function registerUser($userData)
    {       
        
        return $this->post("INSERT INTO Users (userName, pass, email, phone, user_id, adress) VALUES (? ,? ,? ,? ,? ,?)", $userData);
    
    }

    public function addCard($params)
    {
        // $params = "decks = '" . $data . "'";
        // $condition = "user_id = '" . $userId . "'";
        
       
        // array_push($data, $userId);
        
        return $this->post("INSERT INTO Content (cardId, deckId, userId, frontContent, backContent, deckName) VALUES (? ,? ,? ,? ,?, ?)", $params);
        
    }

    public function updatecard($vars)
    {
        
        return $this->update("UPDATE Content SET frontContent=?, backContent=?, fav=?, difficulty=?, correctCounter=? WHERE cardId=?", $vars);

    }

    public function updateParameter($vars, $parameter)
    {
        
        return $this->update("UPDATE Content SET $parameter=? WHERE cardId=? AND userId=?", $vars);

    }

    public function deleteCard($delVars)
    {
        
        return $this->delete("DELETE FROM Content WHERE cardId=? AND deckId=?", $delVars);

    }
}