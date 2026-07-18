<?php
namespace App\Models;

use App\Core\Model;

class SharedWishlistItem extends Model {
    protected $table = 'shared_wishlist_items';
    protected $primaryKey = 'id';
    
    /**
     * Get items by wishlist
     */
    public function getByWishlistId($wishlistId) {
        return $this->db->query(
            "SELECT swi.*, d.name as destination_name, d.image as destination_image, d.price as destination_price
             FROM {$this->table} swi
             JOIN destinations d ON swi.destination_id = d.id
             WHERE swi.wishlist_id = ? 
             ORDER BY swi.priority DESC, swi.created_at ASC",
            [$wishlistId]
        )->fetchAll();
    }
    
    /**
     * Add item to wishlist
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Remove item
     */
    public function remove($wishlistId, $destinationId) {
        return $this->db->query(
            "DELETE FROM {$this->table} 
             WHERE wishlist_id = ? AND destination_id = ?",
            [$wishlistId, $destinationId]
        );
    }
    
    /**
     * Update item
     */
    public function update($id, $data) {
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
