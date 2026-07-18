<?php
/**
 * MyWisata Application - Prayer Times Cache Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PrayerTimesCache extends Model {
    
    protected $table = 'prayer_times_cache';
    
    /**
     * Get by location and date
     */
    public function getByLocationAndDate($lat, $lng, $date) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE location_lat = :lat AND location_lng = :lng AND date = :date 
                LIMIT 1";
        return $this->query($sql, ['lat' => $lat, 'lng' => $lng, 'date' => $date])[0] ?? null;
    }
    
    /**
     * Create cache entry
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (location_lat, location_lng, city_name, country_code, date, fajr, dhuhr, asr, maghrib, isha) 
                VALUES (:location_lat, :location_lng, :city_name, :country_code, :date, :fajr, :dhuhr, :asr, :maghrib, :isha)";
        return $this->execute($sql, $data);
    }
}
