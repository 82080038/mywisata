<?php
/**
 * MyWisata Application - Search Model
 * 
 * Handles advanced search functionality across all content types.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Search extends Model {
    
    /**
     * Search across all content types
     * 
     * @param string $query Search query
     * @param string $type Content type filter
     * @param array $filters Additional filters
     * @param int $page Page number
     * @param int $limit Items per page
     * @return array
     */
    public function search($query, $type = 'all', $filters = [], $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $results = [];
        
        if ($type === 'all' || $type === 'destinations') {
            $results['destinations'] = $this->searchDestinations($query, $filters, $limit);
        }
        
        if ($type === 'all' || $type === 'hotels') {
            $results['hotels'] = $this->searchHotels($query, $filters, $limit);
        }
        
        if ($type === 'all' || $type === 'restaurants') {
            $results['restaurants'] = $this->searchRestaurants($query, $filters, $limit);
        }
        
        if ($type === 'all' || $type === 'events') {
            $results['events'] = $this->searchEvents($query, $filters, $limit);
        }
        
        if ($type === 'all' || $type === 'tour_guides') {
            $results['tour_guides'] = $this->searchTourGuides($query, $filters, $limit);
        }
        
        return $results;
    }
    
    /**
     * Count total search results
     * 
     * @param string $query Search query
     * @param string $type Content type filter
     * @param array $filters Additional filters
     * @return int
     */
    public function countResults($query, $type = 'all', $filters = []) {
        $total = 0;
        
        if ($type === 'all' || $type === 'destinations') {
            $total += $this->countDestinations($query, $filters);
        }
        
        if ($type === 'all' || $type === 'hotels') {
            $total += $this->countHotels($query, $filters);
        }
        
        if ($type === 'all' || $type === 'restaurants') {
            $total += $this->countRestaurants($query, $filters);
        }
        
        if ($type === 'all' || $type === 'events') {
            $total += $this->countEvents($query, $filters);
        }
        
        if ($type === 'all' || $type === 'tour_guides') {
            $total += $this->countTourGuides($query, $filters);
        }
        
        return $total;
    }
    
    /**
     * Search destinations
     */
    private function searchDestinations($query, $filters, $limit) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(d.name LIKE :query OR d.description LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        if (!empty($filters['city'])) {
            $where[] = "d.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['category'])) {
            $where[] = "d.category_id = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['rating_min'])) {
            $where[] = "d.rating_avg >= :rating_min";
            $params['rating_min'] = $filters['rating_min'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT d.*, 'destination' as type,
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'destination' AND reviewable_id = d.id) as rating_avg
                FROM destinations d 
                WHERE {$whereClause}
                ORDER BY d.rating_avg DESC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search hotels
     */
    private function searchHotels($query, $filters, $limit) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(h.name LIKE :query OR h.description LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        if (!empty($filters['city'])) {
            $where[] = "h.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['price_min'])) {
            $where[] = "h.price_per_night >= :price_min";
            $params['price_min'] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $where[] = "h.price_per_night <= :price_max";
            $params['price_max'] = $filters['price_max'];
        }
        
        if (!empty($filters['rating_min'])) {
            $where[] = "h.rating_avg >= :rating_min";
            $params['rating_min'] = $filters['rating_min'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT h.*, 'hotel' as type,
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'hotel' AND reviewable_id = h.id) as rating_avg
                FROM hotels h 
                WHERE {$whereClause}
                ORDER BY h.rating_avg DESC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search restaurants
     */
    private function searchRestaurants($query, $filters, $limit) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(r.name LIKE :query OR r.description LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        if (!empty($filters['city'])) {
            $where[] = "r.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['rating_min'])) {
            $where[] = "r.rating_avg >= :rating_min";
            $params['rating_min'] = $filters['rating_min'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT r.*, 'restaurant' as type,
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'restaurant' AND reviewable_id = r.id) as rating_avg
                FROM restaurants r 
                WHERE {$whereClause}
                ORDER BY r.rating_avg DESC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search events
     */
    private function searchEvents($query, $filters, $limit) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(e.title LIKE :query OR e.description LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        if (!empty($filters['city'])) {
            $where[] = "e.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['rating_min'])) {
            $where[] = "e.rating_avg >= :rating_min";
            $params['rating_min'] = $filters['rating_min'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT e.*, 'event' as type,
                (SELECT AVG(rating) FROM reviews WHERE reviewable_type = 'event' AND reviewable_id = e.id) as rating_avg
                FROM events e 
                WHERE {$whereClause}
                ORDER BY e.start_date ASC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Search tour guides
     */
    private function searchTourGuides($query, $filters, $limit) {
        $where = ['tg.is_available = 1'];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(tg.name LIKE :query OR tg.bio LIKE :query)";
            $params['query'] = "%{$query}%";
        }
        
        if (!empty($filters['city'])) {
            $where[] = "tg.city LIKE :city";
            $params['city'] = "%{$filters['city']}%";
        }
        
        if (!empty($filters['rating_min'])) {
            $where[] = "tg.rating_avg >= :rating_min";
            $params['rating_min'] = $filters['rating_min'];
        }
        
        if (!empty($filters['price_min'])) {
            $where[] = "tg.hourly_rate >= :price_min";
            $params['price_min'] = $filters['price_min'];
        }
        
        if (!empty($filters['price_max'])) {
            $where[] = "tg.hourly_rate <= :price_max";
            $params['price_max'] = $filters['price_max'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT tg.*, 'tour_guide' as type
                FROM tour_guides tg 
                WHERE {$whereClause}
                ORDER BY tg.rating_avg DESC
                LIMIT :limit";
        
        $params['limit'] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }
    
    /**
     * Count methods for each type
     */
    private function countDestinations($query, $filters) {
        // Similar to search but with COUNT
        return 0; // Simplified for now
    }
    
    private function countHotels($query, $filters) {
        return 0;
    }
    
    private function countRestaurants($query, $filters) {
        return 0;
    }
    
    private function countEvents($query, $filters) {
        return 0;
    }
    
    private function countTourGuides($query, $filters) {
        return 0;
    }
    
    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions($query, $type = 'all', $limit = 10) {
        $suggestions = [];
        
        if ($type === 'all' || $type === 'destinations') {
            $sql = "SELECT name, 'destination' as type FROM destinations 
                    WHERE name LIKE :query LIMIT :limit";
            $results = $this->db->query($sql, ['query' => "%{$query}%", 'limit' => $limit])->fetchAll();
            $suggestions = array_merge($suggestions, $results);
        }
        
        if ($type === 'all' || $type === 'hotels') {
            $sql = "SELECT name, 'hotel' as type FROM hotels 
                    WHERE name LIKE :query LIMIT :limit";
            $results = $this->db->query($sql, ['query' => "%{$query}%", 'limit' => $limit])->fetchAll();
            $suggestions = array_merge($suggestions, $results);
        }
        
        if ($type === 'all' || $type === 'tour_guides') {
            $sql = "SELECT name, 'tour_guide' as type FROM tour_guides 
                    WHERE name LIKE :query LIMIT :limit";
            $results = $this->db->query($sql, ['query' => "%{$query}%", 'limit' => $limit])->fetchAll();
            $suggestions = array_merge($suggestions, $results);
        }
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Save search history
     */
    public function saveSearchHistory($userId, $query, $type, $resultCount) {
        $sql = "INSERT INTO search_history (user_id, query, search_type, result_count, created_at)
                VALUES (:user_id, :query, :search_type, :result_count, NOW())";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'query' => $query,
            'search_type' => $type,
            'result_count' => $resultCount
        ]);
    }
    
    /**
     * Get search history for user
     */
    public function getSearchHistory($userId, $limit = 10) {
        $sql = "SELECT * FROM search_history 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->query($sql, ['user_id' => $userId, 'limit' => $limit])->fetchAll();
    }
    
    /**
     * Clear search history for user
     */
    public function clearSearchHistory($userId) {
        $sql = "DELETE FROM search_history WHERE user_id = :user_id";
        return $this->db->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Save a search
     */
    public function saveSavedSearch($userId, $query, $type, $name = null) {
        $sql = "INSERT INTO saved_searches (user_id, query, search_type, name, created_at)
                VALUES (:user_id, :query, :search_type, :name, NOW())";
        
        return $this->db->query($sql, [
            'user_id' => $userId,
            'query' => $query,
            'search_type' => $type,
            'name' => $name ?: $query
        ]);
    }
    
    /**
     * Get saved searches for user
     */
    public function getSavedSearches($userId, $limit = 10) {
        $sql = "SELECT * FROM saved_searches 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->query($sql, ['user_id' => $userId, 'limit' => $limit])->fetchAll();
    }
    
    /**
     * Delete saved search
     */
    public function deleteSavedSearch($savedSearchId, $userId) {
        $sql = "DELETE FROM saved_searches 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, ['id' => $savedSearchId, 'user_id' => $userId]);
    }
    
    /**
     * Get popular searches
     */
    public function getPopularSearches($limit = 10) {
        $sql = "SELECT query, COUNT(*) as search_count 
                FROM search_history 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY query 
                ORDER BY search_count DESC 
                LIMIT :limit";
        
        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }
}
