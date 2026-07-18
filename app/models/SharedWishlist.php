<?php
namespace App\Models;

use App\Core\Model;

class SharedWishlist extends Model {
    protected $table = 'shared_wishlists';
    protected $primaryKey = 'id';
    
    /**
     * Get wishlists by user
     */
    public function getByUserId($userId, $limit = 20) {
        return $this->db->query(
            "SELECT sw.*, 
                    (SELECT COUNT(*) FROM shared_wishlist_items WHERE wishlist_id = sw.id) as item_count
             FROM {$this->table} sw
             WHERE sw.created_by = ? 
             ORDER BY sw.created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Get wishlist by ID
     */
    public function getById($wishlistId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE id = ?",
            [$wishlistId]
        )->fetch();
    }
    
    /**
     * Create wishlist
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update wishlist
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
