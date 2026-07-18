<?php
namespace App\Models;

use App\Core\Model;

class ImportedItinerary extends Model {
    protected $table = 'imported_itineraries';
    protected $primaryKey = 'id';
    
    /**
     * Get imports by user
     */
    public function getByUserId($userId, $limit = 20) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT ?",
            [$userId, $limit]
        )->fetchAll();
    }
    
    /**
     * Create import record
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update import status
     */
    public function updateStatus($id, $status, $parsedData = null, $errorMessage = null) {
        $data = ['import_status' => $status];
        if ($parsedData !== null) {
            $data['parsed_data'] = $parsedData;
        }
        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
}
