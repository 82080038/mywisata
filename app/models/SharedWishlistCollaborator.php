<?php
namespace App\Models;

use App\Core\Model;

class SharedWishlistCollaborator extends Model {
    protected $table = 'shared_wishlist_collaborators';
    protected $primaryKey = 'id';
    
    /**
     * Get collaborators by wishlist
     */
    public function getByWishlistId($wishlistId) {
        return $this->db->query(
            "SELECT swc.*, u.name, u.email 
             FROM {$this->table} swc
             JOIN users u ON swc.user_id = u.id
             WHERE swc.wishlist_id = ? 
             ORDER BY swc.permission DESC, swc.created_at ASC",
            [$wishlistId]
        )->fetchAll();
    }
    
    /**
     * Get wishlists by user (as collaborator)
     */
    public function getWishlistsByUserId($userId) {
        return $this->db->query(
            "SELECT swc.*, sw.wishlist_name, sw.created_by
             FROM {$this->table} swc
             JOIN shared_wishlists sw ON swc.wishlist_id = sw.id
             WHERE swc.user_id = ? AND swc.status = 'accepted'
             ORDER BY sw.created_at DESC",
            [$userId]
        )->fetchAll();
    }
    
    /**
     * Add collaborator
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update status
     */
    public function updateStatus($wishlistId, $userId, $status) {
        $data = ['status' => $status];
        return $this->db->update($this->table, $data, "wishlist_id = ? AND user_id = ?", [$wishlistId, $userId]);
    }
    
    /**
     * Remove collaborator
     */
    public function remove($wishlistId, $userId) {
        return $this->db->query(
            "DELETE FROM {$this->table} 
             WHERE wishlist_id = ? AND user_id = ?",
            [$wishlistId, $userId]
        );
    }
}
