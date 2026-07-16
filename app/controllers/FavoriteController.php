<?php
/**
 * MyWisata Application - Favorite Controller
 * 
 * Handles user favorites/wishlist with enhanced features.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class FavoriteController extends Controller {
    
    private $favoriteModel;
    
    /**
     * Constructor - Require login
     */
    public function __construct() {
        parent::__construct();
        if (!Session::get('user_id')) {
            $this->redirect('auth/login');
        }
        $this->favoriteModel = $this->model('Favorite');
    }
    
    /**
     * Index - List user favorites
     */
    public function index() {
        $userId = Session::get('user_id');
        
        $itemType = $this->get('type', 'all');
        $folder = $this->get('folder', null);
        $page = $this->get('page', 1);
        $limit = $this->get('limit', 12);
        
        $favorites = $this->favoriteModel->getUserFavorites($userId, $itemType === 'all' ? null : $itemType, $folder, $page, $limit);
        $total = $this->favoriteModel->countUserFavorites($userId, $itemType === 'all' ? null : $itemType, $folder);
        $folders = $this->favoriteModel->getUserFolders($userId);
        
        $data = [
            'title' => 'Favorit Saya - MyWisata',
            'favorites' => $favorites,
            'total' => $total,
            'type_filter' => $itemType,
            'folder_filter' => $folder,
            'folders' => $folders,
            'page' => $page,
            'limit' => $limit,
            'csrf_token' => Middleware::csrfToken()
        ];
        
        $this->view('favorites/index', $data);
    }
    
    /**
     * Add to favorites
     */
    public function add() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        $folder = $this->post('folder', null);
        $notes = $this->post('notes', null);
        
        // Validate input
        if (empty($itemType) || empty($itemId)) {
            $this->json(['status' => 'error', 'message' => 'Data tidak valid'], 400);
        }
        
        if (!in_array($itemType, ['destination', 'hotel', 'restaurant', 'event', 'tour_guide'])) {
            $this->json(['status' => 'error', 'message' => 'Tipe item tidak valid'], 400);
        }
        
        // Check if already favorited
        if ($this->favoriteModel->isFavorited($userId, $itemType, $itemId)) {
            $this->json(['status' => 'error', 'message' => 'Sudah ada di favorit'], 400);
        }
        
        $favoriteId = $this->favoriteModel->add($userId, $itemType, $itemId, $folder, $notes);
        
        if ($favoriteId) {
            Logger::audit('ADD_FAVORITE', 'user_favorites', "Added favorite: {$itemType} ID: {$itemId}", [], [
                'folder' => $folder,
                'notes' => $notes
            ]);
            
            $this->json(['status' => 'success', 'message' => 'Ditambahkan ke favorit', 'favorite_id' => $favoriteId]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menambahkan ke favorit'], 500);
        }
    }
    
    /**
     * Remove from favorites
     */
    public function remove() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        
        $removed = $this->favoriteModel->remove($userId, $itemType, $itemId);
        
        if ($removed) {
            Logger::audit('REMOVE_FAVORITE', 'user_favorites', "Removed favorite: {$itemType} ID: {$itemId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Dihapus dari favorit']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus dari favorit'], 500);
        }
    }
    
    /**
     * Create folder
     */
    public function createFolder() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $name = $this->post('name');
        $description = $this->post('description', null);
        
        if (empty($name)) {
            $this->json(['status' => 'error', 'message' => 'Nama folder wajib diisi'], 400);
        }
        
        $folderId = $this->favoriteModel->createFolder($userId, $name, $description);
        
        if ($folderId) {
            Logger::audit('CREATE_FAVORITE_FOLDER', 'favorite_folders', "Created folder: {$name}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Folder berhasil dibuat', 'folder_id' => $folderId]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal membuat folder'], 500);
        }
    }
    
    /**
     * Update folder
     */
    public function updateFolder() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $folderId = $this->post('folder_id');
        $name = $this->post('name');
        $description = $this->post('description', null);
        
        if (empty($name)) {
            $this->json(['status' => 'error', 'message' => 'Nama folder wajib diisi'], 400);
        }
        
        $updated = $this->favoriteModel->updateFolder($folderId, $userId, $name, $description);
        
        if ($updated) {
            Logger::audit('UPDATE_FAVORITE_FOLDER', 'favorite_folders', "Updated folder ID: {$folderId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Folder berhasil diperbarui']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui folder'], 500);
        }
    }
    
    /**
     * Delete folder
     */
    public function deleteFolder() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $folderId = $this->post('folder_id');
        
        $deleted = $this->favoriteModel->deleteFolder($folderId, $userId);
        
        if ($deleted) {
            Logger::audit('DELETE_FAVORITE_FOLDER', 'favorite_folders', "Deleted folder ID: {$folderId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Folder berhasil dihapus']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus folder'], 500);
        }
    }
    
    /**
     * Move item to folder
     */
    public function moveToFolder() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        $folder = $this->post('folder');
        
        $moved = $this->favoriteModel->moveToFolder($userId, $itemType, $itemId, $folder);
        
        if ($moved) {
            Logger::audit('MOVE_FAVORITE', 'user_favorites', "Moved favorite to folder: {$folder}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Item berhasil dipindahkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memindahkan item'], 500);
        }
    }
    
    /**
     * Update notes
     */
    public function updateNotes() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $itemType = $this->post('item_type');
        $itemId = $this->post('item_id');
        $notes = $this->post('notes');
        
        $updated = $this->favoriteModel->updateNotes($userId, $itemType, $itemId, $notes);
        
        if ($updated) {
            Logger::audit('UPDATE_FAVORITE_NOTES', 'user_favorites', "Updated notes for {$itemType} ID: {$itemId}", [], []);
            
            $this->json(['status' => 'success', 'message' => 'Catatan berhasil diperbarui']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal memperbarui catatan'], 500);
        }
    }
    
    /**
     * Check if item is favorited
     */
    public function check() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        $userId = Session::get('user_id');
        $itemType = $this->get('item_type');
        $itemId = $this->get('item_id');
        
        $isFavorited = $this->favoriteModel->isFavorited($userId, $itemType, $itemId);
        
        $this->json([
            'status' => 'success',
            'data' => [
                'is_favorited' => $isFavorited
            ]
        ]);
    }
    
    /**
     * Share wishlist
     */
    public function share() {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }
        
        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }
        
        $userId = Session::get('user_id');
        $folder = $this->post('folder', null);
        
        $shareToken = $this->favoriteModel->generateShareToken($userId, $folder);
        
        if ($shareToken) {
            $shareUrl = BASE_URL . 'favorites/shared/' . $shareToken;
            
            Logger::audit('SHARE_WISHLIST', 'user_favorites', "Generated share token for wishlist", [], [
                'folder' => $folder
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
     * View shared wishlist
     */
    public function viewShared() {
        $shareToken = $this->get('token');
        
        if (empty($shareToken)) {
            Session::flash('error', 'Token tidak valid');
            $this->redirect('home');
        }
        
        $favorites = $this->favoriteModel->getSharedFavorites($shareToken);
        
        if (!$favorites) {
            Session::flash('error', 'Wishlist tidak ditemukan atau sudah kadaluarsa');
            $this->redirect('home');
        }
        
        $data = [
            'title' => 'Wishlist Berbagi - MyWisata',
            'favorites' => $favorites
        ];
        
        $this->view('favorites/shared', $data);
    }
}
