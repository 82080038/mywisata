<?php

/**
 * MyWisata Application - Cart Controller
 *
 * Handles multi-service shopping cart operations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class CartController extends Controller
{
    /**
     * Constructor - Require login for checkout
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index - Show cart page
     */
    public function index()
    {
        $cart = Cart::get();
        $summary = Cart::summary();

        $data = [
            'title' => 'Keranjang - MyWisata',
            'cart' => $cart,
            'summary' => $summary,
            'total' => Cart::total(),
            'count' => Cart::count(),
        ];

        $this->view('cart/index', $data);
    }

    /**
     * Add item to cart
     */
    public function add()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $type = $this->post('type');
        $itemId = $this->post('item_id');
        $quantity = $this->post('quantity', 1);
        $data = $this->post('data', []);

        if (empty($type) || empty($itemId)) {
            $this->json(['status' => 'error', 'message' => 'Type and item_id are required'], 400);
        }

        // Check availability before adding to cart
        $availModel = new Availability();
        $availCheck = $this->checkAvailability($availModel, $type, $itemId, $quantity, $data);
        if (!$availCheck['available']) {
            $this->json(['status' => 'error', 'message' => $availCheck['message']], 400);
        }

        // Get item details based on type
        $itemData = $this->getItemDetails($type, $itemId, $data);
        
        if (!$itemData) {
            $this->json(['status' => 'error', 'message' => 'Item not found'], 404);
        }

        // Merge with provided data
        $data = array_merge($data, $itemData);

        $success = Cart::add($type, $itemId, $data, $quantity);

        if ($success) {
            $this->json([
                'status' => 'success',
                'message' => 'Item added to cart',
                'cart_count' => Cart::count(),
                'cart_total' => Cart::total(),
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to add item to cart'], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove()
    {
        // Verify CSRF
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('cart');
        }

        $itemKey = $this->post('item_key');
        $type = $this->post('type');
        $itemId = $this->post('item_id');
        $data = $this->post('data', []);

        if ($itemKey) {
            $success = Cart::removeByKey($itemKey);
        } else {
            $success = Cart::remove($type, $itemId, $data);
        }

        if ($this->isAjax()) {
            if ($success) {
                $this->json([
                    'status' => 'success',
                    'message' => 'Item removed from cart',
                    'cart_count' => Cart::count(),
                    'cart_total' => Cart::total(),
                ]);
            } else {
                $this->json(['status' => 'error', 'message' => 'Item not found in cart'], 404);
            }
        } else {
            if ($success) {
                Session::flash('success', 'Item dihapus dari keranjang');
            } else {
                Session::flash('error', 'Item tidak ditemukan');
            }
            $this->redirect('cart');
        }
    }

    /**
     * Update item quantity
     */
    public function update()
    {
        if (!$this->isAjax()) {
            $this->redirect('cart');
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $type = $this->post('type');
        $itemId = $this->post('item_id');
        $quantity = (int) $this->post('quantity', 1);
        $data = $this->post('data', []);

        $success = Cart::updateQuantity($type, $itemId, $quantity, $data);

        if ($success) {
            $this->json([
                'status' => 'success',
                'message' => 'Cart updated',
                'cart_count' => Cart::count(),
                'cart_total' => Cart::total(),
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Failed to update cart'], 500);
        }
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        // Verify CSRF
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('cart');
        }

        Cart::clear();

        if ($this->isAjax()) {
            $this->json([
                'status' => 'success',
                'message' => 'Cart cleared',
                'cart_count' => 0,
                'cart_total' => 0,
            ]);
        } else {
            Session::flash('success', 'Keranjang dikosongkan');
            $this->redirect('cart');
        }
    }

    /**
     * Checkout - Initialize checkout process
     */
    public function checkout()
    {
        if (Cart::isEmpty()) {
            Session::flash('error', 'Keranjang kosong');
            $this->redirect('cart');
        }

        // Require login for checkout
        if (!Session::get('user_id')) {
            Session::flash('error', 'Silakan login terlebih dahulu');
            Session::set('redirect_after_login', 'cart/checkout');
            $this->redirect('auth/login');
        }

        $userId = Session::get('user_id');
        $cart = Cart::get();
        $summary = Cart::summary();
        $total = Cart::total();

        // Create transaction
        $transactionModel = new Transaction();
        $transactionData = [
            'transaction_code' => 'TX' . date('YmdHis') . rand(1000, 9999),
            'user_id' => $userId,
            'booking_id' => null,
            'type' => 'cart',
            'gross_amount' => $total,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'net_amount' => $total,
            'payment_method' => 'pending',
        ];

        $transactionId = $transactionModel->create($transactionData);

        // Save cart items to database
        $this->saveCartItems($transactionId, $cart);

        // Clear cart
        Cart::clear();

        Logger::audit('CART_CHECKOUT', 'carts', 
            "Cart checkout initiated", [], [
                'transaction_id' => $transactionId,
                'total' => $total,
            ]);

        // Redirect to payment
        $this->redirect('payments?transaction_id=' . $transactionId);
    }

    /**
     * Get item details from database
     *
     * @param string $type Item type
     * @param int $itemId Item ID
     * @param array $data Additional data
     * @return array|null Item details
     */
    private function getItemDetails($type, $itemId, $data)
    {
        switch ($type) {
            case 'destination':
                $model = new Destination();
                $item = $model->findById($itemId);
                if ($item) {
                    return [
                        'name' => $item['name'],
                        'price' => $item['entry_fee'] ?? 0,
                    ];
                }
                break;

            case 'hotel':
                $model = new Hotel();
                $item = $model->findById($itemId);
                if ($item) {
                    $price = $data['room_price'] ?? 0;
                    return [
                        'name' => $item['name'],
                        'price' => $price,
                    ];
                }
                break;

            case 'restaurant':
                $model = new Restaurant();
                $item = $model->findById($itemId);
                if ($item) {
                    return [
                        'name' => $item['name'],
                        'price' => 0, // Restaurant items vary
                    ];
                }
                break;

            case 'event':
                $model = new Event();
                $item = $model->findById($itemId);
                if ($item) {
                    return [
                        'name' => $item['title'],
                        'price' => $item['price'] ?? 0,
                    ];
                }
                break;

            case 'tour_guide':
                $model = new TourGuide();
                $item = $model->findById($itemId);
                if ($item) {
                    $duration = $data['duration_hours'] ?? 1;
                    $rate = $data['rate_type'] === 'hourly' ? $item['hourly_rate'] : $item['daily_rate'];
                    $price = $rate * $duration;
                    return [
                        'name' => 'Tour Guide: ' . ($item['name'] ?? 'Guide'),
                        'price' => $price,
                    ];
                }
                break;

            case 'product':
                $model = new Product();
                $item = $model->findById($itemId);
                if ($item) {
                    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
                    return [
                        'name' => $item['name'],
                        'price' => $price,
                        'image' => $item['main_image'] ?? '',
                    ];
                }
                break;
        }

        return null;
    }

    /**
     * Save cart items to database
     *
     * @param int $transactionId Transaction ID
     * @param array $cart Cart items
     * @return void
     */
    private function saveCartItems($transactionId, $cart)
    {
        $db = Database::getInstance();

        foreach ($cart as $item) {
            $sql = "INSERT INTO cart_items 
                    (transaction_id, item_type, item_id, quantity, price, subtotal, item_data, created_at)
                    VALUES 
                    (:transaction_id, :item_type, :item_id, :quantity, :price, :subtotal, :item_data, NOW())";

            $db->query($sql, [
                'transaction_id' => $transactionId,
                'item_type' => $item['type'],
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
                'item_data' => json_encode($item['data']),
            ]);
        }
    }

    /**
     * Get cart count (AJAX)
     */
    public function count()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        $this->json([
            'status' => 'success',
            'count' => Cart::count(),
            'total' => Cart::total(),
        ]);
    }

    /**
     * Check availability based on item type
     *
     * @param Availability $model
     * @param string $type
     * @param int $itemId
     * @param int $quantity
     * @param array $data
     * @return array
     */
    private function checkAvailability($model, $type, $itemId, $quantity, $data = [])
    {
        switch ($type) {
            case 'hotel':
                $checkIn = $data['check_in'] ?? date('Y-m-d');
                $checkOut = $data['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
                return $model->checkRoom($itemId, $checkIn, $checkOut, $quantity);

            case 'destination':
                $visitDate = $data['visit_date'] ?? date('Y-m-d');
                return $model->checkDestinationTicket($itemId, $visitDate, $quantity);

            case 'event':
                return $model->checkEventTicket($itemId, $quantity);

            case 'product':
                return $model->checkProduct($itemId, $quantity);

            case 'tour_guide':
                $bookingDate = $data['booking_date'] ?? date('Y-m-d');
                $startTime = $data['start_time'] ?? '09:00';
                $duration = $data['duration_hours'] ?? 4;
                return $model->checkGuide($itemId, $bookingDate, $startTime, $duration);

            case 'restaurant':
                $date = $data['date'] ?? date('Y-m-d');
                $time = $data['time'] ?? '12:00';
                return $model->checkTable($itemId, $date, $time, $quantity);

            default:
                return ['available' => true];
        }
    }
}
