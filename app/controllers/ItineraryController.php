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
        $templateId = $this->post('template_id');
        
        $data = [
            'user_id' => $userId,
            'title' => $this->post('title'),
            'description' => $this->post('description'),
            'start_date' => $this->post('start_date'),
            'end_date' => $this->post('end_date'),
            'budget' => $this->post('budget'),
            'participants' => $this->post('participants', 1),
            'is_public' => $this->post('is_public', 0),
            'timeline_view_mode' => $this->post('timeline_view_mode', 'timeline'),
            'template_id' => $templateId
        ];
        
        // Validate input
        if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->json(['status' => 'error', 'message' => 'Data tidak lengkap'], 400);
        }
        
        $itineraryId = $this->itineraryModel->create($data);
        
        if ($itineraryId) {
            // If template is provided, copy template events
            if ($templateId) {
                $this->copyTemplateEvents($templateId, $itineraryId);
            }
            
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
     * Copy template events to itinerary
     */
    private function copyTemplateEvents($templateId, $itineraryId) {
        $templateEventModel = $this->model('ItineraryTemplateEvent');
        $templateEvents = $templateEventModel->getByTemplateId($templateId);
        
        $timelineEventModel = $this->model('ItineraryTimelineEvent');
        
        foreach ($templateEvents as $templateEvent) {
            $timelineEventModel->create([
                'itinerary_id' => $itineraryId,
                'day_number' => $templateEvent['day_number'],
                'event_order' => $templateEvent['event_order'],
                'event_type' => $templateEvent['event_type'],
                'event_title' => $templateEvent['event_title'],
                'event_description' => $templateEvent['event_description'],
                'start_time' => $templateEvent['start_time'],
                'end_time' => $templateEvent['end_time'],
                'duration_minutes' => $templateEvent['duration_minutes'],
                'is_mandatory' => !$templateEvent['is_optional']
            ]);
        }
    }
    
    /**
     * Show - View itinerary details with visual timeline
     */
    public function show() {
        Middleware::requireAuth();
        
        $itineraryId = $this->get('id');
        $userId = Session::get('user_id');
        $viewMode = $this->get('view_mode', 'timeline'); // timeline, map, list, calendar
        
        $itinerary = $this->itineraryModel->findById($itineraryId);
        
        if (!$itinerary || ($itinerary['user_id'] != $userId && !$itinerary['is_public'])) {
            Session::flash('error', 'Itinerary tidak ditemukan');
            $this->redirect('itinerary');
        }
        
        $items = $this->itineraryModel->getItems($itineraryId);
        
        // Get timeline events if view mode is timeline
        $timelineEvents = [];
        if ($viewMode === 'timeline') {
            $timelineEventModel = $this->model('ItineraryTimelineEvent');
            $timelineEvents = $timelineEventModel->getByItineraryId($itineraryId);
            
            // Get day summaries
            $daySummaryModel = $this->model('ItineraryDaySummary');
            $daySummaries = $daySummaryModel->getByItineraryId($itineraryId);
        }
        
        $data = [
            'title' => 'Detail Itinerary - MyWisata',
            'itinerary' => $itinerary,
            'items' => $items,
            'view_mode' => $viewMode,
            'timeline_events' => $timelineEvents ?? [],
            'day_summaries' => $daySummaries ?? [],
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
     * Share itinerary with advanced options
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
        $shareType = $this->post('share_type', 'link'); // public, private, link, email
        $sharedWithUserId = $this->post('shared_with_user_id');
        $canEdit = $this->post('can_edit', false);
        $canComment = $this->post('can_comment', true);
        $expiresAt = $this->post('expires_at');
        
        // Check ownership
        $itinerary = $this->itineraryModel->findById($itineraryId);
        if (!$itinerary || $itinerary['user_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }
        
        $shareToken = $this->itineraryModel->generateShareToken($itineraryId);
        
        if ($shareToken) {
            // Create sharing record
            $itinerarySharingModel = $this->model('ItinerarySharing');
            $itinerarySharingModel->create([
                'itinerary_id' => $itineraryId,
                'shared_by_user_id' => $userId,
                'shared_with_user_id' => $sharedWithUserId,
                'share_type' => $shareType,
                'share_token' => $shareToken,
                'share_link' => BASE_URL . 'itinerary/shared/' . $shareToken,
                'can_edit' => $canEdit,
                'can_comment' => $canComment,
                'expires_at' => $expiresAt
            ]);
            
            $shareUrl = BASE_URL . 'itinerary/shared/' . $shareToken;
            
            Logger::audit('SHARE_ITINERARY', 'itinerary_sharing', "Shared itinerary ID: {$itineraryId}", [], [
                'share_type' => $shareType,
                'can_edit' => $canEdit
            ]);
            
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
     * Add comment to itinerary
     */
    public function addComment() {
        Middleware::requireAuth();
        
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itineraryId = $this->post('itinerary_id');
        $commentText = $this->post('comment_text');
        $parentCommentId = $this->post('parent_comment_id');
        
        $itinerary = $this->itineraryModel->findById($itineraryId);
        if (!$itinerary) {
            $this->json(['status' => 'error', 'message' => 'Itinerary tidak ditemukan'], 404);
        }
        
        $commentModel = $this->model('ItineraryComment');
        $commentId = $commentModel->create([
            'itinerary_id' => $itineraryId,
            'user_id' => $userId,
            'comment_text' => $commentText,
            'parent_comment_id' => $parentCommentId
        ]);
        
        if ($commentId) {
            $this->json([
                'status' => 'success',
                'message' => 'Komentar berhasil ditambahkan',
                'comment_id' => $commentId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan komentar'], 500);
        }
    }
    
    /**
     * Get itinerary templates
     */
    public function templates() {
        Middleware::requireAuth();
        
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        $destinationId = $this->get('destination_id');
        $durationDays = $this->get('duration_days');
        
        $templateModel = $this->model('ItineraryTemplate');
        $templates = $templateModel->getActive($page, $limit, $destinationId, $durationDays);
        
        $this->json([
            'status' => 'success',
            'data' => $templates
        ]);
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
