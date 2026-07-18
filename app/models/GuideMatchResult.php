<?php
namespace App\Models;

use App\Core\Model;

class GuideMatchResult extends Model {
    protected $table = 'guide_match_results';
    protected $primaryKey = 'id';
    
    /**
     * Get match results by booking
     */
    public function getByBookingId($bookingId) {
        return $this->db->query(
            "SELECT * FROM {$this->table} 
             WHERE booking_id = ? 
             ORDER BY match_score DESC",
            [$bookingId]
        )->fetchAll();
    }
    
    /**
     * Create match result
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update acceptance status
     */
    public function updateAcceptance($id, $isAccepted) {
        $data = ['is_accepted' => $isAccepted];
        return $this->db->update($this->table, $data, "id = ?", [$id]);
    }
    
    /**
     * Get match statistics by guide
     */
    public function getGuideStatistics($guideId) {
        $total = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE matched_guide_id = ?", [$guideId])->fetch()['count'];
        $accepted = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE matched_guide_id = ? AND is_accepted = 1", [$guideId])->fetch()['count'];
        $avgScore = $this->db->query("SELECT AVG(match_score) as avg FROM {$this->table} WHERE matched_guide_id = ?", [$guideId])->fetch()['avg'];
        
        return [
            'total' => $total,
            'accepted' => $accepted,
            'acceptance_rate' => $total > 0 ? ($accepted / $total) * 100 : 0,
            'avg_score' => $avgScore ?? 0
        ];
    }
}
