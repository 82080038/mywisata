<?php
/**
 * MyWisata Application - Dynamic Pricing Helper
 * 
 * Handles dynamic pricing recommendations based on demand, seasonality, and other factors.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class DynamicPricing {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get recommended price for a service
     * 
     * @param string $itemType Item type (destination, hotel, restaurant, tour_guide)
     * @param int $itemId Item ID
     * @param string $date Date for pricing
     * @param float $basePrice Base price
     * @return array Recommended pricing
     */
    public function getRecommendedPrice($itemType, $itemId, $date, $basePrice) {
        $demandMultiplier = $this->getDemandMultiplier($itemType, $itemId, $date);
        $seasonalMultiplier = $this->getSeasonalMultiplier($itemType, $itemId, $date);
        $competitorPrice = $this->getCompetitorPrice($itemType, $itemId);
        
        $recommendedPrice = $basePrice * $demandMultiplier * $seasonalMultiplier;
        
        // Apply reasonable limits (max 50% increase, max 30% decrease)
        $maxPrice = $basePrice * 1.5;
        $minPrice = $basePrice * 0.7;
        
        if ($recommendedPrice > $maxPrice) {
            $recommendedPrice = $maxPrice;
        }
        if ($recommendedPrice < $minPrice) {
            $recommendedPrice = $minPrice;
        }
        
        return [
            'base_price' => $basePrice,
            'recommended_price' => round($recommendedPrice, 2),
            'demand_multiplier' => $demandMultiplier,
            'seasonal_multiplier' => $seasonalMultiplier,
            'competitor_price' => $competitorPrice,
            'confidence' => $this->calculateConfidence($demandMultiplier, $seasonalMultiplier),
            'factors' => [
                'demand' => $this->getDemandLevel($demandMultiplier),
                'season' => $this->getSeasonLevel($seasonalMultiplier)
            ]
        ];
    }
    
    /**
     * Get demand multiplier based on historical data
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param string $date Date
     * @return float Multiplier
     */
    private function getDemandMultiplier($itemType, $itemId, $date) {
        // Get booking data for similar period
        $dateObj = new DateTime($date);
        $month = $dateObj->format('m');
        $dayOfWeek = $dateObj->format('N');
        
        // Weekend multiplier
        $weekendMultiplier = ($dayOfWeek >= 6) ? 1.2 : 1.0;
        
        // Seasonal demand based on month
        $seasonMultiplier = $this->getMonthMultiplier($month);
        
        // Historical booking data
        $historicalMultiplier = $this->getHistoricalDemand($itemType, $itemId, $month);
        
        return $weekendMultiplier * $seasonMultiplier * $historicalMultiplier;
    }
    
    /**
     * Get seasonal multiplier based on month
     * 
     * @param int $month Month (1-12)
     * @return float Multiplier
     */
    private function getMonthMultiplier($month) {
        // Peak season: June-August (summer holidays), December (Christmas/New Year)
        $peakMonths = [6, 7, 8, 12];
        // High season: April-May, September-October
        $highMonths = [4, 5, 9, 10];
        // Low season: January-March, November
        $lowMonths = [1, 2, 3, 11];
        
        if (in_array($month, $peakMonths)) {
            return 1.3;
        } elseif (in_array($month, $highMonths)) {
            return 1.15;
        } elseif (in_array($month, $lowMonths)) {
            return 0.85;
        }
        
        return 1.0;
    }
    
    /**
     * Get historical demand from bookings
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param int $month Month
     * @return float Multiplier
     */
    private function getHistoricalDemand($itemType, $itemId, $month) {
        $table = $this->getItemTable($itemType);
        
        // Get average bookings for this item in this month
        $sql = "SELECT COUNT(*) as bookings 
                FROM bookings b
                WHERE b.item_type = :item_type 
                AND b.item_id = :item_id
                AND MONTH(b.booking_date) = :month";
        
        $result = $this->db->query($sql, [
            'item_type' => $itemType,
            'item_id' => $itemId,
            'month' => $month
        ])->fetch();
        
        $bookings = $result['bookings'] ?? 0;
        
        // Get average bookings across all months
        $sql = "SELECT COUNT(*) as total_bookings 
                FROM bookings b
                WHERE b.item_type = :item_type 
                AND b.item_id = :item_id";
        
        $result = $this->db->query($sql, [
            'item_type' => $itemType,
            'item_id' => $itemId
        ])->fetch();
        
        $totalBookings = $result['total_bookings'] ?? 0;
        
        if ($totalBookings === 0) {
            return 1.0;
        }
        
        $averagePerMonth = $totalBookings / 12;
        
        if ($bookings > $averagePerMonth * 1.5) {
            return 1.2;
        } elseif ($bookings > $averagePerMonth * 1.2) {
            return 1.1;
        } elseif ($bookings < $averagePerMonth * 0.5) {
            return 0.8;
        }
        
        return 1.0;
    }
    
    /**
     * Get seasonal multiplier
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param string $date Date
     * @return float Multiplier
     */
    private function getSeasonalMultiplier($itemType, $itemId, $date) {
        // This could be enhanced with actual seasonal data
        // For now, use the month multiplier
        $dateObj = new DateTime($date);
        $month = $dateObj->format('m');
        
        return $this->getMonthMultiplier($month);
    }
    
    /**
     * Get competitor price (mock implementation)
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @return float|null
     */
    private function getCompetitorPrice($itemType, $itemId) {
        // In production, this would fetch from external APIs or competitor databases
        // For now, return null
        return null;
    }
    
    /**
     * Calculate confidence level
     * 
     * @param float $demandMultiplier Demand multiplier
     * @param float $seasonalMultiplier Seasonal multiplier
     * @return string Confidence level
     */
    private function calculateConfidence($demandMultiplier, $seasonalMultiplier) {
        $variance = abs($demandMultiplier - 1) + abs($seasonalMultiplier - 1);
        
        if ($variance < 0.2) {
            return 'high';
        } elseif ($variance < 0.4) {
            return 'medium';
        }
        return 'low';
    }
    
    /**
     * Get demand level description
     * 
     * @param float $multiplier Multiplier
     * @return string
     */
    private function getDemandLevel($multiplier) {
        if ($multiplier >= 1.3) {
            return 'very_high';
        } elseif ($multiplier >= 1.15) {
            return 'high';
        } elseif ($multiplier >= 0.9) {
            return 'normal';
        } elseif ($multiplier >= 0.7) {
            return 'low';
        }
        return 'very_low';
    }
    
    /**
     * Get season level description
     * 
     * @param float $multiplier Multiplier
     * @return string
     */
    private function getSeasonLevel($multiplier) {
        if ($multiplier >= 1.2) {
            return 'peak';
        } elseif ($multiplier >= 1.1) {
            return 'high';
        } elseif ($multiplier >= 0.9) {
            return 'normal';
        }
        return 'low';
    }
    
    /**
     * Get item table name
     * 
     * @param string $itemType Item type
     * @return string
     */
    private function getItemTable($itemType) {
        $tables = [
            'destination' => 'destinations',
            'hotel' => 'hotels',
            'restaurant' => 'restaurants',
            'tour_guide' => 'tour_guides'
        ];
        
        return $tables[$itemType] ?? 'destinations';
    }
    
    /**
     * Get pricing forecast for date range
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param float $basePrice Base price
     * @return array
     */
    public function getPricingForecast($itemType, $itemId, $startDate, $endDate, $basePrice) {
        $forecast = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $pricing = $this->getRecommendedPrice($itemType, $itemId, $date, $basePrice);
            $forecast[$date] = $pricing;
            $current->modify('+1 day');
        }
        
        return $forecast;
    }
    
    /**
     * Get optimal pricing strategy
     * 
     * @param string $itemType Item type
     * @param int $itemId Item ID
     * @param float $basePrice Base price
     * @return array
     */
    public function getOptimalStrategy($itemType, $itemId, $basePrice) {
        // Get forecast for next 30 days
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+30 days'));
        $forecast = $this->getPricingForecast($itemType, $itemId, $startDate, $endDate, $basePrice);
        
        // Calculate average recommended price
        $totalPrice = 0;
        $count = 0;
        $highDemandDays = 0;
        $lowDemandDays = 0;
        
        foreach ($forecast as $date => $pricing) {
            $totalPrice += $pricing['recommended_price'];
            $count++;
            
            if ($pricing['factors']['demand'] === 'very_high' || $pricing['factors']['demand'] === 'high') {
                $highDemandDays++;
            } elseif ($pricing['factors']['demand'] === 'low' || $pricing['factors']['demand'] === 'very_low') {
                $lowDemandDays++;
            }
        }
        
        $averagePrice = $count > 0 ? $totalPrice / $count : $basePrice;
        
        return [
            'average_recommended_price' => round($averagePrice, 2),
            'high_demand_days' => $highDemandDays,
            'low_demand_days' => $lowDemandDays,
            'strategy' => $this->generateStrategy($highDemandDays, $lowDemandDays, $averagePrice, $basePrice),
            'forecast' => $forecast
        ];
    }
    
    /**
     * Generate pricing strategy recommendation
     * 
     * @param int $highDemandDays Number of high demand days
     * @param int $lowDemandDays Number of low demand days
     * @param float $averagePrice Average recommended price
     * @param float $basePrice Base price
     * @return string
     */
    private function generateStrategy($highDemandDays, $lowDemandDays, $averagePrice, $basePrice) {
        $priceDiff = (($averagePrice - $basePrice) / $basePrice) * 100;
        
        if ($highDemandDays > 20) {
            return 'dynamic_high - Use dynamic pricing with higher rates during peak demand days';
        } elseif ($lowDemandDays > 20) {
            return 'dynamic_low - Use dynamic pricing with discounts during low demand days';
        } elseif ($priceDiff > 10) {
            return 'moderate_increase - Consider moderate price increase based on demand';
        } elseif ($priceDiff < -10) {
            return 'moderate_decrease - Consider moderate price decrease to attract more bookings';
        }
        
        return 'stable - Maintain current pricing strategy';
    }
}
