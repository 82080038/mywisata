<?php

/**
 * MyWisata Application - Itinerary Controller
 *
 * AI-powered itinerary builder for trip planning.
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class ItineraryController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Middleware::requireAuth();
    }

    /**
     * Index - List user itineraries
     */
    public function index()
    {
        $userId = Session::get('user_id');
        $itineraryModel = new Itinerary();
        $itineraries = $itineraryModel->getByUserId($userId);

        $data = [
            'title' => 'Itinerary Saya - MyWisata',
            'itineraries' => $itineraries,
        ];

        $this->view('itinerary/index', $data);
    }

    /**
     * Builder - AI suggestion + manual builder
     */
    public function builder()
    {
        $db = Database::getInstance();

        // Get categories for filter
        $categories = $db->query("SELECT * FROM destination_categories WHERE is_active = 1 ORDER BY sort_order, name")->fetchAll();

        // Get cities
        $cities = $db->query("SELECT DISTINCT city FROM destinations WHERE is_active = 1 AND city IS NOT NULL AND city != '' ORDER BY city")->fetchAll();

        $data = [
            'title' => 'AI Itinerary Builder - MyWisata',
            'categories' => $categories,
            'cities' => $cities,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('itinerary/builder', $data);
    }

    /**
     * Generate - AJAX endpoint for AI suggestion
     */
    public function generate()
    {
        if (!$this->isAjax()) {
            $this->redirect('itinerary/builder');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $prefs = [
            'city' => $this->post('city'),
            'days' => (int)$this->post('days', 3),
            'budget' => (float)$this->post('budget', 5000000),
            'travelers' => (int)$this->post('travelers', 1),
            'interests' => $this->post('interests', []),
        ];

        if (!is_array($prefs['interests'])) {
            $prefs['interests'] = $prefs['interests'] ? [$prefs['interests']] : [];
        }

        $itineraryModel = new Itinerary();
        $suggestion = $itineraryModel->generateSuggestion($prefs);

        // Calculate totals
        $totalCost = 0;
        foreach ($suggestion as $day => $items) {
            foreach ($items as $item) {
                $totalCost += $item['estimated_cost'] ?? 0;
            }
        }

        $this->json([
            'status' => 'success',
            'suggestion' => $suggestion,
            'total_cost' => $totalCost,
            'days' => $prefs['days'],
        ]);
    }

    /**
     * Save - Save itinerary to database
     */
    public function save()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('itinerary/builder');
        }

        $userId = Session::get('user_id');
        $title = $this->post('title');
        $startDate = $this->post('start_date');
        $endDate = $this->post('end_date');
        $numDays = (int)$this->post('num_days', 1);
        $numTravelers = (int)$this->post('num_travelers', 1);
        $budgetMin = $this->post('budget_min');
        $budgetMax = $this->post('budget_max');
        $description = $this->post('description');

        if (!$title || !$startDate || !$endDate) {
            Session::flash('error', 'Judul, tanggal mulai, dan tanggal selesai wajib diisi');
            $this->redirect('itinerary/builder');
        }

        $itineraryModel = new Itinerary();
        $itineraryId = $itineraryModel->create([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'num_days' => $numDays,
            'num_travelers' => $numTravelers,
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'status' => 'draft',
        ]);

        // Save items from JSON
        $itemsJson = $this->post('items_json');
        if ($itemsJson) {
            $items = json_decode($itemsJson, true);
            if (is_array($items)) {
                $seq = 0;
                foreach ($items as $item) {
                    $itineraryModel->addItem([
                        'itinerary_id' => $itineraryId,
                        'day_number' => $item['day'] ?? 1,
                        'sequence' => $seq++,
                        'item_type' => $item['item_type'] ?? 'custom',
                        'item_id' => $item['item_id'] ?? null,
                        'item_name' => $item['item_name'] ?? '',
                        'start_time' => $item['start_time'] ?? null,
                        'end_time' => $item['end_time'] ?? null,
                        'location' => $item['location'] ?? null,
                        'estimated_cost' => $item['estimated_cost'] ?? 0,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }
        }

        // Update total cost
        $totalCost = $itineraryModel->calculateTotalCost($itineraryId);
        $itineraryModel->update($itineraryId, ['total_estimated_cost' => $totalCost]);

        Logger::audit('CREATE_ITINERARY', 'itineraries', "Created itinerary ID: {$itineraryId}");

        Session::flash('success', 'Itinerary berhasil disimpan');
        $this->redirect('itinerary/detail/' . $itineraryId);
    }

    /**
     * Detail - View itinerary
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }

        $itineraryModel = new Itinerary();
        $itinerary = $itineraryModel->findById($id);

        if (!$itinerary) {
            Session::flash('error', 'Itinerary tidak ditemukan');
            $this->redirect('itinerary');
        }

        // Check ownership
        if ($itinerary['user_id'] != Session::get('user_id') && Session::get('role') !== 'admin') {
            Session::flash('error', 'Anda tidak memiliki akses');
            $this->redirect('itinerary');
        }

        $data = [
            'title' => $itinerary['title'] . ' - Itinerary - MyWisata',
            'itinerary' => $itinerary,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('itinerary/detail', $data);
    }

    /**
     * Delete - Delete itinerary
     */
    public function delete()
    {
        if (!$this->isAjax()) {
            $this->redirect('itinerary');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $id = $this->post('id');
        $itineraryModel = new Itinerary();
        $itinerary = $itineraryModel->findById($id);

        if (!$itinerary) {
            $this->json(['status' => 'error', 'message' => 'Itinerary tidak ditemukan'], 404);
        }

        if ($itinerary['user_id'] != Session::get('user_id') && Session::get('role') !== 'admin') {
            $this->json(['status' => 'error', 'message' => 'Tidak memiliki akses'], 403);
        }

        $itineraryModel->delete($id);
        Logger::audit('DELETE_ITINERARY', 'itineraries', "Deleted itinerary ID: {$id}");

        $this->json(['status' => 'success', 'message' => 'Itinerary berhasil dihapus']);
    }

    /**
     * AddItem - AJAX add item to itinerary
     */
    public function addItem()
    {
        if (!$this->isAjax()) {
            $this->redirect('itinerary');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $itineraryId = $this->post('itinerary_id');
        $itineraryModel = new Itinerary();
        $itinerary = $itineraryModel->findById($itineraryId);

        if (!$itinerary) {
            $this->json(['status' => 'error', 'message' => 'Itinerary tidak ditemukan'], 404);
        }

        if ($itinerary['user_id'] != Session::get('user_id')) {
            $this->json(['status' => 'error', 'message' => 'Tidak memiliki akses'], 403);
        }

        // Get next sequence
        $items = $itineraryModel->getItems($itineraryId);
        $dayItems = array_filter($items, function ($i) {
            return $i['day_number'] == $this->post('day_number');
        });
        $nextSeq = count($dayItems);

        $itineraryModel->addItem([
            'itinerary_id' => $itineraryId,
            'day_number' => $this->post('day_number'),
            'sequence' => $nextSeq,
            'item_type' => $this->post('item_type'),
            'item_id' => $this->post('item_id') ?: null,
            'item_name' => $this->post('item_name'),
            'start_time' => $this->post('start_time') ?: null,
            'end_time' => $this->post('end_time') ?: null,
            'location' => $this->post('location') ?: null,
            'estimated_cost' => (float)$this->post('estimated_cost', 0),
            'notes' => $this->post('notes') ?: null,
        ]);

        // Update total cost
        $totalCost = $itineraryModel->calculateTotalCost($itineraryId);
        $itineraryModel->update($itineraryId, ['total_estimated_cost' => $totalCost]);

        $this->json(['status' => 'success', 'message' => 'Item berhasil ditambahkan']);
    }

    /**
     * RemoveItem - AJAX remove item
     */
    public function removeItem()
    {
        if (!$this->isAjax()) {
            $this->redirect('itinerary');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $itemId = $this->post('item_id');
        $itineraryModel = new Itinerary();
        $itineraryModel->deleteItem($itemId);

        $itineraryId = $this->post('itinerary_id');
        if ($itineraryId) {
            $totalCost = $itineraryModel->calculateTotalCost($itineraryId);
            $itineraryModel->update($itineraryId, ['total_estimated_cost' => $totalCost]);
        }

        $this->json(['status' => 'success', 'message' => 'Item berhasil dihapus']);
    }
}
