<?php

/**
 * MyWisata Application - Admin Controller
 *
 * Handles administrator dashboard and management functions.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class AdminController extends Controller
{
    /**
     * Constructor - Require admin role
     */
    public function __construct()
    {
        parent::__construct();
        Middleware::requireRole('admin');
    }

    /**
     * Dashboard - Main admin dashboard
     */
    public function dashboard()
    {
        $db = Database::getInstance();

        // Get statistics
        $stats = [
            'total_users' => $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch()['count'],
            'total_guides' => $db->query("SELECT COUNT(*) as count FROM tour_guides WHERE is_verified = 1")->fetch()['count'],
            'total_destinations' => $db->query("SELECT COUNT(*) as count FROM destinations WHERE is_active = 1")->fetch()['count'],
            'total_transactions' => $db->query("SELECT COUNT(*) as count FROM transactions WHERE payment_status = 'paid'")->fetch()['count'],
            'pending_guides' => $db->query("SELECT COUNT(*) as count FROM tour_guides WHERE is_verified = 0")->fetch()['count'],
            'pending_hotels' => $db->query("SELECT COUNT(*) as count FROM hotels WHERE is_approved = 0")->fetch()['count'],
            'pending_restaurants' => $db->query("SELECT COUNT(*) as count FROM restaurants WHERE is_approved = 0")->fetch()['count'],
        ];

        // Get monthly revenue
        $monthly_revenue = $db->query("SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions 
                                       WHERE payment_status = 'paid' 
                                       AND MONTH(created_at) = MONTH(CURRENT_DATE) 
                                       AND YEAR(created_at) = YEAR(CURRENT_DATE)")->fetch()['total'];

        // Get recent bookings
        $recent_bookings = $db->query("SELECT b.*, u.name as user_name, tg.user_id as guide_id 
                                       FROM bookings b 
                                       LEFT JOIN users u ON b.user_id = u.id 
                                       LEFT JOIN tour_guides tg ON b.guide_id = tg.id 
                                       ORDER BY b.created_at DESC LIMIT 5")->fetchAll();

        $data = [
            'title' => 'Dashboard Admin - MyWisata',
            'stats' => $stats,
            'monthly_revenue' => $monthly_revenue,
            'recent_bookings' => $recent_bookings,
        ];

        $this->view('admin/dashboard', $data);
    }

    /**
     * Users management - List all users
     */
    public function users()
    {
        $db = Database::getInstance();

        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $search = $this->get('search', '');
        $role_filter = $this->get('role', '');
        $status_filter = $this->get('status', '');

        $where = ['1=1'];
        $params = [];

        if (!empty($search)) {
            $where[] = "(name LIKE :search OR email LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if (!empty($role_filter)) {
            $where[] = "role = :role";
            $params['role'] = $role_filter;
        }

        if (!empty($status_filter)) {
            $where[] = "status = :status";
            $params['status'] = $status_filter;
        }

        $whereClause = implode(' AND ', $where);

        $users = $db->query("SELECT * FROM users WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}", $params)->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM users WHERE {$whereClause}", $params)->fetch()['count'];

        $data = [
            'title' => 'Manajemen Pengguna - MyWisata',
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'search' => $search,
            'role_filter' => $role_filter,
            'status_filter' => $status_filter,
        ];

        $this->view('admin/users/index', $data);
    }

    /**
     * Edit user
     */
    public function editUser()
    {
        $id = $this->get('id');

        if (!$id) {
            $this->redirect('admin/users');
        }

        $db = Database::getInstance();
        $user = $db->query("SELECT * FROM users WHERE id = :id", ['id' => $id])->fetch();

        if (!$user) {
            Session::flash('error', 'Pengguna tidak ditemukan');
            $this->redirect('admin/users');
        }

        $data = [
            'title' => 'Edit Pengguna - MyWisata',
            'user' => $user,
        ];

        $this->view('admin/users/edit', $data);
    }

    /**
     * Update user
     */
    public function updateUser()
    {
        $id = $this->post('id');
        $name = $this->post('name');
        $email = $this->post('email');
        $phone = $this->post('phone');
        $role = $this->post('role');
        $status = $this->post('status');

        $validator = new Validator($_POST);
        $validator->required(['id', 'name', 'email', 'role', 'status'])
                  ->email('email');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('admin/users/edit?id=' . $id);
        }

        $db = Database::getInstance();
        $db->query(
            "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id",
            ['name' => $name, 'email' => $email, 'phone' => $phone, 'role' => $role, 'status' => $status, 'id' => $id]
        );

        Logger::audit('UPDATE_USER', 'users', "Updated user ID: {$id}", [], ['id' => $id, 'name' => $name, 'role' => $role, 'status' => $status]);

        Session::flash('success', 'Pengguna berhasil diperbarui');
        $this->redirect('admin/users');
    }

    /**
     * Tour guides management
     */
    public function guides()
    {
        $db = Database::getInstance();

        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $guides = $db->query("SELECT tg.*, u.name, u.email, u.phone 
                             FROM tour_guides tg 
                             LEFT JOIN users u ON tg.user_id = u.id 
                             ORDER BY tg.created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM tour_guides")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Tour Guide - MyWisata',
            'guides' => $guides,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/guides/index', $data);
    }

    /**
     * Approve tour guide
     */
    public function approveGuide()
    {
        $id = $this->post('id');

        if (!$id) {
            $this->json(['status' => 'error', 'message' => 'ID tidak valid'], 400);
        }

        $db = Database::getInstance();
        $db->query(
            "UPDATE tour_guides SET is_verified = 1, verified_at = NOW(), verified_by = :admin_id WHERE id = :id",
            ['id' => $id, 'admin_id' => Middleware::userId()]
        );

        Logger::audit('APPROVE_GUIDE', 'tour_guides', "Approved guide ID: {$id}", [], ['id' => $id]);

        $this->json(['status' => 'success', 'message' => 'Tour guide berhasil disetujui']);
    }

    /**
     * Destinations management
     */
    public function destinations()
    {
        $db = Database::getInstance();

        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $destinations = $db->query("SELECT d.*, dc.name as category_name 
                                    FROM destinations d 
                                    LEFT JOIN destination_categories dc ON d.category_id = dc.id 
                                    ORDER BY d.created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM destinations")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Destinasi - MyWisata',
            'destinations' => $destinations,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/destinations/index', $data);
    }

    /**
     * Create destination form
     */
    public function createDestination()
    {
        $db = Database::getInstance();
        $categories = $db->query("SELECT * FROM destination_categories ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Tambah Destinasi - MyWisata',
            'categories' => $categories,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/destinations/create', $data);
    }

    /**
     * Store destination
     */
    public function storeDestination()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('admin/createDestination');
        }

        $db = Database::getInstance();

        $imageFile = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/destinations');
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('admin/createDestination');
            }
        }

        $db->query(
            "INSERT INTO destinations (name, slug, description, short_desc, category_id, city, province, latitude, longitude, entry_fee, main_image, is_active, created_at)
             VALUES (:name, :slug, :description, :short_desc, :category_id, :city, :province, :latitude, :longitude, :entry_fee, :main_image, 1, NOW())",
            [
                'name' => $this->post('name'),
                'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $this->post('name'))),
                'description' => $this->post('description', ''),
                'short_desc' => $this->post('short_desc', ''),
                'category_id' => $this->post('category_id') ?: null,
                'city' => $this->post('city', ''),
                'province' => $this->post('province', ''),
                'latitude' => $this->post('latitude') ?: null,
                'longitude' => $this->post('longitude') ?: null,
                'entry_fee' => $this->post('entry_fee', 0),
                'main_image' => $imageFile,
            ]
        );

        Logger::audit('CREATE_DESTINATION', 'destinations', "Created destination: " . $this->post('name'));
        Session::flash('success', 'Destinasi berhasil dibuat');
        $this->redirect('admin/destinations');
    }

    /**
     * Edit destination form
     */
    public function editDestination()
    {
        $id = $this->get('id');
        $db = Database::getInstance();

        $destination = $db->query("SELECT * FROM destinations WHERE id = :id", ['id' => $id])->fetch();
        if (!$destination) {
            Session::flash('error', 'Destinasi tidak ditemukan');
            $this->redirect('admin/destinations');
        }

        $categories = $db->query("SELECT * FROM destination_categories ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Edit Destinasi - MyWisata',
            'destination' => $destination,
            'categories' => $categories,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/destinations/edit', $data);
    }

    /**
     * Update destination
     */
    public function updateDestination()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('admin/destinations');
        }

        $id = $this->post('id');
        $db = Database::getInstance();

        $imageFile = $this->post('existing_image', '');
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/destinations');
                if (!empty($this->post('existing_image'))) {
                    FileUpload::delete('destinations/' . $this->post('existing_image'));
                }
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('admin/editDestination?id=' . $id);
            }
        }

        $db->query(
            "UPDATE destinations SET 
                name = :name, description = :description, short_desc = :short_desc,
                category_id = :category_id, city = :city, province = :province,
                latitude = :latitude, longitude = :longitude, entry_fee = :entry_fee,
                main_image = :main_image, is_active = :is_active, updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $id,
                'name' => $this->post('name'),
                'description' => $this->post('description', ''),
                'short_desc' => $this->post('short_desc', ''),
                'category_id' => $this->post('category_id') ?: null,
                'city' => $this->post('city', ''),
                'province' => $this->post('province', ''),
                'latitude' => $this->post('latitude') ?: null,
                'longitude' => $this->post('longitude') ?: null,
                'entry_fee' => $this->post('entry_fee', 0),
                'main_image' => $imageFile,
                'is_active' => $this->post('is_active', 1),
            ]
        );

        Logger::audit('UPDATE_DESTINATION', 'destinations', "Updated destination ID: {$id}");
        Session::flash('success', 'Destinasi berhasil diperbarui');
        $this->redirect('admin/destinations');
    }

    /**
     * Delete destination
     */
    public function deleteDestination()
    {
        if (!$this->isAjax()) {
            $this->redirect('admin/destinations');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $id = $this->post('id');
        $db = Database::getInstance();

        $dest = $db->query("SELECT main_image FROM destinations WHERE id = :id", ['id' => $id])->fetch();
        if ($dest && !empty($dest['main_image'])) {
            FileUpload::delete('destinations/' . $dest['main_image']);
        }

        $db->query("DELETE FROM destinations WHERE id = :id", ['id' => $id]);
        Logger::audit('DELETE_DESTINATION', 'destinations', "Deleted destination ID: {$id}");
        $this->json(['status' => 'success', 'message' => 'Destinasi berhasil dihapus']);
    }

    /**
     * Settings
     */
    public function settings()
    {
        $db = Database::getInstance();

        if ($this->post()) {
            foreach ($_POST as $key => $value) {
                if ($key !== 'csrf_token') {
                    $db->query(
                        "INSERT INTO settings (key_name, value, updated_at) VALUES (:key, :value, NOW()) 
                               ON DUPLICATE KEY UPDATE value = :value, updated_at = NOW()",
                        ['key' => $key, 'value' => $value]
                    );
                }
            }

            Logger::audit('UPDATE_SETTINGS', 'settings', 'Updated system settings');
            Session::flash('success', 'Pengaturan berhasil disimpan');
            $this->redirect('admin/settings');
        }

        $settings = $db->query("SELECT * FROM settings ORDER BY key_name")->fetchAll();
        $settingsArray = [];

        foreach ($settings as $setting) {
            $settingsArray[$setting['key_name']] = $setting['value'];
        }

        $data = [
            'title' => 'Pengaturan - MyWisata',
            'settings' => $settingsArray,
        ];

        $this->view('admin/settings', $data);
    }

    /**
     * Hotels management
     */
    public function hotels()
    {
        $db = Database::getInstance();
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $hotels = $db->query("SELECT * FROM hotels ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM hotels")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Hotel - MyWisata',
            'hotels' => $hotels,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/hotels/index', $data);
    }

    /**
     * Approve hotel
     */
    public function approveHotel()
    {
        $id = $this->post('id');
        if (!$id) {
            $this->json(['status' => 'error', 'message' => 'ID tidak valid'], 400);
        }

        $db = Database::getInstance();
        $db->query("UPDATE hotels SET is_approved = 1 WHERE id = :id", ['id' => $id]);

        Logger::audit('APPROVE_HOTEL', 'hotels', "Approved hotel ID: {$id}", [], ['id' => $id]);
        $this->json(['status' => 'success', 'message' => 'Hotel berhasil disetujui']);
    }

    /**
     * Restaurants management
     */
    public function restaurants()
    {
        $db = Database::getInstance();
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $restaurants = $db->query("SELECT * FROM restaurants ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM restaurants")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Restoran - MyWisata',
            'restaurants' => $restaurants,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/restaurants/index', $data);
    }

    /**
     * Approve restaurant
     */
    public function approveRestaurant()
    {
        $id = $this->post('id');
        if (!$id) {
            $this->json(['status' => 'error', 'message' => 'ID tidak valid'], 400);
        }

        $db = Database::getInstance();
        $db->query("UPDATE restaurants SET is_approved = 1 WHERE id = :id", ['id' => $id]);

        Logger::audit('APPROVE_RESTAURANT', 'restaurants', "Approved restaurant ID: {$id}", [], ['id' => $id]);
        $this->json(['status' => 'success', 'message' => 'Restoran berhasil disetujui']);
    }

    /**
     * Events management
     */
    public function events()
    {
        $db = Database::getInstance();
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $events = $db->query("SELECT * FROM events ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM events")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Event - MyWisata',
            'events' => $events,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/events/index', $data);
    }

    /**
     * Approve event
     */
    public function approveEvent()
    {
        $id = $this->post('id');
        if (!$id) {
            $this->json(['status' => 'error', 'message' => 'ID tidak valid'], 400);
        }

        $db = Database::getInstance();
        $db->query("UPDATE events SET is_active = 1 WHERE id = :id", ['id' => $id]);

        Logger::audit('APPROVE_EVENT', 'events', "Approved event ID: {$id}", [], ['id' => $id]);
        $this->json(['status' => 'success', 'message' => 'Event berhasil disetujui']);
    }

    /**
     * Transactions management
     */
    public function transactions()
    {
        $db = Database::getInstance();
        $page = $this->get('page', 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $transactions = $db->query("SELECT t.*, u.name as user_name FROM transactions t LEFT JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT {$limit} OFFSET {$offset}")->fetchAll();
        $total = $db->query("SELECT COUNT(*) as count FROM transactions")->fetch()['count'];

        $data = [
            'title' => 'Manajemen Transaksi - MyWisata',
            'transactions' => $transactions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->view('admin/transactions/index', $data);
    }

    /**
     * Audio guides management
     */
    public function audioGuides()
    {
        $db = Database::getInstance();

        $audioGuides = $db->query("SELECT ag.*, d.name as destination_name 
                                    FROM audio_guides ag 
                                    LEFT JOIN destinations d ON ag.destination_id = d.id 
                                    ORDER BY ag.created_at DESC")->fetchAll();

        $destinations = $db->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Manajemen Audio Guide - MyWisata',
            'audio_guides' => $audioGuides,
            'destinations' => $destinations,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/audioguides/index', $data);
    }

    /**
     * Create audio guide
     */
    public function createAudioGuide()
    {
        if (!$this->isAjax()) {
            $this->redirect('admin/audioGuides');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $destinationId = $this->post('destination_id');
        $title = $this->post('title');
        $language = $this->post('language', 'Indonesia');
        $description = $this->post('description', '');
        $duration = (int) $this->post('duration', 0);

        if (empty($destinationId) || empty($title)) {
            $this->json(['status' => 'error', 'message' => 'Destinasi dan judul wajib diisi'], 400);
        }

        $audioFile = '';
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $audioFile = FileUpload::upload(
                    $_FILES['audio_file'],
                    APP_ROOT . '/public/uploads/audio',
                    ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/x-wav'],
                    52428800
                );
            } catch (Exception $e) {
                $this->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
        }

        $db = Database::getInstance();
        $db->query(
            "INSERT INTO audio_guides (destination_id, title, description, audio_file, language, duration, is_active, created_at)
             VALUES (:destination_id, :title, :description, :audio_file, :language, :duration, 1, NOW())",
            [
                'destination_id' => $destinationId,
                'title' => $title,
                'description' => $description,
                'audio_file' => $audioFile,
                'language' => $language,
                'duration' => $duration,
            ]
        );

        Logger::audit('CREATE_AUDIO_GUIDE', 'audio_guides', "Created audio guide: {$title}");

        $this->json(['status' => 'success', 'message' => 'Audio guide berhasil dibuat']);
    }

    /**
     * Delete audio guide
     */
    public function deleteAudioGuide()
    {
        if (!$this->isAjax()) {
            $this->redirect('admin/audioGuides');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $id = $this->post('id');

        if (empty($id)) {
            $this->json(['status' => 'error', 'message' => 'ID wajib diisi'], 400);
        }

        $db = Database::getInstance();
        $audio = $db->query("SELECT audio_file FROM audio_guides WHERE id = :id", ['id' => $id])->fetch();

        if ($audio && !empty($audio['audio_file'])) {
            FileUpload::delete('audio/' . $audio['audio_file']);
        }

        $db->query("DELETE FROM audio_guides WHERE id = :id", ['id' => $id]);

        Logger::audit('DELETE_AUDIO_GUIDE', 'audio_guides', "Deleted audio guide ID: {$id}");

        $this->json(['status' => 'success', 'message' => 'Audio guide berhasil dihapus']);
    }

    /**
     * Products management
     */
    public function products()
    {
        $productModel = new Product();
        $products = $productModel->getAllWithFilters(['limit' => 50, 'offset' => 0]);
        $categories = $productModel->getCategories();

        $db = Database::getInstance();
        $destinations = $db->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Manajemen Produk - MyWisata',
            'products' => $products,
            'categories' => $categories,
            'destinations' => $destinations,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/products/index', $data);
    }

    /**
     * Create product form
     */
    public function createProduct()
    {
        $productModel = new Product();
        $categories = $productModel->getCategories();

        $db = Database::getInstance();
        $destinations = $db->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Tambah Produk - MyWisata',
            'categories' => $categories,
            'destinations' => $destinations,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/products/create', $data);
    }

    /**
     * Store product
     */
    public function storeProduct()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('admin/createProduct');
        }

        $imageFile = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/products');
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('admin/createProduct');
            }
        }

        $name = $this->post('name');
        $productModel = new Product();
        $productId = $productModel->create([
            'name' => $name,
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)),
            'description' => $this->post('description', ''),
            'short_desc' => $this->post('short_desc', ''),
            'category_id' => $this->post('category_id') ?: null,
            'destination_id' => $this->post('destination_id') ?: null,
            'price' => $this->post('price', 0),
            'discount_price' => $this->post('discount_price', 0),
            'stock' => $this->post('stock', 0),
            'sku' => $this->post('sku', ''),
            'main_image' => $imageFile,
            'is_active' => $this->post('is_active', 1),
            'is_featured' => $this->post('is_featured', 0),
            'region' => $this->post('region', ''),
        ]);

        Logger::audit('CREATE_PRODUCT', 'products', "Created product: {$name}");
        Session::flash('success', 'Produk berhasil dibuat');
        $this->redirect('admin/products');
    }

    /**
     * Edit product form
     */
    public function editProduct()
    {
        $id = $this->get('id');
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan');
            $this->redirect('admin/products');
        }

        $categories = $productModel->getCategories();
        $db = Database::getInstance();
        $destinations = $db->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Edit Produk - MyWisata',
            'product' => $product,
            'categories' => $categories,
            'destinations' => $destinations,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/products/edit', $data);
    }

    /**
     * Update product
     */
    public function updateProduct()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('admin/products');
        }

        $id = $this->post('id');
        $imageFile = $this->post('existing_image', '');

        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/products');
                if (!empty($this->post('existing_image'))) {
                    FileUpload::delete('products/' . $this->post('existing_image'));
                }
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('admin/editProduct?id=' . $id);
            }
        }

        $name = $this->post('name');
        $productModel = new Product();
        $productModel->update($id, [
            'name' => $name,
            'description' => $this->post('description', ''),
            'short_desc' => $this->post('short_desc', ''),
            'category_id' => $this->post('category_id') ?: null,
            'destination_id' => $this->post('destination_id') ?: null,
            'price' => $this->post('price', 0),
            'discount_price' => $this->post('discount_price', 0),
            'stock' => $this->post('stock', 0),
            'sku' => $this->post('sku', ''),
            'main_image' => $imageFile,
            'is_active' => $this->post('is_active', 1),
            'is_featured' => $this->post('is_featured', 0),
            'region' => $this->post('region', ''),
        ]);

        Logger::audit('UPDATE_PRODUCT', 'products', "Updated product ID: {$id}");
        Session::flash('success', 'Produk berhasil diperbarui');
        $this->redirect('admin/products');
    }

    /**
     * Delete product
     */
    public function deleteProduct()
    {
        if (!$this->isAjax()) {
            $this->redirect('admin/products');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $id = $this->post('id');
        $productModel = new Product();
        $product = $productModel->findById($id);

        if ($product && !empty($product['main_image'])) {
            FileUpload::delete('products/' . $product['main_image']);
        }

        $productModel->delete($id);
        Logger::audit('DELETE_PRODUCT', 'products', "Deleted product ID: {$id}");
        $this->json(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
    }
}
