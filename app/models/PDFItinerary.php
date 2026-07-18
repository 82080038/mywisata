<?php
namespace App\Models;

use App\Core\Model;

class PDFItinerary extends Model {
    protected $table = 'pdf_itineraries';
    protected $primaryKey = 'id';
    
    /**
     * Get PDFs by itinerary
     */
    public function getByItineraryId($itineraryId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE itinerary_id = ? AND is_active = 1 
             ORDER BY generated_at DESC",
            [$itineraryId]
        )->fetchAll();
    }
    
    /**
     * Create PDF record
     */
    public function create($data) {
        $data['generated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Increment download count
     */
    public function incrementDownload($id) {
        return $this->db->query(
            "UPDATE {$this->table} 
             SET download_count = download_count + 1 
             WHERE id = ?",
            [$id]
        );
    }
}
