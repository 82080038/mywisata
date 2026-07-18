<?php

/**
 * MyWisata Application - Master Table Controller
 *
 * Generic CRUD controller for all master/reference tables.
 * Each master table can be managed (Create, Read, Update, Delete)
 * with rules: system records cannot be deleted, only deactivated.
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class MasterTableController extends Controller
{
    /**
     * Master table definitions — config drives all CRUD behavior
     */
    private $tables = [
        'destination_categories' => [
            'label' => 'Kategori Destinasi',
            'icon' => 'fa-tags',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-mountain'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'product_categories' => [
            'label' => 'Kategori Produk',
            'icon' => 'fa-gift',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-tshirt'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'languages' => [
            'label' => 'Bahasa',
            'icon' => 'fa-language',
            'columns' => [
                'code' => ['type' => 'text', 'label' => 'Kode (ISO)', 'required' => true, 'placeholder' => 'en'],
                'name' => ['type' => 'text', 'label' => 'Nama (English)', 'required' => true, 'placeholder' => 'English'],
                'native_name' => ['type' => 'text', 'label' => 'Nama Native', 'required' => true, 'placeholder' => 'English'],
                'flag' => ['type' => 'text', 'label' => 'Flag Emoji', 'required' => false, 'placeholder' => '🇬🇧'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['code', 'name', 'native_name'],
        ],
        'specializations' => [
            'label' => 'Spesialisasi Guide',
            'icon' => 'fa-user-tie',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-mountain'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'facilities' => [
            'label' => 'Fasilitas',
            'icon' => 'fa-list-check',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'default' => 'fa-check', 'placeholder' => 'fa-wifi'],
                'category' => ['type' => 'select', 'label' => 'Kategori', 'required' => true, 'options' => [
                    'general' => 'Umum', 'accommodation' => 'Akomodasi', 'dining' => 'Kuliner',
                    'recreation' => 'Rekreasi', 'accessibility' => 'Aksesibilitas', 'safety' => 'Keamanan',
                    'transport' => 'Transportasi', 'service' => 'Layanan', 'view' => 'Pemandangan', 'equipment' => 'Peralatan',
                ]],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'exchange_rates' => [
            'label' => 'Mata Uang',
            'icon' => 'fa-money-bill-wave',
            'columns' => [
                'currency_code' => ['type' => 'text', 'label' => 'Kode Uang', 'required' => true, 'placeholder' => 'USD'],
                'currency_name' => ['type' => 'text', 'label' => 'Nama Uang', 'required' => true, 'placeholder' => 'US Dollar'],
                'currency_symbol' => ['type' => 'text', 'label' => 'Simbol', 'required' => true, 'placeholder' => '$'],
                'rate_to_idr' => ['type' => 'number', 'label' => 'Rate ke IDR', 'required' => true, 'step' => '0.0001'],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
            ],
            'search_fields' => ['currency_code', 'currency_name'],
        ],
        'hotel_types' => [
            'label' => 'Tipe Hotel',
            'icon' => 'fa-hotel',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-hotel'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'restaurant_types' => [
            'label' => 'Tipe Restoran',
            'icon' => 'fa-utensils',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-utensils'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'event_categories' => [
            'label' => 'Kategori Event',
            'icon' => 'fa-calendar-alt',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-festival'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'ticket_types' => [
            'label' => 'Tipe Tiket',
            'icon' => 'fa-ticket-alt',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
        'booking_statuses' => [
            'label' => 'Status Booking',
            'icon' => 'fa-clipboard-check',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama (System)', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'label' => ['type' => 'text', 'label' => 'Label (Tampilan)', 'required' => true],
                'color' => ['type' => 'select', 'label' => 'Warna', 'required' => false, 'options' => [
                    'primary' => 'Primary (Biru)', 'success' => 'Success (Hijau)', 'warning' => 'Warning (Kuning)',
                    'danger' => 'Danger (Merah)', 'info' => 'Info (Cyan)', 'secondary' => 'Secondary (Abu-abu)',
                ]],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
            ],
            'search_fields' => ['name', 'label'],
        ],
        'payment_methods' => [
            'label' => 'Metode Pembayaran',
            'icon' => 'fa-credit-card',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama (System)', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'label' => ['type' => 'text', 'label' => 'Label (Tampilan)', 'required' => true],
                'icon' => ['type' => 'text', 'label' => 'Icon (Font Awesome)', 'required' => false, 'placeholder' => 'fa-credit-card'],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
            ],
            'search_fields' => ['name', 'label'],
        ],
        'dietary_preferences' => [
            'label' => 'Preferensi Diet',
            'icon' => 'fa-leaf',
            'columns' => [
                'name' => ['type' => 'text', 'label' => 'Nama', 'required' => true],
                'slug' => ['type' => 'slug', 'label' => 'Slug', 'required' => true, 'source' => 'name'],
                'category' => ['type' => 'select', 'label' => 'Kategori', 'required' => true, 'options' => [
                    'allergy' => 'Alergi', 'diet' => 'Diet', 'religious' => 'Religius',
                ]],
                'description' => ['type' => 'textarea', 'label' => 'Deskripsi', 'required' => false],
                'sort_order' => ['type' => 'number', 'label' => 'Urutan', 'required' => false, 'default' => 0],
                'is_active' => ['type' => 'boolean', 'label' => 'Aktif', 'default' => 1],
                'is_system' => ['type' => 'hidden', 'default' => 0],
            ],
            'search_fields' => ['name', 'description'],
        ],
    ];

    /**
     * Constructor - Require admin role
     */
    public function __construct()
    {
        parent::__construct();
        Middleware::requireRole('admin');
    }

    /**
     * Get table config, throw error if invalid
     */
    private function getTableConfig($table)
    {
        if (!isset($this->tables[$table])) {
            Session::flash('error', 'Tabel master tidak valid');
            $this->redirect('mastertable/index');
        }
        return $this->tables[$table];
    }

    /**
     * Generate slug from string
     */
    private function generateSlug($text)
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Index - List all master tables (overview page)
     */
    public function index()
    {
        $db = Database::getInstance();
        $tableInfos = [];

        foreach ($this->tables as $name => $config) {
            $count = $db->query("SELECT COUNT(*) as c FROM {$name}")->fetch()['c'];
            $activeCount = 0;
            try {
                $activeCount = $db->query("SELECT COUNT(*) as c FROM {$name} WHERE is_active = 1")->fetch()['c'];
            } catch (Exception $e) {
                // Some tables may not have is_active
            }

            $tableInfos[] = [
                'name' => $name,
                'label' => $config['label'],
                'icon' => $config['icon'],
                'total' => $count,
                'active' => $activeCount,
            ];
        }

        $data = [
            'title' => 'Master Data - MyWisata Admin',
            'tableInfos' => $tableInfos,
        ];

        $this->view('admin/master/index', $data);
    }

    /**
     * List - Show records for a specific master table
     */
    public function list()
    {
        $table = $this->get('table');
        $config = $this->getTableConfig($table);

        $db = Database::getInstance();
        $search = $this->get('q', '');

        $sql = "SELECT * FROM {$table}";
        $params = [];

        if (!empty($search) && !empty($config['search_fields'])) {
            $conditions = [];
            foreach ($config['search_fields'] as $field) {
                $conditions[] = "{$field} LIKE :search";
            }
            $sql .= " WHERE " . implode(' OR ', $conditions);
            $params['search'] = "%{$search}%";
        }

        // Order by sort_order if exists, then by id
        $hasSortOrder = isset($config['columns']['sort_order']);
        $sql .= $hasSortOrder ? " ORDER BY sort_order, id" : " ORDER BY id";

        $records = $db->query($sql, $params)->fetchAll();

        $data = [
            'title' => $config['label'] . ' - Master Data',
            'table' => $table,
            'config' => $config,
            'records' => $records,
            'search' => $search,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/master/list', $data);
    }

    /**
     * Create - Show create form
     */
    public function create()
    {
        $table = $this->get('table');
        $config = $this->getTableConfig($table);

        $data = [
            'title' => 'Tambah ' . $config['label'],
            'table' => $table,
            'config' => $config,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/master/form', $data);
    }

    /**
     * Store - Save new record
     */
    public function store()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('mastertable/index');
        }

        $table = $this->post('table');
        $config = $this->getTableConfig($table);

        $db = Database::getInstance();
        $data = [];

        foreach ($config['columns'] as $field => $fieldConfig) {
            if ($fieldConfig['type'] === 'hidden') {
                $data[$field] = $fieldConfig['default'] ?? 0;
                continue;
            }

            $value = $this->post($field);

            if ($fieldConfig['type'] === 'slug') {
                $sourceField = $fieldConfig['source'] ?? 'name';
                $value = $this->generateSlug($this->post($sourceField));
                // Ensure unique
                $existing = $db->query("SELECT id FROM {$table} WHERE slug = :slug", ['slug' => $value])->fetch();
                if ($existing) {
                    $value .= '-' . rand(100, 999);
                }
            }

            if ($fieldConfig['type'] === 'boolean') {
                $value = $value ? 1 : 0;
            }

            if ($value === null || $value === '') {
                if (isset($fieldConfig['default'])) {
                    $value = $fieldConfig['default'];
                } elseif (!$fieldConfig['required']) {
                    $value = null;
                }
            }

            $data[$field] = $value;
        }

        // Build INSERT
        $columns = array_keys($data);
        $placeholders = array_map(function ($col) {
            return ":{$col}";
        }, $columns);

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        try {
            $db->query($sql, $data);
            $id = $db->lastInsertId();
            Logger::audit('CREATE_MASTER', $table, "Created {$config['label']}: ID {$id}");
            Session::flash('success', $config['label'] . ' berhasil ditambahkan');
        } catch (Exception $e) {
            Session::flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        $this->redirect('mastertable/list?table=' . $table);
    }

    /**
     * Edit - Show edit form
     */
    public function edit()
    {
        $table = $this->get('table');
        $id = $this->get('id');
        $config = $this->getTableConfig($table);

        $db = Database::getInstance();
        $record = $db->query("SELECT * FROM {$table} WHERE id = :id", ['id' => $id])->fetch();

        if (!$record) {
            Session::flash('error', 'Data tidak ditemukan');
            $this->redirect('mastertable/list?table=' . $table);
        }

        $data = [
            'title' => 'Edit ' . $config['label'],
            'table' => $table,
            'config' => $config,
            'record' => $record,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('admin/master/form', $data);
    }

    /**
     * Update - Save edited record
     */
    public function update()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('mastertable/index');
        }

        $table = $this->post('table');
        $id = $this->post('id');
        $config = $this->getTableConfig($table);

        $db = Database::getInstance();
        $data = [];

        foreach ($config['columns'] as $field => $fieldConfig) {
            if ($fieldConfig['type'] === 'hidden') {
                // Preserve existing is_system value
                $existing = $db->query("SELECT {$field} FROM {$table} WHERE id = :id", ['id' => $id])->fetch();
                $data[$field] = $existing[$field] ?? ($fieldConfig['default'] ?? 0);
                continue;
            }

            $value = $this->post($field);

            if ($fieldConfig['type'] === 'slug') {
                $sourceField = $fieldConfig['source'] ?? 'name';
                $value = $this->generateSlug($this->post($sourceField));
                // Ensure unique (exclude current)
                $existing = $db->query("SELECT id FROM {$table} WHERE slug = :slug AND id != :id", ['slug' => $value, 'id' => $id])->fetch();
                if ($existing) {
                    $value .= '-' . rand(100, 999);
                }
            }

            if ($fieldConfig['type'] === 'boolean') {
                $value = $value ? 1 : 0;
            }

            if ($value === null || $value === '') {
                if (isset($fieldConfig['default'])) {
                    $value = $fieldConfig['default'];
                } elseif (!$fieldConfig['required']) {
                    $value = null;
                }
            }

            $data[$field] = $value;
        }

        // Build UPDATE
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $data['id'] = $id;

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE id = :id";

        try {
            $db->query($sql, $data);
            Logger::audit('UPDATE_MASTER', $table, "Updated {$config['label']}: ID {$id}");
            Session::flash('success', $config['label'] . ' berhasil diperbarui');
        } catch (Exception $e) {
            Session::flash('error', 'Gagal memperbarui: ' . $e->getMessage());
        }

        $this->redirect('mastertable/list?table=' . $table);
    }

    /**
     * Delete - Delete record (with system record protection)
     */
    public function delete()
    {
        if (!$this->isAjax()) {
            $this->redirect('mastertable/index');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $table = $this->post('table');
        $id = $this->post('id');
        $config = $this->getTableConfig($table);

        $db = Database::getInstance();
        $record = $db->query("SELECT * FROM {$table} WHERE id = :id", ['id' => $id])->fetch();

        if (!$record) {
            $this->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        // System records cannot be deleted, only deactivated
        if (isset($record['is_system']) && $record['is_system'] == 1) {
            // Deactivate instead of delete
            $db->query("UPDATE {$table} SET is_active = 0 WHERE id = :id", ['id' => $id]);
            Logger::audit('DEACTIVATE_MASTER', $table, "Deactivated system {$config['label']}: ID {$id}");
            $this->json([
                'status' => 'success',
                'message' => 'Record sistem tidak dapat dihapus, dinonaktifkan instead',
                'action' => 'deactivated'
            ]);
        }

        try {
            // Check if record is referenced by other tables
            $inUse = $this->checkInUse($table, $id);
            if ($inUse) {
                // Deactivate instead of delete
                if (isset($config['columns']['is_active'])) {
                    $db->query("UPDATE {$table} SET is_active = 0 WHERE id = :id", ['id' => $id]);
                    Logger::audit('DEACTIVATE_MASTER', $table, "Deactivated in-use {$config['label']}: ID {$id}");
                    $this->json([
                        'status' => 'success',
                        'message' => 'Data sedang digunakan, dinonaktifkan instead',
                        'action' => 'deactivated'
                    ]);
                } else {
                    $this->json([
                        'status' => 'error',
                        'message' => 'Data tidak dapat dihapus karena sedang digunakan'
                    ], 400);
                }
            }

            $db->query("DELETE FROM {$table} WHERE id = :id", ['id' => $id]);
            Logger::audit('DELETE_MASTER', $table, "Deleted {$config['label']}: ID {$id}");
            $this->json(['status' => 'success', 'message' => 'Data berhasil dihapus']);
        } catch (Exception $e) {
            $this->json(['status' => 'error', 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle active status (AJAX)
     */
    public function toggleActive()
    {
        if (!$this->isAjax()) {
            $this->redirect('mastertable/index');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $table = $this->post('table');
        $id = $this->post('id');
        $config = $this->getTableConfig($table);

        if (!isset($config['columns']['is_active'])) {
            $this->json(['status' => 'error', 'message' => 'Tabel ini tidak mendukung toggle active'], 400);
        }

        $db = Database::getInstance();
        $record = $db->query("SELECT is_active FROM {$table} WHERE id = :id", ['id' => $id])->fetch();

        if (!$record) {
            $this->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }

        $newStatus = $record['is_active'] ? 0 : 1;
        $db->query("UPDATE {$table} SET is_active = :status WHERE id = :id", ['status' => $newStatus, 'id' => $id]);

        Logger::audit('TOGGLE_MASTER', $table, "Toggled active for {$config['label']}: ID {$id} → {$newStatus}");

        $this->json([
            'status' => 'success',
            'message' => $newStatus ? 'Diaktifkan' : 'Dinonaktifkan',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Check if a record is referenced by other tables
     */
    private function checkInUse($table, $id)
    {
        $db = Database::getInstance();

        $references = [
            'destination_categories' => ['destinations' => 'category_id'],
            'product_categories' => ['products' => 'category_id'],
            'languages' => ['guide_languages' => 'language_id'],
            'specializations' => ['guide_specializations' => 'specialization_id'],
            'facilities' => ['entity_facilities' => 'facility_id'],
            'hotel_types' => ['hotels' => 'hotel_type_id'],
            'restaurant_types' => ['restaurants' => 'restaurant_type_id'],
            'event_categories' => ['events' => 'event_category_id'],
            'ticket_types' => ['tickets' => 'ticket_type_id'],
            'dietary_preferences' => ['user_dietary_map' => 'dietary_id'],
        ];

        if (!isset($references[$table])) {
            return false;
        }

        foreach ($references[$table] as $refTable => $refColumn) {
            $count = $db->query("SELECT COUNT(*) as c FROM {$refTable} WHERE {$refColumn} = :id", ['id' => $id])->fetch()['c'];
            if ($count > 0) {
                return true;
            }
        }

        return false;
    }
}
