<?php
/**
 * MyWisata Application - Restaurant Controller
 * 
 * Handles restaurant browsing and ordering.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class RestaurantController extends Controller {
    
    /**
     * Index - List all restaurants
     */
    public function index() {
        $restaurantModel = $this->model('Restaurant');
        
        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'type' => $this->get('type'),
            'is_halal' => $this->get('is_halal'),
            'is_kosher' => $this->get('is_kosher'),
            'is_vegan_friendly' => $this->get('is_vegan_friendly'),
            'is_vegetarian_friendly' => $this->get('is_vegetarian_friendly'),
            'is_gluten_free_friendly' => $this->get('is_gluten_free_friendly'),
            'has_prayer_space' => $this->get('has_prayer_space'),
            'is_alcohol_free' => $this->get('is_alcohol_free'),
            'is_approved' => 1
        ];
        
        $restaurants = $restaurantModel->getAllWithFilters($filters);
        
        $data = [
            'title' => 'Restoran & UMKM - MyWisata',
            'restaurants' => $restaurants,
            'filters' => $filters
        ];
        
        $this->view('restaurants/index', $data);
    }
    
    /**
     * Detail - Show restaurant details
     */
    public function detail() {
        $id = $this->get('id');
        $restaurantModel = $this->model('Restaurant');
        
        $restaurant = $restaurantModel->findById($id);
        
        if (!$restaurant) {
            Session::flash('error', 'Restoran tidak ditemukan');
            $this->redirect('restaurants');
        }
        
        $menuItems = $restaurantModel->getMenuItems($id);
        $reviews = $restaurantModel->getReviews($id, 10);
        
        $data = [
            'title' => $restaurant['name'] . ' - MyWisata',
            'restaurant' => $restaurant,
            'menu_items' => $menuItems,
            'reviews' => $reviews
        ];
        
        $this->view('restaurants/detail', $data);
    }
    
    /**
     * Add review
     */
    public function addReview() {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            $this->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu'], 401);
        }
        
        $data = [
            'restaurant_id' => $this->post('restaurant_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['restaurant_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);
        
        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }
        
        $restaurantModel = $this->model('Restaurant');
        $restaurantModel->addReview($data);
        $restaurantModel->updateRating($data['restaurant_id']);
        
        Logger::audit('ADD_RESTAURANT_REVIEW', 'restaurant_reviews', "Added review for restaurant ID: {$data['restaurant_id']}", [], $data);
        
        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
