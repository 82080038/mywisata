<?php

/**
 * MyWisata Application - Product Controller
 *
 * Handles souvenir and local product listing, detail, and cart integration.
 *
 * @version 1.0.0
 *
 * @since 2026-07-19
 */
class ProductController extends Controller
{
    /**
     * Index - List all products with filters
     */
    public function index()
    {
        $productModel = new Product();

        $filters = [
            'search' => $this->get('search', ''),
            'category_id' => $this->get('category', ''),
            'region' => $this->get('region', ''),
            'min_price' => $this->get('min_price', ''),
            'max_price' => $this->get('max_price', ''),
            'sort' => $this->get('sort', 'featured'),
            'limit' => 12,
            'offset' => ($this->get('page', 1) - 1) * 12,
        ];

        $products = $productModel->getAllWithFilters($filters);
        $total = $productModel->countWithFilters($filters);
        $categories = $productModel->getCategories();
        $regions = $productModel->getRegions();
        $featured = $productModel->getFeatured(4);

        $data = [
            'title' => 'Souvenir & Khasanah Lokal - MyWisata',
            'products' => $products,
            'categories' => $categories,
            'regions' => $regions,
            'featured' => $featured,
            'total' => $total,
            'page' => $this->get('page', 1),
            'totalPages' => ceil($total / 12),
            'filters' => $filters,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('products/index', $data);
    }

    /**
     * Detail - Show single product
     */
    public function detail($id = null)
    {
        if (!$id) {
            $id = $this->get('id');
        }

        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan');
            $this->redirect('products');
        }

        $images = $productModel->getImages($id);
        $related = $productModel->getAllWithFilters([
            'category_id' => $product['category_id'],
            'limit' => 4,
            'offset' => 0,
        ]);

        $variantModel = new Variant();
        $variants = $variantModel->getVariants('product', $id);

        $data = [
            'title' => $product['name'] . ' - MyWisata',
            'product' => $product,
            'images' => $images,
            'related' => $related,
            'variants' => $variants,
            'variantModel' => $variantModel,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('products/detail', $data);
    }

    /**
     * Add to cart - AJAX
     */
    public function addToCart()
    {
        if (!$this->isAjax()) {
            $this->redirect('products');
        }

        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $productId = $this->post('product_id');
        $quantity = (int) $this->post('quantity', 1);
        $variantId = (int) $this->post('variant_id', 0);

        $productModel = new Product();
        $product = $productModel->findById($productId);

        if (!$product) {
            $this->json(['status' => 'error', 'message' => 'Produk tidak ditemukan'], 404);
        }

        // Check variant if selected
        $variantName = '';
        $effectivePrice = $product['discount_price'] > 0 ? $product['discount_price'] : $product['price'];
        $effectiveStock = $product['stock'];

        if ($variantId > 0) {
            $variantModel = new Variant();
            $variant = $variantModel->findById($variantId);
            if (!$variant) {
                $this->json(['status' => 'error', 'message' => 'Varian tidak ditemukan'], 404);
            }
            if ($variant['parent_id'] != $productId || $variant['parent_type'] !== 'product') {
                $this->json(['status' => 'error', 'message' => 'Varian tidak sesuai dengan produk'], 400);
            }
            if ($variant['stock'] < $quantity) {
                $this->json(['status' => 'error', 'message' => 'Stok varian tidak mencukupi. Tersedia: ' . $variant['stock'] . ' pcs'], 400);
            }
            if ($variant['stock'] == 0) {
                $this->json(['status' => 'error', 'message' => 'Varian ini sudah habis terjual'], 400);
            }
            $effectivePrice = $variant['discount_price'] > 0 ? $variant['discount_price'] : $variant['price'];
            $effectiveStock = $variant['stock'];
            $variantName = ' - ' . $variant['name'];
        } else {
            if ($product['stock'] < $quantity) {
                $this->json(['status' => 'error', 'message' => 'Stok tidak mencukupi. Tersedia: ' . $product['stock'] . ' pcs'], 400);
            }
            if ($product['stock'] == 0) {
                $this->json(['status' => 'error', 'message' => 'Produk sudah habis terjual'], 400);
            }
        }

        $data = [
            'name' => $product['name'] . $variantName,
            'price' => $effectivePrice,
            'image' => $product['main_image'],
            'variant_id' => $variantId,
        ];

        Cart::add('product', $productId, $data, $quantity);

        $this->json([
            'status' => 'success',
            'message' => 'Produk ditambahkan ke keranjang',
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total(),
        ]);
    }

    /**
     * Search - AJAX product search
     */
    public function search()
    {
        if (!$this->isAjax()) {
            $this->redirect('products');
        }

        $query = $this->get('q', '');
        $productModel = new Product();
        $products = $productModel->getAllWithFilters(['search' => $query, 'limit' => 10]);

        $results = [];
        foreach ($products as $p) {
            $results[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'region' => $p['region'],
                'image' => $p['main_image'],
                'url' => BASE_URL . 'products/detail/' . $p['id'],
            ];
        }

        $this->json(['status' => 'success', 'results' => $results]);
    }
}
