<?php
/**
 * MyWisata Application - Hotel Controller
 * 
 * Handles hotel browsing and booking.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class HotelController extends Controller {
    
    /**
     * Index - List all hotels
     */
    public function index() {
        $hotelModel = $this->model('Hotel');
        
        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'type' => $this->get('type'),
            'has_prayer_room' => $this->get('has_prayer_room'),
            'is_alcohol_free' => $this->get('is_alcohol_free'),
            'has_women_only_facilities' => $this->get('has_women_only_facilities'),
            'is_approved' => 1
        ];
        
        $hotels = $hotelModel->getAllWithFilters($filters);
        
        $data = [
            'title' => 'Hotel & Homestay - MyWisata',
            'hotels' => $hotels,
            'filters' => $filters
        ];
        
        $this->view('hotels/index', $data);
    }
    
    /**
     * Detail - Show hotel details
     */
    public function detail() {
        $id = $this->get('id');
        $hotelModel = $this->model('Hotel');
        
        $hotel = $hotelModel->findById($id);
        
        if (!$hotel) {
            Session::flash('error', 'Hotel tidak ditemukan');
            $this->redirect('hotels');
        }
        
        $rooms = $hotelModel->getRooms($id);
        $reviews = $hotelModel->getReviews($id, 10);
        
        $data = [
            'title' => $hotel['name'] . ' - MyWisata',
            'hotel' => $hotel,
            'rooms' => $rooms,
            'reviews' => $reviews
        ];
        
        $this->view('hotels/detail', $data);
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
            'hotel_id' => $this->post('hotel_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment')
        ];
        
        $validator = new Validator($_POST);
        $validator->required(['hotel_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);
        
        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }
        
        $hotelModel = $this->model('Hotel');
        $hotelModel->addReview($data);
        $hotelModel->updateRating($data['hotel_id']);
        
        Logger::audit('ADD_HOTEL_REVIEW', 'hotel_reviews', "Added review for hotel ID: {$data['hotel_id']}", [], $data);
        
        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
