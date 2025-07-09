<?php
require_once PROJECT_ROOT_PATH . "/Model/Database.php";
class UserModel extends Database
{
    public function getUsers($limit)
    {
        return $this->select("SELECT * FROM Users ORDER BY user_id ASC LIMIT ?", ["i", $limit]);
    }

    public function getUser($userName)
    {
        return $this->select("SELECT * FROM Users WHERE userName = ?", [$userName]);
    }

    public function getDecks($user)
    {
        return $this->select("SELECT * FROM Users WHERE userName = '" . $user . "'");
    }

    public function registerUser($userData)
    {       
        
        return $this->post("INSERT INTO Users (userName, pass, email, phone, user_id, adress) VALUES (? ,? ,? ,? ,? ,?)", $userData);
    
    }

    public function addDeck($data , $userId)
    {
        // $params = "decks = '" . $data . "'";
        // $condition = "user_id = '" . $userId . "'";
        
        $params =  array(
            'deckArr' => $data,
            'user_id' => $userId
        );
        // array_push($data, $userId);
        
        return $this->update("UPDATE  Users SET decks = ? WHERE user_id = ?", $params);
    }
}