<?php
/**
 * MyWisata Application - Itinerary Controller
 * 
 * Handles itinerary builder functionality.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class ItineraryController extends Controller {
    
    private $itineraryModel;
    
    public function __construct() {
        parent::__construct();
        $this->itineraryModel = $this->model('Itinerary');
    }
    
    /**
     * Index - List user itineraries
     */
    public function index() {
        Middleware::requireAuth();
        
        $userId = Session::get('user_id');
        $itineraries = $this->itineraryModel->getByUserId($userId);
        
        $data = [
            'title' => 'Itinerary Saya - MyWisata',
            'itineraries' => $itineraries,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('itinerary/index', $data);
    }
    
    /**
     * Create - Show itinerary builder
     */
    public function create() {
        Middleware::requireAuth();
        
        $data = [
            'title' => 'Buat Itinerary - MyWisata',
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('itinerary/create', $data);
    }
    
    /**
     * Store - Create new itinerary
     */
    public function store() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        
        $data = [
            'user_id' => $userId,
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'start_date' => $this->post('start_date'),
            'end_date' => $this->post('end_date'),
            'budget' => $this->post('budget'),
            'participants' => $this->post('participants', 1),
            'is_public' => $this->post('is_public', 0)
        ];
        
        // Validate input
        if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
        }
        
        $itineraryId = $this->itineraryModel->create($data);
        
        if ($itineraryId) {
            Logger::audit('CREATE_ITINERARY', 'itineraries', "Created itinerary ID: {$itineraryId}", [], $data);
            
            $this->json([
                'status' => 'success',
                'message' => 'Itinerary berhasil dibuat',
                'itinerary_id' => $itineraryId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat itinerary'], 500);
        }
    }
    
    /**
     * Show - View itinerary details
     */
    public function show() {
        Middleware::requireAuth();
        
        $itineraryId = $this->get('id');
        $userId = Session::get('user_id');
        
        $itinerary = $this->itineraryModel->findById($itineraryId);
        
        if (!$itinerary || ($itinerary['user_id'] != $userId && !$itinerary['is_public'])) {
            Session::flash('error', 'Itinerary tidak ditemukan');
            $this->redirect('itinerary');
        }
        
        $items = $this->itineraryModel->getItems($itineraryId);
        
        $data = [
            'title' => 'Detail Itinerary - MyWisata',
            'itinerary' => $itinerary,
            'items' => $items,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('itinerary/show', $data);
    }
    
    /**
     * Add item to itinerary
     */
    public function addItem() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itineraryId = $this->post('itinerary_id');
        
        // Check ownership
        $itinerary = $this->itineraryModel->findById($itineraryId);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $data = [
            'itinerary_id' => $itineraryId,
            'item_type' => $this->post('item_type'),
            'item_id' => $this->post('item_id'),
            'day_number' => $this->post('day_number', 1),
            'start_time' => $this->post('start_time'),
            'end_time' => $this->post('end_time'),
            'notes' => $this->post('notes'),
            'order_index' => $this->post('order_index', 0)
        ];
        
        // Validate input
        if (empty($data['item_type']) || empty($data['item_id'])) {
            $this->json(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
        }
        
        $itemId = $this->itineraryModel->addItem($data);
        
        if ($itemId) {
            Logger::audit('ADD_ITINERARY_ITEM', 'itinerary_items', "Added item to itinerary ID: {$itineraryId}", [], $data);
            
            $this->json([
                'status' => 'success',
                'message' => 'Item berhasil ditambahkan',
                'item_id' => $itemId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan item'], 500);
        }
    }
    
    /**
     * Update item
     */
    public function updateItem() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemId = $this->post('item_id');
        
        // Check ownership
        $item = $this->itineraryModel->getItemById($itemId);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Item tidak ditemukan'], 404);
        }
        
        $itinerary = $this->itineraryModel->findById($item['itinerary_id']);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $data = [
            'day_number' => $this->post('day_number'),
            'start_time' => $this->post('start_time'),
            'end_time' => $this->post('end_time'),
            'notes' => $this->post('notes'),
            'order_index' => $this->post('order_index')
        ];
        
        $updated = $this->itineraryModel->updateItem($itemId, $data);
        
        if ($updated) {
            Logger::audit('UPDATE_ITINERARY_ITEM', 'itinerary_items', "Updated item ID: {$itemId}", [], $data);
            
            $this->json(['status' => 'success', 'message' => 'Item berhasil diperbarui']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui item'], 500);
        }
    }
    
    /**
     * Remove item
     */
    public function removeItem() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemId = $this->post('item_id');
        
        // Check ownership
        $item = $this->itineraryModel->getItemById($itemId);
        if (!$item) {
            $this->json(['status' => 'error', 'message' => 'Item tidak ditemukan'], 404);
        }
        
        $itinerary = $this->itineraryModel->findById($item['itinerary_id']);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $removed = $this->itineraryModel->removeItem($itemId);
        
        if ($removed) {
            Logger::audit('REMOVE_ITINERARY_ITEM', 'itinerary_items', "Removed item ID: {$itemId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Item berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus item'], 500);
        }
    }
    
    /**
     * Delete itinerary
     */
    public function delete() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itineraryId = $this->post('itinerary_id');
        
        // Check ownership
        $itinerary = $this->itineraryModel->findById($itineraryId);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $deleted = $this->itineraryModel->delete($itineraryId);
        
        if ($deleted) {
            Logger::audit('DELETE_ITINERARY', 'itineraries', "Deleted itinerary ID: {$itineraryId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Itinerary berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus itinerary'], 500);
        }
    }
    
    /**
     * Share itinerary
     */
    public function share() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itineraryId = $this->post('itinerary_id');
        
        // Check ownership
        $itinerary = $this->itineraryModel->findById($itineraryId);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $shareToken = $this->itineraryModel->generateShareToken($itineraryId);
        
        if ($shareToken) {
            $shareUrl = BASE_URL . 'itinerary/shared/' . $shareToken;
            
            Logger::audit('SHARE_ITINERARY', 'itineraries', "Generated share token for itinerary ID: {$itineraryId}", [], []);
            
            $this->json([
                'status' => 'success',
                'data' => [
                    'share_url' => $shareUrl,
                    'share_token' => $shareToken
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat share link'], 500);
        }
    }
    
    /**
     * View shared itinerary
     */
    public function viewShared() {
        $shareToken = $this->get('token');
        
        if (empty($shareToken)) {
            Session::flash('error', 'Token tidak valid');
            $this->redirect('home');
        }
        
        $itinerary = $this->itineraryModel->getByShareToken($shareToken);
        
        if (!$itinerary) {
            Session::flash('error', 'Itinerary tidak ditemukan atau sudah kadaluarsa');
            $this->redirect('home');
        }
        
        $items = $this->itineraryModel->getItems($itinerary['id']);
        
        $data = [
            'title' => 'Itinerary Berbagi - MyWisata',
            'itinerary' => $itinerary,
            'items' => $items
        ];
        
        $this->view('itinerary/shared', $data);
    }
}
