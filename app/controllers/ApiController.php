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
        $destinationModel = new Destination();
        $destinations = $destinationModel->getAll(['is_active' => 1]);
        
        $this->json(['status' => 'success', 'data' => $destinations]);
    }
    
    /**
     * Get destination detail
     */
    public function getDestination() {
        $id = $this->get('id');
        $destinationModel = new Destination();
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
        $tourGuideModel = new TourGuide();
        $guides = $tourGuideModel->getAll(['is_verified' => 1, 'is_available' => 1]);
        
        $this->json(['status' => 'success', 'data' => $guides]);
    }
    
    /**
     * Get tour guide detail
     */
    public function getTourGuide() {
        $id = $this->get('id');
        $tourGuideModel = new TourGuide();
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
        $hotelModel = new Hotel();
        $hotels = $hotelModel->getAll(['is_approved' => 1]);
        
        $this->json(['status' => 'success', 'data' => $hotels]);
    }
    
    /**
     * Get restaurants
     */
    public function getRestaurants() {
        $restaurantModel = new Restaurant();
        $restaurants = $restaurantModel->getAll(['is_approved' => 1]);
        
        $this->json(['status' => 'success', 'data' => $restaurants]);
    }
    
    /**
     * Get events
     */
    public function getEvents() {
        $eventModel = new Event();
        $events = $eventModel->getAll(['is_approved' => 1, 'upcoming' => true]);
        
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
            $destinationModel = new Destination();
            $results['destinations'] = $destinationModel->getAll(['search' => $query, 'is_active' => 1]);
        }
        
        if ($type === 'all' || $type === 'guides') {
            $tourGuideModel = new TourGuide();
            $results['guides'] = $tourGuideModel->getAll(['is_verified' => 1]);
        }
        
        if ($type === 'all' || $type === 'hotels') {
            $hotelModel = new Hotel();
            $results['hotels'] = $hotelModel->getAll(['search' => $query, 'is_approved' => 1]);
        }
        
        if ($type === 'all' || $type === 'restaurants') {
            $restaurantModel = new Restaurant();
            $results['restaurants'] = $restaurantModel->getAll(['search' => $query, 'is_approved' => 1]);
        }
        
        if ($type === 'all' || $type === 'events') {
            $eventModel = new Event();
            $results['events'] = $eventModel->getAll(['search' => $query, 'is_approved' => 1]);
        }
        
        $this->json(['status' => 'success', 'data' => $results]);
    }
}
