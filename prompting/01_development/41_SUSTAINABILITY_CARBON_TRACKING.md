# MODUL 41 — SUSTAINABILITY FEATURES (CARBON TRACKING)

> **Modul:** Sustainability & Carbon Tracking  
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18  
> **Tujuan:** Implementasi sustainability features dengan GHG Calculator (open-source, zero API costs)

---

## 1. OBJECTIVE

Implementasi sustainability features untuk track carbon footprint, eco-scoring, dan green credits menggunakan GHG Calculator (open-source, MIT License, zero API costs).

## 2. FITUR YANG AKAN DIIMPLEMENTASIKAN

### 2.1 Carbon Tracking
- Real-time carbon footprint calculation per trip
- CO2 emissions untuk transport (pesawat, mobil, bus, dll)
- Carbon footprint history per user

### 2.2 Green Credits / Eco-Score
- Eco-score system per trip
- Green credits accumulation
- Leaderboard untuk eco-friendly travelers

### 2.3 Low-Carbon Routing
- Route suggestion dengan emisi rendah
- Integration dengan existing map system
- Carbon-aware routing

### 2.4 Sustainability Scores
- Score untuk carbon, planet, people impact
- Rating system untuk destinations dan vendors
- Sustainability badges

### 2.5 Eco Rewards
- Gamified rewards untuk sustainable travel
- Points system untuk eco-friendly choices
- Rewards redemption

## 3. PREREQUISITES

### 3.1 Software Requirements
- Python 3.8+
- pip package manager
- Git (untuk clone GHG Calculator)

### 3.2 Configuration
Baca `prompting/config.json` untuk environment configuration.

## 4. IMPLEMENTATION STEPS

### 4.1 Phase 1: GHG Calculator Installation

**Step 1: Clone GHG Calculator**
```bash
cd /opt/lampp/htdocs/mywisata
git clone https://github.com/starrybodies/ghg-calculator.git tools/ghg-calculator
cd tools/ghg-calculator
```

**Step 2: Install Dependencies**
```bash
pip install -r requirements.txt
```

**Step 3: Test Installation**
```bash
python -m ghg_calculator --help
```

**Step 4: Test Carbon Calculation**
```bash
# Test calculation untuk electricity
python -m ghg_calculator calculate \
  --scope scope1 \
  --activity_type electricity \
  --amount 100 \
  --unit kWh
```

### 4.2 Phase 2: Carbon Tracking Service

**Step 1: Create Carbon Service Wrapper**
```php
// app/services/CarbonService.php
<?php
class CarbonService {
    private $ghgCalculatorPath;
    
    public function __construct() {
        $this->ghgCalculatorPath = '/opt/lampp/htdocs/mywisata/tools/ghg-calculator';
    }
    
    public function calculateTransportEmission($distance, $transportType) {
        // Emission factors (kg CO2 per km)
        $emissionFactors = [
            'car_petrol' => 0.21,
            'car_diesel' => 0.19,
            'bus' => 0.089,
            'train' => 0.041,
            'plane_domestic' => 0.255,
            'plane_international' => 0.195,
            'motorcycle' => 0.113,
            'walking' => 0.0,
            'cycling' => 0.0
        ];
        
        $factor = $emissionFactors[$transportType] ?? 0.21;
        $emission = $distance * $factor;
        
        return [
            'distance_km' => $distance,
            'transport_type' => $transportType,
            'emission_kg_co2' => $emission,
            'emission_ton_co2' => $emission / 1000
        ];
    }
    
    public function calculateTripCarbonFootprint($tripData) {
        $totalEmission = 0;
        $breakdown = [];
        
        foreach ($tripData['transport'] as $transport) {
            $result = $this->calculateTransportEmission(
                $transport['distance'],
                $transport['type']
            );
            $totalEmission += $result['emission_kg_co2'];
            $breakdown[] = $result;
        }
        
        // Add accommodation emissions
        if (isset($tripData['accommodation'])) {
            $accomEmission = $this->calculateAccommodationEmission(
                $tripData['accommodation']
            );
            $totalEmission += $accomEmission['emission_kg_co2'];
            $breakdown[] = $accomEmission;
        }
        
        return [
            'total_emission_kg_co2' => $totalEmission,
            'total_emission_ton_co2' => $totalEmission / 1000,
            'breakdown' => $breakdown
        ];
    }
    
    private function calculateAccommodationEmission($accommodation) {
        // Emission factors per night (kg CO2)
        $factors = [
            'hotel' => 20.0,
            'homestay' => 10.0,
            'hostel' => 8.0,
            'camping' => 2.0
        ];
        
        $factor = $factors[$accommodation['type']] ?? 20.0;
        $emission = $accommodation['nights'] * $factor;
        
        return [
            'type' => 'accommodation',
            'accommodation_type' => $accommodation['type'],
            'nights' => $accommodation['nights'],
            'emission_kg_co2' => $emission
        ];
    }
}
```

