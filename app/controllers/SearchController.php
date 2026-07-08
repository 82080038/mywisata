<?php

/**
 * MyWisata Application - Search Controller
 *
 * Handles advanced search with filters.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class SearchController extends Controller
{
    /**
     * Index - Show search page
     */
    public function index()
    {
        $query = $this->get('q', '');
        $type = $this->get('type', 'all');
        $page = (int) $this->get('page', 1);
        $perPage = 12;

        $filters = [
            'search' => $query,
            'city' => $this->get('city', ''),
            'min_price' => $this->get('min_price', ''),
            'max_price' => $this->get('max_price', ''),
            'min_rating' => $this->get('min_rating', ''),
            'category' => $this->get('category', ''),
            'date' => $this->get('date', ''),
        ];

        $results = $this->performSearch($type, $filters, $page, $perPage);

        $data = [
            'title' => 'Pencarian - MyWisata',
            'query' => $query,
            'type' => $type,
            'filters' => $filters,
            'results' => $results,
            'page' => $page,
        ];

        $this->view('search/index', $data);
    }

    /**
     * AJAX search
     */
    public function search()
    {
        if (!$this->isAjax()) {
            $this->redirect('search');
        }

        $query = $this->post('query', '');
        $type = $this->post('type', 'all');
        $page = (int) $this->post('page', 1);
        $perPage = (int) $this->post('per_page', 12);

        $filters = [
            'search' => $query,
            'city' => $this->post('city', ''),
            'min_price' => $this->post('min_price', ''),
            'max_price' => $this->post('max_price', ''),
            'min_rating' => $this->post('min_rating', ''),
            'category' => $this->post('category', ''),
            'date' => $this->post('date', ''),
        ];

        $results = $this->performSearch($type, $filters, $page, $perPage);

        $this->json([
            'status' => 'success',
            'results' => $results,
            'total' => count($results),
        ]);
    }

    /**
     * Perform search with filters
     *
     * @param string $type Search type
     * @param array $filters Filter parameters
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Search results
     */
    private function performSearch($type, $filters, $page, $perPage)
    {
        $results = [];

        switch ($type) {
            case 'destinations':
                $results = $this->searchDestinations($filters);
                break;
            case 'hotels':
                $results = $this->searchHotels($filters);
                break;
            case 'restaurants':
                $results = $this->searchRestaurants($filters);
                break;
            case 'events':
                $results = $this->searchEvents($filters);
                break;
            case 'tour_guides':
                $results = $this->searchTourGuides($filters);
                break;
            case 'all':
            default:
                $results = $this->searchAll($filters);
                break;
        }

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        return array_slice($results, $offset, $perPage);
    }

    /**
     * Search destinations
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchDestinations($filters)
    {
        $destinationModel = new Destination();
        $searchFilters = [];

        if (!empty($filters['search'])) {
            $searchFilters['search'] = $filters['search'];
        }
        if (!empty($filters['city'])) {
            $searchFilters['city'] = $filters['city'];
        }
        if (!empty($filters['is_active'])) {
            $searchFilters['is_active'] = $filters['is_active'];
        }
        if (!empty($filters['category'])) {
            $searchFilters['category_id'] = $filters['category'];
        }

        $destinations = $destinationModel->getAllWithFilters($searchFilters);

        // Apply additional filters
        $results = [];
        foreach ($destinations as $dest) {
            if ($this->matchesPriceFilter($dest['entry_fee'], $filters)) {
                if ($this->matchesRatingFilter($dest['rating_avg'], $filters)) {
                    $results[] = [
                        'type' => 'destination',
                        'id' => $dest['id'],
                        'name' => $dest['name'],
                        'description' => $dest['short_desc'] ?? substr($dest['description'], 0, 150),
                        'price' => $dest['entry_fee'],
                        'rating' => $dest['rating_avg'],
                        'city' => $dest['city'],
                        'image' => $dest['main_image'],
                        'url' => BASE_URL . 'destination/detail/' . $dest['id'],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Search hotels
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchHotels($filters)
    {
        $hotelModel = new Hotel();
        $searchFilters = [];

        if (!empty($filters['search'])) {
            $searchFilters['search'] = $filters['search'];
        }
        if (!empty($filters['city'])) {
            $searchFilters['city'] = $filters['city'];
        }
        if (!empty($filters['is_approved'])) {
            $searchFilters['is_approved'] = $filters['is_approved'];
        }

        $hotels = $hotelModel->getAllWithFilters($searchFilters);

        $results = [];
        foreach ($hotels as $hotel) {
            $rooms = $hotelModel->getRooms($hotel['id']);
            if (!empty($rooms)) {
                $minPrice = min(array_column($rooms, 'price'));
                
                if ($this->matchesPriceFilter($minPrice, $filters)) {
                    if ($this->matchesRatingFilter($hotel['rating_avg'], $filters)) {
                        $results[] = [
                            'type' => 'hotel',
                            'id' => $hotel['id'],
                            'name' => $hotel['name'],
                            'description' => substr($hotel['description'], 0, 150),
                            'price' => $minPrice,
                            'rating' => $hotel['rating_avg'],
                            'city' => $hotel['city'],
                            'image' => $hotel['main_image'],
                            'url' => BASE_URL . 'hotel/detail/' . $hotel['id'],
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Search restaurants
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchRestaurants($filters)
    {
        $restaurantModel = new Restaurant();
        $searchFilters = [];

        if (!empty($filters['search'])) {
            $searchFilters['search'] = $filters['search'];
        }
        if (!empty($filters['city'])) {
            $searchFilters['city'] = $filters['city'];
        }
        if (!empty($filters['is_approved'])) {
            $searchFilters['is_approved'] = $filters['is_approved'];
        }

        $restaurants = $restaurantModel->getAllWithFilters($searchFilters);

        $results = [];
        foreach ($restaurants as $restaurant) {
            if ($this->matchesRatingFilter($restaurant['rating_avg'], $filters)) {
                $results[] = [
                    'type' => 'restaurant',
                    'id' => $restaurant['id'],
                    'name' => $restaurant['name'],
                    'description' => substr($restaurant['description'], 0, 150),
                    'price' => 0,
                    'rating' => $restaurant['rating_avg'],
                    'city' => $restaurant['city'],
                    'image' => $restaurant['main_image'],
                    'url' => BASE_URL . 'restaurant/detail/' . $restaurant['id'],
                ];
            }
        }

        return $results;
    }

    /**
     * Search events
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchEvents($filters)
    {
        $eventModel = new Event();
        $searchFilters = [];

        if (!empty($filters['search'])) {
            $searchFilters['search'] = $filters['search'];
        }
        if (!empty($filters['city'])) {
            $searchFilters['city'] = $filters['city'];
        }
        if (!empty($filters['date'])) {
            $searchFilters['date'] = $filters['date'];
        }

        $events = $eventModel->getAllWithFilters($searchFilters);

        $results = [];
        foreach ($events as $event) {
            if ($this->matchesPriceFilter($event['price'], $filters)) {
                if ($this->matchesRatingFilter($event['rating_avg'], $filters)) {
                    $results[] = [
                        'type' => 'event',
                        'id' => $event['id'],
                        'name' => $event['title'],
                        'description' => substr($event['description'], 0, 150),
                        'price' => $event['price'],
                        'rating' => $event['rating_avg'],
                        'city' => $event['city'],
                        'image' => $event['main_image'],
                        'date' => $event['start_date'],
                        'url' => BASE_URL . 'event/detail/' . $event['id'],
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Search tour guides
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchTourGuides($filters)
    {
        $tourGuideModel = new TourGuide();
        $searchFilters = [];

        if (!empty($filters['city'])) {
            $searchFilters['city'] = $filters['city'];
        }
        if (!empty($filters['is_verified'])) {
            $searchFilters['is_verified'] = $filters['is_verified'];
        }
        if (!empty($filters['is_available'])) {
            $searchFilters['is_available'] = $filters['is_available'];
        }

        $guides = $tourGuideModel->getAll($searchFilters);

        $results = [];
        foreach ($guides as $guide) {
            if ($this->matchesRatingFilter($guide['rating_avg'], $filters)) {
                $results[] = [
                    'type' => 'tour_guide',
                    'id' => $guide['id'],
                    'name' => $guide['name'] ?? 'Tour Guide',
                    'description' => $guide['bio'] ?? '',
                    'price' => $guide['hourly_rate'],
                    'rating' => $guide['rating_avg'],
                    'city' => '',
                    'image' => $guide['avatar'],
                    'url' => BASE_URL . 'tourguide/profile',
                ];
            }
        }

        return $results;
    }

    /**
     * Search all types
     *
     * @param array $filters Filter parameters
     * @return array Results
     */
    private function searchAll($filters)
    {
        $results = [];

        // Search all types and merge results
        $results = array_merge(
            $this->searchDestinations($filters),
            $this->searchHotels($filters),
            $this->searchRestaurants($filters),
            $this->searchEvents($filters),
            $this->searchTourGuides($filters)
        );

        // Sort by rating (highest first)
        usort($results, function ($a, $b) {
            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
        });

        return $results;
    }

    /**
     * Check if item matches price filter
     *
     * @param float $price Item price
     * @param array $filters Filter parameters
     * @return bool
     */
    private function matchesPriceFilter($price, $filters)
    {
        if (!empty($filters['min_price']) && $price < $filters['min_price']) {
            return false;
        }
        if (!empty($filters['max_price']) && $price > $filters['max_price']) {
            return false;
        }
        return true;
    }

    /**
     * Check if item matches rating filter
     *
     * @param float $rating Item rating
     * @param array $filters Filter parameters
     * @return bool
     */
    private function matchesRatingFilter($rating, $filters)
    {
        if (!empty($filters['min_rating']) && $rating < $filters['min_rating']) {
            return false;
        }
        return true;
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function suggestions()
    {
        if (!$this->isAjax()) {
            $this->redirect('search');
        }

        $query = $this->get('q', '');

        if (strlen($query) < 2) {
            $this->json(['status' => 'success', 'suggestions' => []]);
        }

        $suggestions = [];

        // Get destination suggestions
        $destinationModel = new Destination();
        $destinations = $destinationModel->getAllWithFilters(['search' => $query, 'is_active' => 1]);
        foreach (array_slice($destinations, 0, 3) as $dest) {
            $suggestions[] = [
                'type' => 'destination',
                'name' => $dest['name'],
                'url' => BASE_URL . 'destination/detail/' . $dest['id'],
            ];
        }

        // Get hotel suggestions
        $hotelModel = new Hotel();
        $hotels = $hotelModel->getAllWithFilters(['search' => $query, 'is_approved' => 1]);
        foreach (array_slice($hotels, 0, 2) as $hotel) {
            $suggestions[] = [
                'type' => 'hotel',
                'name' => $hotel['name'],
                'url' => BASE_URL . 'hotel/detail/' . $hotel['id'],
            ];
        }

        $this->json([
            'status' => 'success',
            'suggestions' => $suggestions,
        ]);
    }
}
