<?php
require_once __DIR__ . "/Database.php";

class CategoryModel extends Database {
    public function getCategoriesByUserId(string $userId): array{
        try {
            return $this->select("SELECT * FROM categories WHERE user_id = ? ORDER BY name ASC", [$userId]);
        } catch (PDOException $e) {
            error_log("Error in CategoryModel::getCategoriesByUserId: " . $e->getMessage());
            throw new Exception("Database error fetching categories: " . $e->getMessage());
        }
    }

    
    public function createCategory($var)
    {
        try {
            return $this->post("INSERT INTO categories (name, user_id, custom) VALUES (?, ?, ?)", $var);
        } catch (PDOException $e) {
            error_log("Error in CategoryModel::createCategory: " . $e->getMessage());
            throw new Exception("Database error creating category: " . $e->getMessage());
        }
    }

    //  Fetches a single category by its ID and user ID.

    public function getCategoryById($params): ?array
    {
        try {
            return $this->select("SELECT * WHERE id = ? AND user_id = ?", $params);
        } catch (PDOException $e) {
            error_log("Error in CategoryModel::getCategoryById: " . $e->getMessage());
            throw new Exception("Database error fetching category by ID: " . $e->getMessage());
        }
    }

    
     //Updates the name of an existing category for a specific user.

    public function updateCategory(int $categoryId, string $newName, string $userId)
    {
        try {
            // Assumes BaseModel has an 'update' method that returns true if rows were affected
            return $this->update("UPDATE Categories SET name = ? WHERE id = ? AND user_id = ?", [$newName, $categoryId, $userId]);
        } catch (PDOException $e) {
            error_log("Error in CategoryModel::updateCategory: " . $e->getMessage());
            throw new Exception("Database error updating category: " . $e->getMessage());
        }
    }

     //Deletes a category for a specific user.

    public function deleteCategory(int $categoryId, string $userId)
    {
        try {
            
            return $this->delete("DELETE FROM categories WHERE id = ? AND user_id = ?", [$categoryId, $userId]);
        } catch (PDOException $e) {
            error_log("Error in CategoryModel::deleteCategory: " . $e->getMessage());
            throw new Exception("Database error deleting category: " . $e->getMessage());
        }
    }
}