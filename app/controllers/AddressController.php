<?php
/**
 * MyWisata Application - Address Controller
 * 
 * Handles address data API for cascading dropdowns.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-17
 */

class AddressController extends Controller {
    
    /**
     * Get all provinces
     */
    public function getProvinces() {
        try {
            $db = Database::getInstance('address');
            $sql = "SELECT id, code, name FROM provinces ORDER BY name ASC";
            $provinces = $db->query($sql)->fetchAll();
            
            $this->json([
                'status' => 'success',
                'data' => $provinces,
                'count' => count($provinces)
            ]);
        } catch (Exception $e) {
            Logger::error('Get provinces error', ['error' => $e->getMessage()]);
            $this->json(['status' => 'error', 'message' => 'Failed to fetch provinces: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get regencies by province
     */
    public function getRegencies() {
        try {
            $provinceId = $this->get('province_id');
            
            if (empty($provinceId)) {
                $this->json(['status' => 'error', 'message' => 'Province ID is required'], 400);
                return;
            }
            
            $db = Database::getInstance('address');
            $sql = "SELECT id, code, name, postal_code FROM regencies 
                    WHERE province_id = :province_id 
                    ORDER BY name ASC";
            $regencies = $db->query($sql, ['province_id' => $provinceId])->fetchAll();
            
            $this->json([
                'status' => 'success',
                'data' => $regencies
            ]);
        } catch (Exception $e) {
            Logger::error('Get regencies error', ['error' => $e->getMessage()]);
            $this->json(['status' => 'error', 'message' => 'Failed to fetch regencies'], 500);
        }
    }
    
    /**
     * Get districts by regency
     */
    public function getDistricts() {
        try {
            $regencyId = $this->get('regency_id');
            
            if (empty($regencyId)) {
                $this->json(['status' => 'error', 'message' => 'Regency ID is required'], 400);
                return;
            }
            
            $db = Database::getInstance('address');
            $sql = "SELECT id, code, name FROM districts 
                    WHERE regency_id = :regency_id 
                    ORDER BY name ASC";
            $districts = $db->query($sql, ['regency_id' => $regencyId])->fetchAll();
            
            $this->json([
                'status' => 'success',
                'data' => $districts
            ]);
        } catch (Exception $e) {
            Logger::error('Get districts error', ['error' => $e->getMessage()]);
            $this->json(['status' => 'error', 'message' => 'Failed to fetch districts'], 500);
        }
    }
    
    /**
     * Get villages by district
     */
    public function getVillages() {
        try {
            $districtId = $this->get('district_id');
            
            if (empty($districtId)) {
                $this->json(['status' => 'error', 'message' => 'District ID is required'], 400);
                return;
            }
            
            $db = Database::getInstance('address');
            $sql = "SELECT id, code, name, postal_code FROM villages 
                    WHERE district_id = :district_id 
                    ORDER BY name ASC";
            $villages = $db->query($sql, ['district_id' => $districtId])->fetchAll();
            
            $this->json([
                'status' => 'success',
                'data' => $villages
            ]);
        } catch (Exception $e) {
            Logger::error('Get villages error', ['error' => $e->getMessage()]);
            $this->json(['status' => 'error', 'message' => 'Failed to fetch villages'], 500);
        }
    }
}
