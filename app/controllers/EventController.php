<?php

/**
 * MyWisata Application - Event Controller
 *
 * Handles event browsing and registration.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */

// Load required models
require_once APP_ROOT . '/app/models/Event.php';

class EventController extends Controller
{
    /**
     * Index - List all events
     */
    public function index()
    {
        $eventModel = new Event();

        $filters = [
            'city' => $this->get('city'),
            'search' => $this->get('search'),
            'registration_type' => $this->get('registration_type'),
            'is_active' => 1,
            'upcoming' => true,
        ];

        $events = $eventModel->getAllWithFilters($filters);
        $upcoming = $eventModel->getUpcoming(6);

        $data = [
            'title' => 'Event & Budaya - MyWisata',
            'events' => $events,
            'upcoming' => $upcoming,
            'filters' => $filters,
        ];

        $this->view('events/index', $data);
    }

    /**
     * Detail - Show event details
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }
        $eventModel = new Event();

        $event = $eventModel->findById($id);

        if (!$event) {
            Session::flash('error', 'Event tidak ditemukan');
            $this->redirect('events');
        }

        $reviews = $eventModel->getReviews($id, 10);

        $variantModel = new Variant();
        $variants = $variantModel->getVariants('event', $id);

        $facilityModel = new Facility();
        $facilities = $facilityModel->getEntityFacilitiesGrouped('event', $id);

        $videoModel = new VideoGallery();
        $videos = $videoModel->getVideos('event', $id);

        $data = [
            'title' => $event['title'] . ' - MyWisata',
            'event' => $event,
            'reviews' => $reviews,
            'variants' => $variants,
            'variantModel' => $variantModel,
            'facilities' => $facilities,
            'facilityModel' => $facilityModel,
            'videos' => $videos,
            'entityType' => 'event',
            'entityId' => $id,
        ];

        $this->view('events/detail', $data);
    }

    /**
     * Calendar - Show events in calendar view
     */
    public function calendar()
    {
        $eventModel = new Event();

        $month = (int)($this->get('month') ?? date('n'));
        $year = (int)($this->get('year') ?? date('Y'));
        $city = $this->get('city');

        if ($month < 1) { $month = 1; }
        if ($month > 12) { $month = 12; }

        $events = $eventModel->getEventsForCalendar($month, $year, $city);

        $cities = $this->db->query("SELECT DISTINCT location_name FROM events WHERE is_active = 1 AND location_name IS NOT NULL AND location_name != '' ORDER BY location_name")->fetchAll();

        $data = [
            'title' => 'Kalender Event - MyWisata',
            'events' => $events,
            'month' => $month,
            'year' => $year,
            'cities' => $cities,
            'cityFilter' => $city,
        ];

        $this->view('events/calendar', $data);
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
            'event_id' => $this->post('event_id'),
            'user_id' => $userId,
            'rating' => $this->post('rating'),
            'comment' => $this->post('comment'),
        ];

        $validator = new Validator($_POST);
        $validator->required(['event_id', 'rating', 'comment'])
                  ->numeric(['rating'])
                  ->in('rating', [1, 2, 3, 4, 5]);

        if ($validator->fails()) {
            $this->json(['status' => 'error', 'message' => $validator->firstError()], 400);
        }

        $reviewModel = new Review();
        $reviewModel->add([
            'user_id' => $userId,
            'reviewable_type' => 'event',
            'reviewable_id' => $data['event_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        Logger::audit('ADD_REVIEW', 'reviews', "Added review for event ID: {$data['event_id']}", [], $data);

        $this->json(['status' => 'success', 'message' => 'Review berhasil ditambahkan']);
    }
}
