<?php

/**
 * MyWisata Application - Hotel Controller
 *
 * Handles hotel browsing and booking.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */

// Load required models
require_once APP_ROOT . '/app/models/Hotel.php';

class HotelController extends Controller
{
    /**
     * Index - List all hotels
     */
    public function index()
    {
        $hotelModel = new Hotel();

        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'type' => $this->get('type'),
            'min_price' => $this->get('min_price'),
            'max_price' => $this->get('max_price'),
            'star_rating' => $this->get('star_rating'),
            'facility' => $this->get('facility'),
            'sort' => $this->get('sort', 'newest'),
            'is_approved' => 1,
        ];

        $hotels = $hotelModel->getAllWithFilters($filters);
        $types = $hotelModel->getAccommodationTypes();
        $allFacilities = $hotelModel->getAllFacilities();

        $data = [
            'title' => 'Penginapan - MyWisata',
            'hotels' => $hotels,
            'filters' => $filters,
            'types' => $types,
            'allFacilities' => $allFacilities,
        ];

        $this->view('hotels/index', $data);
    }

    /**
     * Detail - Show hotel details
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }
        $hotelModel = new Hotel();

        $hotel = $hotelModel->findById($id);

        if (!$hotel) {
            Session::flash('error', 'Hotel tidak ditemukan');
            $this->redirect('hotels');
        }

        $rooms = $hotelModel->getRooms($id);
        $reviews = $hotelModel->getReviews($id, 10);
        $images = $hotelModel->getImages($id);

        $facilityModel = new Facility();
        $facilities = $facilityModel->getEntityFacilitiesGrouped('hotel', $id);

        $videoModel = new VideoGallery();
        $videos = $videoModel->getVideos('hotel', $id);

        $data = [
            'title' => $hotel['name'] . ' - MyWisata',
            'hotel' => $hotel,
            'rooms' => $rooms,
            'reviews' => $reviews,
            'images' => $images,
            'facilities' => $facilities,
            'facilityModel' => $facilityModel,
            'videos' => $videos,
            'entityType' => 'hotel',
            'entityId' => $id,
        ];

        $this->view('hotels/detail', $data);
    }

    /**
     * Add review
     */
    public function addReview()
    {
        $userId = Session::get('user_id');

        if (!$userId) {
            $this->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $data = [
            'hotel_id' => $this->post('hotel_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment'),
        ];

        $validator = new Validator($_POST);
        $validator->required(['hotel_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);

        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }

        $reviewModel = new Review();
        $reviewModel->add([
            'user_id' => $userId,
            'reviewable_type' => 'hotel',
            'reviewable_id' => $data['hotel_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        Logger::audit('ADD_REVIEW', 'reviews', "Added review for hotel ID: {$data['hotel_id']}", [], $data);

        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
