<?php

/**
 * MyWisata Application - Product Model
 *
 * Handles souvenir and local product database operations.
 *
 * @version 1.0.0
 *
 * @since 2026-07-19
 */
class Product extends Model
{
    protected $table = 'products';

    /**
     * Get all products with filters
     *
     * @param array $filters
     * @return array
     */
    public function getAllWithFilters($filters = [])
    {
        $where = ['p.is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['region'])) {
            $where[] = "p.region LIKE :region";
            $params['region'] = "%{$filters['region']}%";
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search_name OR p.description LIKE :search_desc OR p.region LIKE :search_region)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
            $params['search_region'] = "%{$filters['search']}%";
        }

        if (!empty($filters['destination_id'])) {
            $where[] = "p.destination_id = :destination_id";
            $params['destination_id'] = $filters['destination_id'];
        }

        if (!empty($filters['featured'])) {
            $where[] = "p.is_featured = 1";
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $where[] = "p.price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $where[] = "p.price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        $orderBy = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'p.price ASC';
                    break;
                case 'price_high':
                    $orderBy = 'p.price DESC';
                    break;
                case 'name':
                    $orderBy = 'p.name ASC';
                    break;
                case 'featured':
                    $orderBy = 'p.is_featured DESC, p.created_at DESC';
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 20;
        $offset = $filters['offset'] ?? 0;

        $sql = "SELECT p.*, pc.name as category_name, pc.icon as category_icon,
                d.name as destination_name
                FROM {$this->table} p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN destinations d ON p.destination_id = d.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get product by ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, pc.name as category_name, pc.icon as category_icon,
                d.name as destination_name
                FROM {$this->table} p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN destinations d ON p.destination_id = d.id
                WHERE p.id = :id";

        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Get product images
     *
     * @param int $productId
     * @return array
     */
    public function getImages($productId)
    {
        $sql = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC";
        return $this->db->query($sql, ['product_id' => $productId])->fetchAll();
    }

    /**
     * Get featured products
     *
     * @param int $limit
     * @return array
     */
    public function getFeatured($limit = 8)
    {
        $sql = "SELECT p.*, pc.name as category_name
                FROM {$this->table} p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.is_active = 1 AND p.is_featured = 1
                ORDER BY p.created_at DESC
                LIMIT :limit";

        return $this->db->query($sql, ['limit' => $limit])->fetchAll();
    }

    /**
     * Get products by destination
     *
     * @param int $destinationId
     * @param int $limit
     * @return array
     */
    public function getByDestination($destinationId, $limit = 4)
    {
        $sql = "SELECT p.*, pc.name as category_name
                FROM {$this->table} p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE p.is_active = 1 AND p.destination_id = :destination_id
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT :limit";

        return $this->db->query($sql, ['destination_id' => $destinationId, 'limit' => $limit])->fetchAll();
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function getCategories()
    {
        $sql = "SELECT pc.*, COUNT(p.id) as product_count
                FROM product_categories pc
                LEFT JOIN products p ON pc.id = p.category_id AND p.is_active = 1
                WHERE pc.is_active = 1
                GROUP BY pc.id
                ORDER BY pc.name";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get distinct regions
     *
     * @return array
     */
    public function getRegions()
    {
        $sql = "SELECT DISTINCT region FROM products WHERE region IS NOT NULL AND region != '' AND is_active = 1 ORDER BY region";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Reduce stock after purchase
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function reduceStock($productId, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock - :qty WHERE id = :id AND stock >= :qty_check";
        return $this->db->query($sql, ['qty' => $quantity, 'id' => $productId, 'qty_check' => $quantity]);
    }

    /**
     * Create product
     *
     * @param array $data
     * @return int
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (name, slug, description, short_desc, category_id, destination_id, price, discount_price, stock, sku, main_image, is_active, is_featured, region, created_at)
                VALUES 
                (:name, :slug, :description, :short_desc, :category_id, :destination_id, :price, :discount_price, :stock, :sku, :main_image, :is_active, :is_featured, :region, NOW())";

        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }

    /**
     * Update product
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET 
                name = :name, description = :description, short_desc = :short_desc,
                category_id = :category_id, destination_id = :destination_id,
                price = :price, discount_price = :discount_price, stock = :stock,
                sku = :sku, main_image = :main_image, is_active = :is_active,
                is_featured = :is_featured, region = :region, updated_at = NOW()
                WHERE id = :id";

        return $this->db->query($sql, $data);
    }

    /**
     * Delete product
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->db->query("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);
    }

    /**
     * Count products with filters
     *
     * @param array $filters
     * @return int
     */
    public function countWithFilters($filters = [])
    {
        $where = ['is_active = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }

        if (!empty($filters['region'])) {
            $where[] = "region LIKE :region";
            $params['region'] = "%{$filters['region']}%";
        }

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search_name OR description LIKE :search_desc)";
            $params['search_name'] = "%{$filters['search']}%";
            $params['search_desc'] = "%{$filters['search']}%";
        }

        $whereClause = implode(' AND ', $where);
        return (int) $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE {$whereClause}", $params)->fetch()['count'];
    }
}
