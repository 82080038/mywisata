<?php
/**
 * MyWisata Application - Pilgrimage Package Itinerary Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PilgrimagePackageItinerary extends Model {
    
    protected $table = 'pilgrimage_package_itinerary';
    
    /**
     * Get by package ID
     */
    public function getByPackageId($packageId) {
        $sql = "SELECT * FROM {$this->table} WHERE package_id = :package_id ORDER BY day_number, time_order";
        return $this->query($sql, ['package_id' => $packageId]);
    }
    
    /**
     * Create itinerary item
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (package_id, day_number, time_order, activity_type, activity_name, description, time, location, is_mandatory) 
                VALUES (:package_id, :day_number, :time_order, :activity_type, :activity_name, :description, :time, :location, :is_mandatory)";
        return $this->execute($sql, $data);
    }
}
