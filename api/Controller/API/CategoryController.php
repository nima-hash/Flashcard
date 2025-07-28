<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';
require_once __DIR__ . "/../../Model/CategoriesModel.php"; 

class CategoryController extends BaseController {
    public $defaultCategories;

    public function __construct() 
    {
        $this->defaultCategories = [["id" =>  6, "name" => "Language"], ["id" => 7, "name" => "Law"], ["id" => 8, "name" => "Coocking"], ["id" => 9, "name" => "News"]];
    }

    // Get categories
    public function getAction() 
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $categoryModel = new CategoryModel();
            $categories = $categoryModel->getCategoriesByUserId($userId);
            
            $this->sendOutput(json_encode(['success' => true, 'categories' => ['custom' => $categories, 'default' => $this->defaultCategories]]), 200);

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error fetching categories: ' . $e->getMessage()]), 500);
        }
    }

    // Creates a new category.

    public function createAction($params, $requestBody)
    {
        try {
            $categoryName = $requestBody['name'] ?? null;
            if (empty($categoryName)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Category name is required.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $categoryModel = new CategoryModel();
            $result = $categoryModel->createCategory([$categoryName, $userId, true]);

            if ($result) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Category created successfully!']), 201); // 201 Created
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to create category.']), 500);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]), 500);
        }
    }

    //  Handles the POST /api/categories/edit endpoint.
    //  Updates an existing category.

    public function editAction($queryParams, $requestBody)
    {
        try {
            $categoryId = $requestBody['id'] ?? null;
            $categoryName = $requestBody['name'] ?? null;

            if (empty($categoryId) || empty($categoryName)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Category ID and name are required.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $categoryModel = new CategoryModel();
            $updated = $categoryModel->updateCategory($categoryId, $categoryName, $userId); // Assuming this method exists

            if ($updated) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Category updated successfully!']), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to update category or category not found/owned.']), 404);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]), 500);
        }
    }

    //  Handles the POST /api/categories/delete endpoint.
    //  Deletes a category.

    public function deleteAction($queryParams, $requestBody)
    {
        try {
            $categoryId = $requestBody['id'] ?? null;
            if (empty($categoryId)) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Category ID is required for deletion.']), 400);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'User not authenticated.']), 401);
                return;
            }

            $categoryModel = new CategoryModel();

            $deleted = $categoryModel->deleteCategory($categoryId, $userId);

            if ($deleted) {
                $this->sendOutput(json_encode(['success' => true, 'message' => 'Category deleted successfully!']), 200);
            } else {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Failed to delete category or category not found/owned.']), 404);
            }

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]), 500);
        }
    }
}

?>