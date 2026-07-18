<?php
namespace App\Services;

use App\Models\GuideMatchResult;
use App\Models\SmartScheduleEntry;
use App\Models\PayrollRecord;
use App\Models\GPSClockInRecord;
use App\Models\ExpressBookRecord;
use App\Models\TourGuide;
use App\Models\Booking;

/**
 * Business Operations Service
 * 
 * Service for business operations automation (self-hosted)
 * 
 * @package App\Services
 */
class BusinessOperationsService {
    private $guideMatchResult;
    private $smartScheduleEntry;
    private $payrollRecord;
    private $gpsClockInRecord;
    private $expressBookRecord;
    
    public function __construct() {
        $this->guideMatchResult = new GuideMatchResult();
        $this->smartScheduleEntry = new SmartScheduleEntry();
        $this->payrollRecord = new PayrollRecord();
        $this->gpsClockInRecord = new GPSClockInRecord();
        $this->expressBookRecord = new ExpressBookRecord();
    }
    
    /**
     * AI Match Engine - Find best guide for booking
     * 
     * @param int $bookingId Booking ID
     * @param array $requirements Requirements
     * @return array Match results
     */
    public function matchGuide($bookingId, $requirements) {
        $tourGuideModel = new TourGuide();
        $bookingModel = new Booking();
        
        $booking = $bookingModel->find($bookingId);
        if (!$booking) {
            return ['success' => false, 'error' => 'Booking not found'];
        }
        
        // Get available guides
        $guides = $tourGuideModel->getAvailableGuides(
            $booking['date'],
            $booking['duration']
        );
        
        if (empty($guides)) {
            return ['success' => false, 'error' => 'No available guides'];
        }
        
        // Score each guide
        $matchedGuides = [];
        foreach ($guides as $guide) {
            $score = $this->calculateMatchScore($guide, $requirements, $booking);
            
            $matchedGuides[] = [
                'guide_id' => $guide['id'],
                'match_score' => $score,
                'match_reasons' => $this->getMatchReasons($guide, $requirements)
            ];
        }
        
        // Sort by score
        usort($matchedGuides, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
        
        // Save match results
        foreach ($matchedGuides as $match) {
            $this->guideMatchResult->create([
                'booking_id' => $bookingId,
                'user_id' => $booking['user_id'],
                'matched_guide_id' => $match['guide_id'],
                'match_score' => $match['match_score'],
                'match_reasons' => json_encode($match['match_reasons'])
            ]);
        }
        
        return ['success' => true, 'matches' => $matchedGuides];
    }
    
    /**
     * Calculate match score
     * 
     * @param array $guide Guide data
     * @param array $requirements Requirements
     * @param array $booking Booking data
     * @return float Score (0-100)
     */
    private function calculateMatchScore($guide, $requirements, $booking) {
        $score = 50; // Base score
        
        // Language match (up to 20 points)
        if (isset($requirements['languages']) && !empty($requirements['languages'])) {
            $guideLanguages = json_decode($guide['languages'], true) ?? [];
            $languageMatch = count(array_intersect($requirements['languages'], $guideLanguages));
            $score += min(20, $languageMatch * 10);
        }
        
        // Specialization match (up to 15 points)
        if (isset($requirements['specializations']) && !empty($requirements['specializations'])) {
            $guideSpecializations = json_decode($guide['specializations'], true) ?? [];
            $specMatch = count(array_intersect($requirements['specializations'], $guideSpecializations));
            $score += min(15, $specMatch * 5);
        }
        
        // Rating (up to 10 points)
        $score += min(10, $guide['rating'] * 2);
        
        // Location proximity (up to 5 points)
        if (isset($booking['destination_id']) && $guide['location'] == $booking['destination_id']) {
            $score += 5;
        }
        
        return min(100, $score);
    }
    
    /**
     * Get match reasons
     * 
     * @param array $guide Guide data
     * @param array $requirements Requirements
     * @return array Reasons
     */
    private function getMatchReasons($guide, $requirements) {
        $reasons = [];
        
        if ($guide['rating'] >= 4.5) {
            $reasons[] = 'Highly rated guide';
        }
        
        if (isset($requirements['languages']) && !empty($requirements['languages'])) {
            $guideLanguages = json_decode($guide['languages'], true) ?? [];
            $languageMatch = array_intersect($requirements['languages'], $guideLanguages);
            if (!empty($languageMatch)) {
                $reasons[] = 'Speaks required languages';
            }
        }
        
        return $reasons;
    }
    
    /**
     * Create schedule entry
     * 
     * @param array $data Schedule data
     * @return array Result
     */
    public function createScheduleEntry($data) {
        // Check for conflicts
        $conflicts = $this->smartScheduleEntry->getConflicts(
            $data['guide_id'],
            $data['start_datetime'],
            $data['end_datetime']
        );
        
        if (!empty($conflicts)) {
            return ['success' => false, 'error' => 'Schedule conflict detected', 'conflicts' => $conflicts];
        }
        
        $id = $this->smartScheduleEntry->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to create schedule entry'];
    }
    
    /**
     * Create payroll record
     * 
     * @param array $data Payroll data
     * @return array Result
     */
    public function createPayrollRecord($data) {
        $id = $this->payrollRecord->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to create payroll record'];
    }
    
    /**
     * Calculate payroll for guide
     * 
     * @param int $guideId Guide ID
     * @param string $periodStart Period start date
     * @param string $periodEnd Period end date
     * @return array Payroll calculation
     */
    public function calculatePayroll($guideId, $periodStart, $periodEnd) {
        $tourGuideModel = new TourGuide();
        $guide = $tourGuideModel->find($guideId);
        
        if (!$guide) {
            return ['success' => false, 'error' => 'Guide not found'];
        }
        
        // Get clock-in records
        $totalHours = $this->gpsClockInRecord->getTotalHours($guideId, $periodStart, $periodEnd);
        
        // Calculate base salary
        $baseSalary = $guide['daily_rate'] * 22; // Assuming 22 working days
        
        // Calculate commission (20% of bookings)
        $commission = 0; // Would need to calculate from bookings
        
        // Calculate net salary
        $netSalary = $baseSalary + $commission;
        
        return [
            'success' => true,
            'guide_id' => $guideId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_hours' => $totalHours,
            'base_salary' => $baseSalary,
            'commission' => $commission,
            'net_salary' => $netSalary
        ];
    }
    
    /**
     * Clock in guide
     * 
     * @param array $data Clock-in data
     * @return array Result
     */
    public function clockIn($data) {
        $id = $this->gpsClockInRecord->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to clock in'];
    }
    
    /**
     * Clock out guide
     * 
     * @param int $recordId Record ID
     * @param array $data Clock-out data
     * @return array Result
     */
    public function clockOut($recordId, $data) {
        $result = $this->gpsClockInRecord->clockOut($recordId, $data);
        
        if ($result) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Failed to clock out'];
    }
    
    /**
     * Create express book record
     * 
     * @param array $data Express book data
     * @return array Result
     */
    public function createExpressBook($data) {
        $id = $this->expressBookRecord->create($data);
        
        if ($id) {
            return ['success' => true, 'id' => $id];
        }
        
        return ['success' => false, 'error' => 'Failed to create express book record'];
    }
    
    /**
     * Get guide statistics
     * 
     * @param int $guideId Guide ID
     * @return array Statistics
     */
    public function getGuideStatistics($guideId) {
        $matchStats = $this->guideMatchResult->getGuideStatistics($guideId);
        $clockInRecords = $this->gpsClockInRecord->getByGuideId($guideId, 10);
        $expressBooks = $this->expressBookRecord->getByGuideId($guideId, 10);
        
        return [
            'match_statistics' => $matchStats,
            'recent_clock_ins' => $clockInRecords,
            'recent_express_books' => $expressBooks
        ];
    }
}
