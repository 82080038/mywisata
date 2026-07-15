<?php
/**
 * MyWisata Application - API Controller
 * 
 * Handles mobile API endpoints.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class ApiController extends Controller {
    
    /**
     * Get destinations
     */
    public function getDestinations() {
        $destinationModel = $this->model('Destination');
        $destinations = $destinationModel->getAllWithFilters(['is_active' => 1]);
        
        $this->json(['status' => 'success', 'data' => $destinations]);
    }
    
    /**
     * Get destination detail
     */
    public function getDestination() {
        $id = $this->get('id');
        $destinationModel = $this->model('Destination');
        $destination = $destinationModel->findById($id);
        
        if (!$destination) {
            $this->json(['status' => 'error', 'message' => 'Destination not found'], 404);
        }
        
        $images = $destinationModel->getImages($id);
        $reviews = $destinationModel->getReviews($id, 5);
        
        $this->json(['status' => 'success', 'data' => array_merge($destination, ['images' => $images, 'reviews' => $reviews])]);
    }
    
    /**
     * Get tour guides
     */
    public function getTourGuides() {
        $tourGuideModel = $this->model('TourGuide');
        $guides = $tourGuideModel->getAllWithFilters(['is_verified' => 1, 'is_available' => 1]);
        
        $this->json(['status' => 'success', 'data' => $guides]);
    }
    
    /**
     * Get tour guide detail
     */
    public function getTourGuide() {
        $id = $this->get('id');
        $tourGuideModel = $this->model('TourGuide');
        $guide = $tourGuideModel->findById($id);
        
        if (!$guide) {
            $this->json(['status' => 'error', 'message' => 'Tour guide not found'], 404);
        }
        
        $languages = $tourGuideModel->getLanguages($id);
        $specializations = $tourGuideModel->getSpecializations($id);
        
        $this->json(['status' => 'success', 'data' => array_merge($guide, ['languages' => $languages, 'specializations' => $specializations])]);
    }
    
    /**
     * Get hotels
     */
    public function getHotels() {
        $hotelModel = $this->model('Hotel');
        $hotels = $hotelModel->getAllWithFilters(['is_approved' => 1]);
        
        $this->json(['status' => 'success', 'data' => $hotels]);
    }
    
    /**
     * Get restaurants
     */
    public function getRestaurants() {
        $restaurantModel = $this->model('Restaurant');
        $restaurants = $restaurantModel->getAllWithFilters(['is_approved' => 1]);
        
        $this->json(['status' => 'success', 'data' => $restaurants]);
    }
    
    /**
     * Get events
     */
    public function getEvents() {
        $eventModel = $this->model('Event');
        $events = $eventModel->getAllWithFilters(['is_approved' => 1, 'upcoming' => true]);
        
        $this->json(['status' => 'success', 'data' => $events]);
    }
    
    /**
     * Search
     */
    public function search() {
        $query = $this->get('q');
        $type = $this->get('type', 'all');
        
        $results = [];
        
        if ($type === 'all' || $type === 'destinations') {
            $destinationModel = $this->model('Destination');
            $results['destinations'] = $destinationModel->getAllWithFilters(['search' => $query, 'is_active' => 1]);
        }
        
        if ($type === 'all' || $type === 'guides') {
            $tourGuideModel = $this->model('TourGuide');
            $results['guides'] = $tourGuideModel->getAllWithFilters(['is_verified' => 1]);
        }
        
        if ($type === 'all' || $type === 'hotels') {
            $hotelModel = $this->model('Hotel');
            $results['hotels'] = $hotelModel->getAllWithFilters(['search' => $query, 'is_approved' => 1]);
        }
        
        if ($type === 'all' || $type === 'restaurants') {
            $restaurantModel = $this->model('Restaurant');
            $results['restaurants'] = $restaurantModel->getAllWithFilters(['search' => $query, 'is_approved' => 1]);
        }
        
        if ($type === 'all' || $type === 'events') {
            $eventModel = $this->model('Event');
            $results['events'] = $eventModel->getAllWithFilters(['search' => $query, 'is_approved' => 1]);
        }
        
        $this->json(['status' => 'success', 'data' => $results]);
    }
}
