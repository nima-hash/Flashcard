<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';
require_once __DIR__ . "/../../Model/SearchModel.php"; 


class SearchController extends BaseController
{
    //  Handles the GET /api/search/get request.
    //  Searches for decks and cards based on the query.

    public function getAction($queryParams)
    {
        try {
            $query = $queryParams['query'] ?? '';

            // Convert 'true'/'false' strings to boolean
            $searchCards = isset($queryParams['searchCards']) && $queryParams['searchCards'] === 'true';

            if (empty($query) && $searchCards) {
                $this->sendOutput(json_encode(['success' => false, 'message' => 'Search query cannot be empty when searching cards.']), 400);
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId === null) {
                $this->sendOutput(json_encode(['success' => true, 'decks' => [], 'cards' => [], 'message' => 'Guest mode. No search results.']), 200);
                return;
            }

            $searchModel = new SearchModel();
            $results = [
                'decks' => [],
                'cards' => []
            ];

            $searchQuery = '%' . $query . '%';
            
            $results['decks'] = $searchModel->searchDecks([$userId, $searchQuery, $searchQuery]);

            if ($searchCards && !empty($query)) {
                $results['cards'] = $searchModel->searchCards([$userId, $searchQuery, $searchQuery]);
            }

            $this->sendOutput(json_encode(['success' => true, 'decks' => $results['decks'], 'cards' => $results['cards']]), 200);

        } catch (Exception $e) {
            $this->sendOutput(json_encode(['success' => false, 'message' => 'Search error: ' . $e->getMessage()]), 500);
        }
    }
}

?>