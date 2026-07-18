<?php

/**
 * MyWisata Application - Variant Model
 *
 * Handles item variants for products, events, and restaurant menus.
 * Supports different tiers (VIP, Regular), sizes, colors, packages, etc.
 */
class Variant extends Model
{
    protected $table = 'item_variants';

    /**
     * Get variants by parent
     *
     * @param string $parentType product|event|restaurant_menu
     * @param int $parentId
     * @return array
     */
    public function getVariants($parentType, $parentId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parent_type = :ptype AND parent_id = :pid AND is_active = 1 
                ORDER BY sort_order ASC, price ASC";
        return $this->db->query($sql, ['ptype' => $parentType, 'pid' => $parentId])->fetchAll();
    }

    /**
     * Get a single variant by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND is_active = 1";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Create a variant
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (parent_type, parent_id, name, description, price, discount_price, stock, sku, attributes, is_active, sort_order, created_at)
                VALUES 
                (:parent_type, :parent_id, :name, :description, :price, :discount_price, :stock, :sku, :attributes, 1, :sort_order, NOW())";
        $this->db->query($sql, [
            'parent_type' => $data['parent_type'],
            'parent_id' => $data['parent_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'sku' => $data['sku'] ?? null,
            'attributes' => $data['attributes'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Update a variant
     */
    public function updateVariant($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, description = :description, price = :price, 
                    discount_price = :discount_price, stock = :stock, sku = :sku,
                    attributes = :attributes, sort_order = :sort_order, is_active = :is_active
                WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'discount_price' => $data['discount_price'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'sku' => $data['sku'] ?? null,
            'attributes' => $data['attributes'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
        ]);
    }

    /**
     * Delete a variant (soft delete)
     */
    public function deleteVariant($id)
    {
        $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Reduce variant stock
     */
    public function reduceStock($variantId, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock = stock - :qty WHERE id = :id AND stock >= :qty_check AND is_active = 1";
        return $this->db->query($sql, ['qty' => $quantity, 'id' => $variantId, 'qty_check' => $quantity]);
    }

    /**
     * Get effective price (discount or regular)
     */
    public function getEffectivePrice($variant)
    {
        if (!empty($variant['discount_price']) && $variant['discount_price'] > 0) {
            return $variant['discount_price'];
        }
        return $variant['price'];
    }

    /**
     * Parse attributes JSON
     */
    public function getAttributes($variant)
    {
        if (empty($variant['attributes'])) {
            return [];
        }
        $attrs = json_decode($variant['attributes'], true);
        return is_array($attrs) ? $attrs : [];
    }

    /**
     * Format attributes as badges
     */
    public function formatAttributes($variant)
    {
        $attrs = $this->getAttributes($variant);
        $badges = [];
        $labels = [
            'size' => 'Ukuran',
            'color' => 'Warna',
            'material' => 'Bahan',
            'weight' => 'Berat',
            'roast' => 'Roast',
            'tier' => 'Tingkat',
            'access' => 'Akses',
            'includes' => 'Termasuk',
            'portion' => 'Porsi',
        ];
        foreach ($attrs as $key => $val) {
            $label = $labels[$key] ?? ucfirst($key);
            $badges[] = "<span class=\"badge bg-light text-dark border me-1\"><strong>{$label}:</strong> " . htmlspecialchars($val) . "</span>";
        }
        return implode('', $badges);
    }

    /**
     * Get variant tier badge color
     */
    public function getTierBadge($variant)
    {
        $attrs = $this->getAttributes($variant);
        $tier = $attrs['tier'] ?? '';
        switch ($tier) {
            case 'vvip':
                return '<span class="badge bg-danger"><i class="fas fa-crown me-1"></i>VVIP</span>';
            case 'vip':
                return '<span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>VIP</span>';
            case 'regular':
            case 'reguler':
                return '<span class="badge bg-info text-dark"><i class="fas fa-ticket-alt me-1"></i>Regular</span>';
            default:
                $portion = $attrs['portion'] ?? '';
                if (str_contains($portion, 'vip') || str_contains($portion, 'combo')) {
                    return '<span class="badge bg-warning text-dark"><i class="fas fa-utensils me-1"></i>Paket</span>';
                }
                return '';
        }
    }
}
