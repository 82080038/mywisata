<?php

/**
 * MyWisata Application - Merchant Controller
 *
 * Handles souvenir seller/merchant dashboard and product management.
 *
 * @version 1.0.0
 *
 * @since 2026-07-19
 */
class MerchantController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Middleware::requireRole('merchant');
    }

    /**
     * Dashboard - Merchant overview
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        $db = Database::getInstance();

        $stats = [
            'total_products' => $db->query("SELECT COUNT(*) as count FROM products WHERE seller_id = :sid", ['sid' => $userId])->fetch()['count'],
            'active_products' => $db->query("SELECT COUNT(*) as count FROM products WHERE seller_id = :sid AND is_active = 1", ['sid' => $userId])->fetch()['count'],
            'total_orders' => $db->query("SELECT COUNT(*) as count FROM product_order_items poi INNER JOIN product_orders po ON poi.order_id = po.id INNER JOIN products p ON poi.product_id = p.id WHERE p.seller_id = :sid", ['sid' => $userId])->fetch()['count'],
            'total_revenue' => $db->query("SELECT COALESCE(SUM(poi.subtotal), 0) as total FROM product_order_items poi INNER JOIN product_orders po ON poi.order_id = po.id INNER JOIN products p ON poi.product_id = p.id WHERE p.seller_id = :sid AND po.payment_status = 'paid'", ['sid' => $userId])->fetch()['total'],
        ];

        $recentOrders = $db->query(
            "SELECT po.order_code, po.created_at, po.status, po.payment_status, poi.product_id, poi.quantity, poi.subtotal, p.name as product_name, u.name as buyer_name
             FROM product_order_items poi
             INNER JOIN product_orders po ON poi.order_id = po.id
             INNER JOIN products p ON poi.product_id = p.id
             INNER JOIN users u ON po.user_id = u.id
             WHERE p.seller_id = :sid
             ORDER BY po.created_at DESC
             LIMIT 5",
            ['sid' => $userId]
        )->fetchAll();

        $topProducts = $db->query(
            "SELECT p.name, p.main_image, p.stock, p.price, p.is_active,
                    COALESCE(SUM(poi.quantity), 0) as sold_count
             FROM products p
             LEFT JOIN product_order_items poi ON p.id = poi.product_id
             WHERE p.seller_id = :sid
             GROUP BY p.id
             ORDER BY sold_count DESC
             LIMIT 5",
            ['sid' => $userId]
        )->fetchAll();

        $data = [
            'title' => 'Dashboard Merchant - MyWisata',
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
        ];

        $this->view('merchant/dashboard', $data);
    }

    /**
     * List merchant's products
     */
    public function products()
    {
        $userId = Session::get('user_id');
        $productModel = new Product();

        $products = $productModel->getAllWithFilters([
            'limit' => 50,
            'offset' => 0,
        ]);

        // Filter to only this merchant's products
        $myProducts = array_filter($products, function($p) use ($userId) {
            return $p['seller_id'] == $userId;
        });

        $categories = $productModel->getCategories();

        $data = [
            'title' => 'Produk Saya - MyWisata',
            'products' => $myProducts,
            'categories' => $categories,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('merchant/products', $data);
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

        $this->view('merchant/create_product', $data);
    }

    /**
     * Store product
     */
    public function storeProduct()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('merchant/createProduct');
        }

        $userId = Session::get('user_id');
        $imageFile = '';

        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/products');
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('merchant/createProduct');
            }
        }

        $name = $this->post('name');
        $productModel = new Product();
        $productId = $productModel->create([
            'name' => $name,
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . rand(100, 999),
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
            'is_featured' => 0,
            'region' => $this->post('region', ''),
            'seller_id' => $userId,
        ]);

        // Manually set seller_id since the create method doesn't include it
        $db = Database::getInstance();
        $db->query("UPDATE products SET seller_id = :sid WHERE id = :id", ['sid' => $userId, 'id' => $productId]);

        // Save additional images
        if (isset($_FILES['images'])) {
            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $imgName = FileUpload::upload([
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i],
                        ], APP_ROOT . '/public/uploads/products');

                        $db->query("INSERT INTO product_images (product_id, image, is_primary) VALUES (:pid, :img, 0)", [
                            'pid' => $productId,
                            'img' => $imgName,
                        ]);
                    } catch (Exception $e) {
                        // Skip failed uploads
                    }
                }
            }
        }

        Logger::audit('MERCHANT_CREATE_PRODUCT', 'products', "Merchant created product: {$name}");
        Session::flash('success', 'Produk berhasil dibuat');
        $this->redirect('merchant/products');
    }

    /**
     * Edit product form
     */
    public function editProduct()
    {
        $userId = Session::get('user_id');
        $id = $this->get('id');
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product || $product['seller_id'] != $userId) {
            Session::flash('error', 'Produk tidak ditemukan atau bukan milik Anda');
            $this->redirect('merchant/products');
        }

        $categories = $productModel->getCategories();
        $images = $productModel->getImages($id);
        $db = Database::getInstance();
        $destinations = $db->query("SELECT id, name FROM destinations WHERE is_active = 1 ORDER BY name")->fetchAll();

        $data = [
            'title' => 'Edit Produk - MyWisata',
            'product' => $product,
            'categories' => $categories,
            'destinations' => $destinations,
            'images' => $images,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('merchant/edit_product', $data);
    }

    /**
     * Update product
     */
    public function updateProduct()
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'CSRF token mismatch');
            $this->redirect('merchant/products');
        }

        $userId = Session::get('user_id');
        $id = $this->post('id');
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product || $product['seller_id'] != $userId) {
            Session::flash('error', 'Akses ditolak');
            $this->redirect('merchant/products');
        }

        $imageFile = $this->post('existing_image', '');

        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            try {
                $imageFile = FileUpload::upload($_FILES['main_image'], APP_ROOT . '/public/uploads/products');
                if (!empty($this->post('existing_image'))) {
                    FileUpload::delete('products/' . $this->post('existing_image'));
                }
            } catch (Exception $e) {
                Session::flash('error', $e->getMessage());
                $this->redirect('merchant/editProduct?id=' . $id);
            }
        }

        $name = $this->post('name');
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
            'is_featured' => $product['is_featured'],
            'region' => $this->post('region', ''),
        ]);

        // Save additional images
        if (isset($_FILES['images'])) {
            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    try {
                        $imgName = FileUpload::upload([
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i],
                        ], APP_ROOT . '/public/uploads/products');

                        $db = Database::getInstance();
                        $db->query("INSERT INTO product_images (product_id, image, is_primary) VALUES (:pid, :img, 0)", [
                            'pid' => $id,
                            'img' => $imgName,
                        ]);
                    } catch (Exception $e) {
                        // Skip failed uploads
                    }
                }
            }
        }

        Logger::audit('MERCHANT_UPDATE_PRODUCT', 'products', "Merchant updated product ID: {$id}");
        Session::flash('success', 'Produk berhasil diperbarui');
        $this->redirect('merchant/products');
    }

    /**
     * Delete product
     */
    public function deleteProduct()
    {
        if (!$this->isAjax()) {
            $this->redirect('merchant/products');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $userId = Session::get('user_id');
        $id = $this->post('id');
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product || $product['seller_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        if (!empty($product['main_image'])) {
            FileUpload::delete('products/' . $product['main_image']);
        }

        $productModel->delete($id);
        Logger::audit('MERCHANT_DELETE_PRODUCT', 'products', "Merchant deleted product ID: {$id}");
        $this->json(['status' => 'success', 'message' => 'Produk berhasil dihapus']);
    }

    /**
     * Delete product image
     */
    public function deleteImage()
    {
        if (!$this->isAjax()) {
            $this->redirect('merchant/products');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $userId = Session::get('user_id');
        $imageId = $this->post('image_id');
        $db = Database::getInstance();

        $image = $db->query("SELECT pi.*, p.seller_id FROM product_images pi INNER JOIN products p ON pi.product_id = p.id WHERE pi.id = :id", ['id' => $imageId])->fetch();

        if (!$image || $image['seller_id'] != $userId) {
            $this->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        FileUpload::delete('products/' . $image['image']);
        $db->query("DELETE FROM product_images WHERE id = :id", ['id' => $imageId]);
        $this->json(['status' => 'success', 'message' => 'Gambar dihapus']);
    }
}
