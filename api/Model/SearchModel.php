<?php
require_once __DIR__ . "/Database.php";


class SearchModel extends Database
{

    //  Searches the decks by name or description

    public function searchDecks($params): array
    {
        try {

            return $this->select("SELECT * FROM Decks WHERE user_id = ? AND (deckName LIKE ? OR deckDescription LIKE ?)", $params); 
        } catch (PDOException $e) {
            error_log("Error in SearchModel::searchDecks: " . $e->getMessage());
            throw new Exception("Database error during deck search: " . $e->getMessage());
        }
    }

    public function searchCards($params): array
    {
        try {

            return $this->select("SELECT * FROM Content WHERE userId = ? AND (frontContent LIKE ? OR backContent LIKE ?)", $params);
        } catch (PDOException $e) {
            error_log("Error in SearchModel::searchCards: " . $e->getMessage());
            throw new Exception("Database error during card search: " . $e->getMessage());
        }
    }
}