**Step 2: Create Carbon Tracking Model**
```php
// app/models/CarbonFootprint.php
<?php
class CarbonFootprint extends Model {
    protected $table = 'carbon_footprints';
    
    public function create($data) {
        $sql = "INSERT INTO carbon_footprints 
                (user_id, trip_id, total_emission_kg_co2, breakdown, created_at)
                VALUES (:user_id, :trip_id, :total_emission_kg_co2, :breakdown, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':trip_id' => $data['trip_id'],
            ':total_emission_kg_co2' => $data['total_emission_kg_co2'],
            ':breakdown' => json_encode($data['breakdown'])
        ]);
    }
    
    public function getUserFootprint($userId) {
        $sql = "SELECT * FROM carbon_footprints WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalEmission($userId) {
        $sql = "SELECT SUM(total_emission_kg_co2) as total 
                FROM carbon_footprints 
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
```

**Step 3: Create Database Migration**
```sql
-- database/migrations/add_carbon_footprints.sql
CREATE TABLE carbon_footprints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    trip_id BIGINT UNSIGNED,
    total_emission_kg_co2 DECIMAL(10, 4) NOT NULL,
    breakdown JSON,
    eco_score INT DEFAULT 0,
    green_credits INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_trip_id (trip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE green_credits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    credits INT DEFAULT 0,
    earned_from VARCHAR(100),
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.3 Phase 3: Eco-Score System

**Step 1: Create Eco-Score Service**
```php
// app/services/EcoScoreService.php
<?php
class EcoScoreService {
    public function calculateEcoScore($carbonFootprint) {
        $score = 100;
        
        // Deduct points berdasarkan emission
        if ($carbonFootprint['total_emission_kg_co2'] > 500) {
            $score -= 30;
        } elseif ($carbonFootprint['total_emission_kg_co2'] > 200) {
            $score -= 20;
        } elseif ($carbonFootprint['total_emission_kg_co2'] > 100) {
            $score -= 10;
        }
        
        // Bonus points untuk eco-friendly transport
        foreach ($carbonFootprint['breakdown'] as $item) {
            if (in_array($item['transport_type'] ?? '', ['walking', 'cycling', 'bus', 'train'])) {
                $score += 5;
            }
        }
        
        return max(0, min(100, $score));
    }
    
    public function assignGreenCredits($ecoScore) {
        // 1 eco-score point = 1 green credit
        return $ecoScore;
    }
}
```

**Step 2: Integrate dengan Booking System**
```php
// Di BookingController, setelah booking selesai
public function completeBooking($bookingId) {
    // ... existing logic ...
    
    // Calculate carbon footprint
    $carbonService = new CarbonService();
    $tripData = $this->getTripData($bookingId);
    $footprint = $carbonService->calculateTripCarbonFootprint($tripData);
    
    // Calculate eco-score
    $ecoScoreService = new EcoScoreService();
    $ecoScore = $ecoScoreService->calculateEcoScore($footprint);
    $greenCredits = $ecoScoreService->assignGreenCredits($ecoScore);
    
    // Save ke database
    $carbonModel = new CarbonFootprint();
    $carbonModel->create([
        'user_id' => $booking['user_id'],
        'trip_id' => $bookingId,
        'total_emission_kg_co2' => $footprint['total_emission_kg_co2'],
        'breakdown' => $footprint['breakdown'],
        'eco_score' => $ecoScore,
        'green_credits' => $greenCredits
    ]);
    
    // Update user green credits
    $this->updateUserGreenCredits($booking['user_id'], $greenCredits);
}
```

### 4.4 Phase 4: Low-Carbon Routing

**Step 1: Create Carbon-Aware Routing Service**
```php
// app/services/CarbonRoutingService.php
<?php
class CarbonRoutingService {
    private $carbonService;
    
    public function __construct() {
        $this->carbonService = new CarbonService();
    }
    
    public function getLowCarbonRoutes($origin, $destination) {
        // Get multiple route options
        $routes = $this->getRouteOptions($origin, $destination);
        
        // Calculate carbon untuk setiap route
        foreach ($routes as &$route) {
            $route['carbon_emission'] = $this->carbonService->calculateTransportEmission(
                $route['distance'],
                $route['transport_type']
            );
            $route['eco_score'] = $this->calculateRouteEcoScore($route);
        }
        
        // Sort by carbon emission (lowest first)
        usort($routes, function($a, $b) {
            return $a['carbon_emission']['emission_kg_co2'] <=> $b['carbon_emission']['emission_kg_co2'];
        });
        
        return $routes;
    }
    
