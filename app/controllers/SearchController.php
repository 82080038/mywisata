<?php
/**
 * MyWisata Application - Search Controller
 * 
 * Handles advanced search functionality across all content types.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class SearchController extends Controller {
    
    private $searchModel;
    
    public function __construct() {
        parent::__construct();
        $this->searchModel = $this->model('Search');
    }
    
    /**
     * Display search page
     */
    public function index() {
        $query = $this->get('q', '');
        $type = $this->get('type', 'all'); // all, destinations, hotels, restaurants, events, tour_guides
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        
        $filters = [
            'city' => $this->get('city'),
            'category' => $this->get('category'),
            'price_min' => $this->get('price_min'),
            'price_max' => $this->get('price_max'),
            'rating_min' => $this->get('rating_min'),
            'sort' => $this->get('sort', 'relevance')
        ];
        
        $results = $this->searchModel->search($query, $type, $filters, $page, $limit);
        $total = $this->searchModel->countResults($query, $type, $filters);
        
        // Save search history if user is logged in
        if (Session::get('user_id') && !empty($query)) {
            $this->searchModel->saveSearchHistory(Session::get('user_id'), $query, $type, count($results));
        }
        
        $data = [
            'title' => 'Pencarian - MyWisata',
            'query' => $query,
            'type' => $type,
            'results' => $results,
            'total' => $total,
            'filters' => $filters,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('search/index', $data);
    }
    
    /**
     * AJAX search for autocomplete
     */
    public function autocomplete() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $query = $this->get('q');
        $type = $this->get('type', 'all');
        $limit = $this->get('limit', 10);
        
        if (strlen($query) < 2) {
            $this->json(['status' => 'success', 'data' => ['suggestions' => []]]);
        }
        
        $suggestions = $this->searchModel->getSuggestions($query, $type, $limit);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'suggestions' => $suggestions,
                'query' => $query
            ]
        ]);
    }
    
    /**
     * API search endpoint for frontend autocomplete
     */
    public function apiSearch() {
        header('Content-Type: application/json');
        
        $query = $this->get('q', '');
        $limit = $this->get('limit', 8);
        
        if (strlen($query) < 2) {
            echo json_encode([
                'success' => true,
                'results' => []
            ]);
            exit;
        }
        
        try {
            $suggestions = $this->searchModel->getSuggestions($query, 'all', $limit);
            
            $results = [];
            foreach ($suggestions as $suggestion) {
                $results[] = [
                    'name' => $suggestion['name'],
                    'highlighted_name' => $this->highlightMatch($suggestion['name'], $query),
                    'type' => $suggestion['type'] ?? 'Destination',
                    'url' => $this->getUrlForType($suggestion['type'], $suggestion['id'])
                ];
            }
            
            echo json_encode([
                'success' => true,
                'results' => $results
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Highlight matching text in search results
     */
    private function highlightMatch($text, $query) {
        $pattern = preg_quote($query, '/');
        return preg_replace("/($pattern)/i", '<span class="match">$1</span>', $text);
    }
    
    /**
     * Get URL based on content type
     */
    private function getUrlForType($type, $id) {
        $baseUrl = BASE_URL;
        switch (strtolower($type)) {
            case 'destination':
                return $baseUrl . 'destinations/detail?id=' . $id;
            case 'hotel':
                return $baseUrl . 'hotels/detail?id=' . $id;
            case 'restaurant':
                return $baseUrl . 'restaurants/detail?id=' . $id;
            case 'tour_guide':
                return $baseUrl . 'tourguides/detail?id=' . $id;
            case 'event':
                return $baseUrl . 'events/detail?id=' . $id;
            default:
                return $baseUrl . 'destinations/detail?id=' . $id;
        }
    }
    
    /**
     * Get search history for current user
     */
    public function getHistory() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $limit = $this->get('limit', 10);
        
        $history = $this->searchModel->getSearchHistory($userId, $limit);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'history' => $history
            ]
        ]);
    }
    
    /**
     * Save search
     */
    public function saveSearch() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $query = $this->post('query');
        $type = $this->post('type', 'all');
        $name = $this->post('name');
        
        if (empty($query)) {
            $this->json(['status' => 'error', 'message' => 'Query tidak boleh kosong'], 400);
        }
        
        $saved = $this->searchModel->saveSavedSearch($userId, $query, $type, $name);
        
        if ($saved) {
            $this->json(['status' => 'success', 'message' => 'Pencarian berhasil disimpan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menyimpan pencarian'], 500);
        }
    }
    
    /**
     * Get saved searches
     */
    public function getSaved() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $limit = $this->get('limit', 10);
        
        $saved = $this->searchModel->getSavedSearches($userId, $limit);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'saved_searches' => $saved
            ]
        ]);
    }
    
    /**
     * Delete saved search
     */
    public function deleteSaved() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $savedSearchId = $this->post('saved_search_id');
        
        $deleted = $this->searchModel->deleteSavedSearch($savedSearchId, $userId);
        
        if ($deleted) {
            $this->json(['status' => 'success', 'message' => 'Pencarian tersimpan berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus pencarian tersimpan'], 500);
        }
    }
    
    /**
     * Clear search history
     */
    public function clearHistory() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $cleared = $this->searchModel->clearSearchHistory($userId);
        
        if ($cleared) {
            $this->json(['status' => 'success', 'message' => 'Riwayat pencarian berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus riwayat pencarian'], 500);
        }
    }
    
    /**
     * Get popular searches
     */
    public function getPopular() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $limit = $this->get('limit', 10);
        
        $popular = $this->searchModel->getPopularSearches($limit);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'popular_searches' => $popular
            ]
        ]);
    }
}
