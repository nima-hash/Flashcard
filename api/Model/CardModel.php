<?php
require_once __DIR__ . "/Database.php";
class CardModel extends Database
{

    public function getcards($vars)
    {
        // $params = array_values($vars);
        
        return $this->select("SELECT * FROM Content WHERE deckId = ? AND userId = ?", $vars);
    }

    public function createCard($params)
    {
        
        
        return $this->post("INSERT INTO Content (deckId, userId, frontContent, backContent) VALUES (? ,? ,? ,?)", $params);
        
    }

    public function updatecard($vars)
    {
        return $this->update("UPDATE Content SET frontContent=?, backContent=? WHERE id=? AND deckId=?", $vars);

    }

    public function updateParameter($vars, $parameter)
    {
        
        return $this->update("UPDATE Content SET $parameter=? WHERE cardId=? AND userId=?", $vars);

    }

    public function deleteCard($delVars)
    {
        
        return $this->delete("DELETE FROM Content WHERE id=? AND deckId=?", $delVars);

    }

    public function deleteCards($delVars)
    {
        
        return $this->delete("DELETE FROM Content WHERE deckId=? AND userId=?", $delVars);

    }

    public function updateCardRating(string $cardId, int $rating): bool
    {
        try {
            // logic:
            // - Increment correct_counter if rating is higher than 4
            // - Adjust difficulty based on rating
            // - Update last_reviewed_at and potentially next_review_at (for spaced repetition)


            $sql = "UPDATE Content SET ";
            $params = [];

            // Update correct_counter based on rating
            if ($rating >= 4) { 
                $sql .= "streak = streak + 1, ";
            } else {
                // Reset on low score
                $sql .= "streak = 0, "; 
            }

            $newDifficulty = max(1, min(5, (5 - $rating) + 1));
            $sql .= "difficulty = ? ";
            $params[] = $newDifficulty;

            

            $sql .= "WHERE id = ?";
            $params[] = $cardId;
            
            return $this->update($sql, $params) > 0;
            

        } catch (PDOException $e) {
            error_log("Error in CardModel::scoreCard: " . $e->getMessage());
            throw new Exception("Database error scoring card: " . $e->getMessage());
        }
    }
    
}