    private function calculateRouteEcoScore($route) {
        // Eco-score berdasarkan carbon emission
        $emission = $route['carbon_emission']['emission_kg_co2'];
        
        if ($emission < 10) return 100;
        if ($emission < 20) return 90;
        if ($emission < 50) return 70;
        if ($emission < 100) return 50;
        return 30;
    }
}
```

**Step 2: Create Low-Carbon Route Controller**
```php
// app/controllers/CarbonRoutingController.php
<?php
class CarbonRoutingController extends Controller {
    private $routingService;
    
    public function __construct() {
        $this->routingService = new CarbonRoutingService();
    }
    
    public function getLowCarbonRoutes() {
        $origin = $_GET['origin'] ?? '';
        $destination = $_GET['destination'] ?? '';
        
        if (empty($origin) || empty($destination)) {
            return $this->json(['status' => 'error', 'message' => 'Origin and destination required']);
        }
        
        $routes = $this->routingService->getLowCarbonRoutes($origin, $destination);
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'routes' => $routes,
                'greenest' => $routes[0] ?? null
            ]
        ]);
    }
}
```

### 4.5 Phase 5: Sustainability Dashboard

**Step 1: Create Sustainability Dashboard Controller**
```php
// app/controllers/SustainabilityController.php
<?php
class SustainabilityController extends Controller {
    public function dashboard() {
        $userId = $_SESSION['user_id'];
        
        $carbonModel = new CarbonFootprint();
        $footprints = $carbonModel->getUserFootprint($userId);
        $totalEmission = $carbonModel->getTotalEmission($userId);
        
        // Get user green credits
        $greenCredits = $this->getUserGreenCredits($userId);
        
        // Get leaderboard
        $leaderboard = $this->getEcoLeaderboard();
        
        return $this->view('sustainability/dashboard', [
            'footprints' => $footprints,
            'total_emission' => $totalEmission,
            'green_credits' => $greenCredits,
            'leaderboard' => $leaderboard
        ]);
    }
    
    private function getUserGreenCredits($userId) {
        $sql = "SELECT SUM(credits) as total FROM green_credits WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    private function getEcoLeaderboard() {
        $sql = "SELECT u.id, u.name, SUM(c.green_credits) as total_credits
                FROM users u
                JOIN carbon_footprints c ON u.id = c.user_id
                GROUP BY u.id, u.name
                ORDER BY total_credits DESC
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

**Step 2: Create Dashboard View**
```php
// app/views/sustainability/dashboard.php
<div class="container mt-4">
    <h2>Sustainability Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Total Carbon Footprint</h5>
                    <h3><?php echo number_format($total_emission, 2); ?> kg CO2</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Green Credits</h5>
                    <h3><?php echo $green_credits; ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5>Eco Ranking</h5>
                    <h3>#<?php echo $this->getUserRank($userId); ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h3>Carbon Footprint History</h3>
            <canvas id="carbonChart"></canvas>
        </div>
        
        <div class="col-md-6">
            <h3>Eco Leaderboard</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Green Credits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $index => $user): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo $user['total_credits']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Chart.js untuk carbon footprint history
const ctx = document.getElementById('carbonChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($footprints, 'created_at')); ?>,
        datasets: [{
            label: 'Carbon Footprint (kg CO2)',
            data: <?php echo json_encode(array_column($footprints, 'total_emission_kg_co2')); ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    }
});
</script>
```

## 5. TESTING

### 5.1 Unit Tests
```php
// tests/Unit/Services/CarbonServiceTest.php
<?php
class CarbonServiceTest extends PHPUnit\Framework\TestCase {
    private $carbonService;
    
    protected function setUp(): void {
        $this->carbonService = new CarbonService();
    }
    
    public function testTransportEmissionCalculation() {
        $result = $this->carbonService->calculateTransportEmission(100, 'car_petrol');
        
        $this->assertEquals(21.0, $result['emission_kg_co2']);
        $this->assertEquals(100, $result['distance_km']);
    }
    
    public function testEcoScoreCalculation() {
        $ecoService = new EcoScoreService();
        $score = $ecoService->calculateEcoScore([
            'total_emission_kg_co2' => 50,
            'breakdown' => [
                ['transport_type' => 'bus']
            ]
        ]);
        
        $this->assertGreaterThan(80, $score);
    }
}
```

## 6. DOCUMENTATION UPDATES

Update dokumentasi berikut:
- `docs/sustainability_guide.md` - Create new guide
- `docs/DEVELOPER_GUIDE.md` - Add sustainability section
- API documentation - Add carbon tracking endpoints

## 7. COMPLETION CRITERIA

Modul ini selesai ketika:
- ✅ GHG Calculator terinstall dan tested
- ✅ Carbon tracking service berfungsi
- ✅ Eco-score system berfungsi
- ✅ Green credits system berfungsi
- ✅ Low-carbon routing berfungsi
- ✅ Sustainability dashboard berfungsi
- ✅ Semua tests passing
- ✅ Documentation updated

---

## NEXT STEPS

Setelah modul ini selesai, lanjut ke:
- Modul 42: WhatsApp Integration (OpenWA/WaSphere)
