<?php
/**
 * MyWisata Application - Packing List Helper
 * 
 * Handles smart packing list generation based on destination and trip details.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class PackingList {
    
    /**
     * Generate packing list based on destination and trip details
     * 
     * @param string $destination Destination name
     * @param string $climate Climate type (tropical, temperate, cold, desert)
     * @param int $duration Trip duration in days
     * @param array $activities Activities planned
     * @param string $season Season (summer, winter, spring, autumn)
     * @return array
     */
    public function generateList($destination, $climate, $duration, $activities = [], $season = null) {
        $packingList = [
            'essentials' => $this->getEssentials($duration),
            'clothing' => $this->getClothing($climate, $season, $duration),
            'footwear' => $this->getFootwear($activities),
            'toiletries' => $this->getToiletries($duration),
            'electronics' => $this->getElectronics($duration),
            'health' => $this->getHealthItems($destination),
            'documents' => $this->getDocuments(),
            'activity_specific' => $this->getActivitySpecificItems($activities),
            'optional' => $this->getOptionalItems($climate)
        ];
        
        return $packingList;
    }
    
    /**
     * Get essential items
     * 
     * @param int $duration Duration in days
     * @return array
     */
    private function getEssentials($duration) {
        return [
            'Passport/ID Card',
            'Wallet with cash and cards',
            'Phone and charger',
            'Keys',
            'Travel itinerary',
            'Emergency contacts list',
            'Snacks for travel',
            'Water bottle',
            'Travel pillow (for long journeys)',
            'Eye mask and earplugs'
        ];
    }
    
    /**
     * Get clothing items based on climate and season
     * 
     * @param string $climate Climate type
     * @param string $season Season
     * @param int $duration Duration in days
     * @return array
     */
    private function getClothing($climate, $season, $duration) {
        $clothing = [];
        
        // Base items
        $clothing[] = 'Underwear (' . ($duration + 2) . ' pairs)';
        $clothing[] = 'Socks (' . ($duration + 2) . ' pairs)';
        
        switch ($climate) {
            case 'tropical':
                $clothing = array_merge($clothing, [
                    'Light t-shirts (' . ($duration + 1) . ')',
                    'Shorts (2-3 pairs)',
                    'Lightweight pants (1-2 pairs)',
                    'Light dress/skirt',
                    'Light jacket or cardigan',
                    'Swimwear',
                    'Hat or cap',
                    'Sunglasses',
                    'Rain jacket or poncho'
                ]);
                break;
                
            case 'temperate':
                $clothing = array_merge($clothing, [
                    'T-shirts (' . $duration . ')',
                    'Long-sleeve shirts (2-3)',
                    'Jeans or trousers (2-3 pairs)',
                    'Sweater or hoodie',
                    'Light jacket',
                    'Dress or nice outfit',
                    'Comfortable walking shoes',
                    'Scarf'
                ]);
                break;
                
            case 'cold':
                $clothing = array_merge($clothing, [
                    'Thermal underwear',
                    'Long-sleeve shirts (' . $duration . ')',
                    'Sweaters (2-3)',
                    'Heavy jacket or coat',
                    'Warm pants (2-3 pairs)',
                    'Wool socks (3-4 pairs)',
                    'Gloves',
                    'Scarf',
                    'Beanie or warm hat',
                    'Waterproof boots'
                ]);
                break;
                
            case 'desert':
                $clothing = array_merge($clothing, [
                    'Light long-sleeve shirts (sun protection)',
                    'Light pants (cover legs)',
                    'Wide-brimmed hat',
                    'Sunglasses with UV protection',
                    'Light jacket (cold nights)',
                    'Scarf (for dust protection)',
                    'Closed-toe shoes'
                ]);
                break;
                
            default:
                $clothing = array_merge($clothing, [
                    'T-shirts (' . $duration . ')',
                    'Long-sleeve shirts (2)',
                    'Pants (2-3 pairs)',
                    'Light jacket',
                    'Comfortable shoes'
                ]);
        }
        
        return $clothing;
    }
    
    /**
     * Get footwear based on activities
     * 
     * @param array $activities Activities
     * @return array
     */
    private function getFootwear($activities) {
        $footwear = ['Comfortable walking shoes'];
        
        if (in_array('hiking', $activities) || in_array('trekking', $activities)) {
            $footwear[] = 'Hiking boots';
        }
        
        if (in_array('beach', $activities) || in_array('swimming', $activities)) {
            $footwear[] = 'Sandals or flip-flops';
            $footwear[] = 'Water shoes';
        }
        
        if (in_array('formal', $activities) || in_array('dining', $activities)) {
            $footwear[] = 'Dress shoes';
        }
        
        if (in_array('running', $activities) || in_array('exercise', $activities)) {
            $footwear[] = 'Running shoes';
        }
        
        return $footwear;
    }
    
    /**
     * Get toiletries
     * 
     * @param int $duration Duration in days
     * @return array
     */
    private function getToiletries($duration) {
        return [
            'Toothbrush and toothpaste',
            'Deodorant',
            'Shampoo and conditioner (travel size)',
            'Body wash or soap',
            'Razor and shaving cream',
            'Skincare products',
            'Sunscreen (SPF 30+)',
            'Lip balm with SPF',
            'Hairbrush/comb',
            'Feminine hygiene products',
            'Towel (quick-dry if possible)',
            'Hand sanitizer',
            'Wet wipes'
        ];
    }
    
    /**
     * Get electronics
     * 
     * @param int $duration Duration in days
     * @return array
     */
    private function getElectronics($duration) {
        return [
            'Phone charger',
            'Power bank',
            'Universal travel adapter',
            'Camera (optional)',
            'Memory cards',
            'Headphones/earbuds',
            'Laptop/tablet (optional)',
            'E-reader (optional)',
            'Portable speaker (optional)',
            'Flashlight or headlamp'
        ];
    }
    
    /**
     * Get health items based on destination
     * 
     * @param string $destination Destination
     * @return array
     */
    private function getHealthItems($destination) {
        $items = [
            'Basic first aid kit',
            'Pain relievers',
            'Allergy medication',
            'Motion sickness medication',
            'Insect repellent',
            'Band-aids',
            'Antiseptic cream',
            'Prescription medications'
        ];
        
        // Add destination-specific items
        if (stripos($destination, 'bali') !== false || stripos($destination, 'tropical') !== false) {
            $items[] = 'Anti-malaria medication (consult doctor)';
            $items[] = 'Anti-diarrhea medication';
        }
        
        if (stripos($destination, 'mountain') !== false || stripos($destination, 'hiking') !== false) {
            $items[] = 'Altitude sickness medication';
            $items[] = 'Blister treatment';
        }
        
        return $items;
    }
    
    /**
     * Get essential documents
     * 
     * @return array
     */
    private function getDocuments() {
        return [
            'Passport (valid 6+ months)',
            'Visa (if required)',
            'Travel insurance documents',
            'Flight tickets',
            'Hotel reservations',
            'Driving license',
            'Vaccination certificate',
            'Emergency contact information',
            'Copies of important documents'
        ];
    }
    
    /**
     * Get activity-specific items
     * 
     * @param array $activities Activities
     * @return array
     */
    private function getActivitySpecificItems($activities) {
        $items = [];
        
        if (in_array('beach', $activities) || in_array('swimming', $activities)) {
            $items = array_merge($items, [
                'Swimwear',
                'Beach towel',
                'Snorkel gear (optional)',
                'Waterproof phone case',
                'Beach bag'
            ]);
        }
        
        if (in_array('hiking', $activities) || in_array('trekking', $activities)) {
            $items = array_merge($items, [
                'Backpack',
                'Hiking poles',
                'Map and compass/GPS',
                'Water bottles or hydration pack',
                'Energy bars/snacks',
                'Headlamp',
                'Emergency whistle'
            ]);
        }
        
        if (in_array('photography', $activities)) {
            $items = array_merge($items, [
                'Camera with extra batteries',
                'Tripod',
                'Lens cleaning kit',
                'Extra memory cards'
            ]);
        }
        
        if (in_array('camping', $activities)) {
            $items = array_merge($items, [
                'Sleeping bag',
                'Tent',
                'Sleeping pad',
                'Camp stove',
                'Cooking utensils',
                'Multi-tool'
            ]);
        }
        
        return $items;
    }
    
    /**
     * Get optional items
     * 
     * @param string $climate Climate type
     * @return array
     */
    private function getOptionalItems($climate) {
        $items = [
            'Travel journal',
            'Pen',
            'Book or e-reader',
            'Playing cards/games',
            'Travel pillow',
            'Eye mask',
            'Earplugs',
            'Laundry detergent (travel size)',
            'Clothespins',
            'Ziploc bags'
        ];
        
        if ($climate === 'tropical') {
            $items[] = 'Mosquito net';
            $items[] = 'Cooling towel';
        }
        
        if ($climate === 'cold') {
            $items[] = 'Hand warmers';
            $items[] = 'Thermal blanket';
        }
        
        return $items;
    }
    
    /**
     * Get packing checklist with checkboxes
     * 
     * @param array $packingList Packing list
     * @return array
     */
    public function getChecklist($packingList) {
        $checklist = [];
        
        foreach ($packingList as $category => $items) {
            $checklist[$category] = [];
            foreach ($items as $item) {
                $checklist[$category][] = [
                    'item' => $item,
                    'checked' => false
                ];
            }
        }
        
        return $checklist;
    }
    
    /**
     * Save packing list to user profile
     * 
     * @param int $userId User ID
     * @param array $packingList Packing list
     * @param string $tripName Trip name
     * @return bool
     */
    public function savePackingList($userId, $packingList, $tripName) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO packing_lists 
                (user_id, trip_name, items, created_at)
                VALUES (:user_id, :trip_name, :items, NOW())";
        
        return $db->query($sql, [
            'user_id' => $userId,
            'trip_name' => $tripName,
            'items' => json_encode($packingList)
        ]);
    }
    
    /**
     * Get saved packing lists for user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserPackingLists($userId) {
        $db = Database::getInstance();
        
        $sql = "SELECT * FROM packing_lists WHERE user_id = :user_id ORDER BY created_at DESC";
        return $db->query($sql, ['user_id' => $userId])->fetchAll();
    }
}
