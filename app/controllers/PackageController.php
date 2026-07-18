<?php

/**
 * MyWisata Application - Package Controller
 *
 * Handles tourism package listings and booking.
 *
 * @version 1.0.0
 * @since 2026-07-19
 */
class PackageController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index - List all packages
     */
    public function index()
    {
        $db = Database::getInstance();
        $search = $this->get('search');
        $sort = $this->get('sort', 'newest');

        $where = ["is_active = 1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(title LIKE :search OR description LIKE :search2)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }

        $orderBy = "created_at DESC";
        if ($sort === 'price_low') $orderBy = "price ASC";
        if ($sort === 'price_high') $orderBy = "price DESC";
        if ($sort === 'duration') $orderBy = "duration_days ASC";

        $sql = "SELECT * FROM tourism_packages WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy}";
        $packages = $db->query($sql, $params)->fetchAll();

        $data = [
            'title' => 'Paket Wisata - MyWisata',
            'packages' => $packages,
            'filters' => ['search' => $search, 'sort' => $sort],
        ];

        $this->view('packages/index', $data);
    }

    /**
     * Detail - Show package details
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }

        $db = Database::getInstance();
        $pkg = $db->query("SELECT * FROM tourism_packages WHERE id = :id AND is_active = 1", ['id' => $id])->fetch();

        if (!$pkg) {
            Session::flash('error', 'Paket tidak ditemukan');
            $this->redirect('packages');
        }

        // Get package items
        $items = $db->query(
            "SELECT pi.*, 
                    CASE 
                        WHEN pi.item_type = 'destination' THEN (SELECT name FROM destinations WHERE id = pi.item_id)
                        WHEN pi.item_type = 'hotel' THEN (SELECT name FROM hotels WHERE id = pi.item_id)
                        WHEN pi.item_type = 'restaurant' THEN (SELECT name FROM restaurants WHERE id = pi.item_id)
                        WHEN pi.item_type = 'event' THEN (SELECT title FROM events WHERE id = pi.item_id)
                        WHEN pi.item_type = 'guide' THEN (SELECT name FROM tour_guides WHERE id = pi.item_id)
                    END as item_name
             FROM package_items pi WHERE pi.package_id = :id ORDER BY pi.day_number, pi.sequence",
            ['id' => $id]
        )->fetchAll();

        // Group by day
        $itemsByDay = [];
        foreach ($items as $item) {
            $itemsByDay[$item['day_number']][] = $item;
        }

        $data = [
            'title' => $pkg['title'] . ' - Paket Wisata - MyWisata',
            'package' => $pkg,
            'itemsByDay' => $itemsByDay,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('packages/detail', $data);
    }

    /**
     * Book - Book a package
     */
    public function book()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('packages');
        }

        $userId = Session::get('user_id');
        if (!$userId) {
            Session::flash('error', 'Silakan login terlebih dahulu');
            $this->redirect('auth/login');
        }

        $packageId = $this->post('package_id');
        $travelers = (int)$this->post('travelers', 1);
        $startDate = $this->post('start_date');

        $db = Database::getInstance();
        $pkg = $db->query("SELECT * FROM tourism_packages WHERE id = :id", ['id' => $packageId])->fetch();

        if (!$pkg) {
            Session::flash('error', 'Paket tidak ditemukan');
            $this->redirect('packages');
        }

        $price = $pkg['discount_price'] ?: $pkg['price'];
        $totalAmount = $price * $travelers;

        // Create transaction
        $transactionModel = new Transaction();
        $transactionId = $transactionModel->create([
            'transaction_code' => 'PK' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'type' => 'package',
            'gross_amount' => $totalAmount,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'net_amount' => $totalAmount,
            'payment_method' => 'pending',
        ]);

        Logger::audit('BOOK_PACKAGE', 'transactions', "Booked package ID: {$packageId}, Transaction: {$transactionId}");

        Session::flash('success', 'Paket berhasil dipesan. Silakan lanjutkan pembayaran.');
        $this->redirect('payments?transaction_id=' . $transactionId);
    }
}
