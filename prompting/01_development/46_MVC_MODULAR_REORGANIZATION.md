# MODUL 46 — MVC MODULAR REORGANIZATION

> **Objective:** Reorganize MVC structure to be modular with clear separation of concerns for better maintainability, performance, and scalability
> **Version:** 1.0  
> **Date:** 2026-07-18

---

## OBJECTIVES

Transform the current monolithic MVC structure into a modular architecture with:
- **Module-based organization** - Controllers, models, services, repositories organized by feature modules
- **Service Layer** - Business logic separated from controllers
- **Repository Pattern** - Data access layer abstracted from business logic
- **Caching** - Systematic caching at service layer for performance
- **Namespaces** - Clear namespace structure for better code organization

---

## CURRENT STRUCTURE (Before)

```
app/
├── controllers/      # 35 files (monolithic)
├── models/          # 23 files (monolithic)
├── services/        # 6 files (incomplete)
├── views/           # scattered
└── core/            # basic framework
```

**Problems:**
- All controllers in one folder
- All models in one folder
- Business logic in controllers (fat controllers)
- No repository pattern
- Incomplete service layer
- No systematic caching
- No module organization

---

## TARGET STRUCTURE (After)

```
app/
├── Modules/                    # Modular structure
│   ├── Auth/                  # Authentication Module
│   │   ├── Controllers/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Views/
│   ├── Admin/                 # Admin Module
│   ├── Booking/               # Booking Module
│   ├── Destination/           # Destination Module
│   ├── TourGuide/             # Tour Guide Module
│   ├── AI/                    # AI Module
│   ├── Social/                # Social Module
│   ├── Content/               # Content Module
│   ├── Utility/               # Utility Module
│   └── Gamification/          # Gamification Module
├── Core/                      # Enhanced framework
│   ├── Repository.php         # Base Repository
│   ├── Service.php            # Base Service
│   ├── Controller.php         # Base Controller
│   ├── Model.php              # Base Model
│   └── Database.php
└── Helpers/                   # Helpers (unchanged)
```

---

## IMPLEMENTATION STEPS

### Phase 1: Base Classes (COMPLETED ✅)

**Files Created:**
- `app/Core/Repository.php` - Base Repository with CRUD operations
- `app/Core/Service.php` - Base Service with caching and validation

**Features:**
- Repository: find, findBy, findAll, create, update, delete, count, exists, query, transactions
- Service: cacheGet, cacheSet, cacheDelete, cacheClear, cacheRemember, validate, logError, paginate

### Phase 2: Module Structure (COMPLETED ✅)

**Modules Created:**
- Auth (Controllers, Models, Services, Repositories, Views)
- Admin (Controllers, Services, Views)
- Booking (Controllers, Models, Services, Repositories, Views)
- Destination (Controllers, Models, Services, Repositories, Views)
- TourGuide (Controllers, Models, Services, Repositories, Views)
- AI (Controllers, Services, Views)
- Social (Controllers, Models, Services, Repositories, Views)
- Content (Controllers, Models, Services, Views)
- Utility (Controllers, Services, Views)
- Gamification (Controllers, Models, Services, Views)

### Phase 3: Migrate Controllers

**Task:** Move controllers to module folders and update namespaces

**Mapping:**
```
Auth Module:
- AuthController.php → Modules/Auth/Controllers/AuthController.php
- VerificationController.php → Modules/Auth/Controllers/VerificationController.php

Admin Module:
- AdminController.php → Modules/Admin/Controllers/AdminController.php
- ReportController.php → Modules/Admin/Controllers/ReportController.php
- BackupController.php → Modules/Admin/Controllers/BackupController.php

Booking Module:
- BookingController.php → Modules/Booking/Controllers/BookingController.php
- CartController.php → Modules/Booking/Controllers/CartController.php
- PaymentController.php → Modules/Booking/Controllers/PaymentController.php

Destination Module:
- DestinationController.php → Modules/Destination/Controllers/DestinationController.php
- HotelController.php → Modules/Destination/Controllers/HotelController.php
- RestaurantController.php → Modules/Destination/Controllers/RestaurantController.php
- EventController.php → Modules/Destination/Controllers/EventController.php

TourGuide Module:
- TourGuideController.php → Modules/TourGuide/Controllers/TourGuideController.php
- AvailabilityController.php → Modules/TourGuide/Controllers/AvailabilityController.php
- SupplierController.php → Modules/TourGuide/Controllers/SupplierController.php

AI Module:
- AIController.php → Modules/AI/Controllers/AIController.php
- AITourGuideController.php → Modules/AI/Controllers/AITourGuideController.php
- SpeechController.php → Modules/AI/Controllers/SpeechController.php

Social Module:
- FavoriteController.php → Modules/Social/Controllers/FavoriteController.php
- ReviewController.php → Modules/Social/Controllers/ReviewController.php
- MessageController.php → Modules/Social/Controllers/MessageController.php

Content Module:
- ItineraryController.php → Modules/Content/Controllers/ItineraryController.php
- AudioGuideController.php → Modules/Content/Controllers/AudioGuideController.php
- NotificationController.php → Modules/Content/Controllers/NotificationController.php

Utility Module:
- SearchController.php → Modules/Utility/Controllers/SearchController.php
- MapController.php → Modules/Utility/Controllers/MapController.php
- AddressController.php → Modules/Utility/Controllers/AddressController.php
- LanguageController.php → Modules/Utility/Controllers/LanguageController.php

Gamification Module:
- GamificationController.php → Modules/Gamification/Controllers/GamificationController.php
- PromoCodeController.php → Modules/Gamification/Controllers/PromoCodeController.php

Others:
- HomeController.php → Modules/Utility/Controllers/HomeController.php
- ApiController.php → Modules/Utility/Controllers/ApiController.php
- DataImportController.php → Modules/Admin/Controllers/DataImportController.php
- TestController.php → Modules/Utility/Controllers/TestController.php
```

**Namespace Updates:**
```php
// Before
namespace App\Controllers;

// After
namespace App\Modules\Auth\Controllers;
namespace App\Modules\Admin\Controllers;
// etc.
```

### Phase 4: Migrate Models and Create Repositories

**Task:** Move models to module folders and create repository for each model

**Mapping:**
```
Auth Module:
- User.php → Modules/Auth/Models/User.php
- Verification.php → Modules/Auth/Models/Verification.php
- Create: Modules/Auth/Repositories/UserRepository.php
- Create: Modules/Auth/Repositories/VerificationRepository.php

Booking Module:
- Booking.php → Modules/Booking/Models/Booking.php
- Cart.php → Modules/Booking/Models/Cart.php
- Transaction.php → Modules/Booking/Models/Transaction.php
- Create: Modules/Booking/Repositories/BookingRepository.php
- Create: Modules/Booking/Repositories/CartRepository.php
- Create: Modules/Booking/Repositories/TransactionRepository.php

Destination Module:
- Destination.php → Modules/Destination/Models/Destination.php
- Hotel.php → Modules/Destination/Models/Hotel.php
- Restaurant.php → Modules/Destination/Models/Restaurant.php
- Event.php → Modules/Destination/Models/Event.php
- Create: Modules/Destination/Repositories/DestinationRepository.php
- Create: Modules/Destination/Repositories/HotelRepository.php
- Create: Modules/Destination/Repositories/RestaurantRepository.php
- Create: Modules/Destination/Repositories/EventRepository.php

TourGuide Module:
- TourGuide.php → Modules/TourGuide/Models/TourGuide.php
- Create: Modules/TourGuide/Repositories/TourGuideRepository.php

Social Module:
- Favorite.php → Modules/Social/Models/Favorite.php
- Review.php → Modules/Social/Models/Review.php
- Message.php → Modules/Social/Models/Message.php
- Create: Modules/Social/Repositories/FavoriteRepository.php
- Create: Modules/Social/Repositories/ReviewRepository.php
- Create: Modules/Social/Repositories/MessageRepository.php

Content Module:
- Itinerary.php → Modules/Content/Models/Itinerary.php
- Notification.php → Modules/Content/Models/Notification.php
- Create: Modules/Content/Repositories/ItineraryRepository.php
- Create: Modules/Content/Repositories/NotificationRepository.php

Gamification Module:
- Gamification.php → Modules/Gamification/Models/Gamification.php
- PromoCode.php → Modules/Gamification/Models/PromoCode.php
- Create: Modules/Gamification/Repositories/GamificationRepository.php
- Create: Modules/Gamification/Repositories/PromoCodeRepository.php
```

**Remove Duplicates:**
- Delete: PromoCodeModel.php (use PromoCode.php)
- Delete: ReviewModel.php (use Review.php)
- Delete: ReviewPhotoModel.php (merge into Review.php)

**Repository Template:**
```php
<?php
namespace App\Modules\[Module]\Repositories;

use App\Core\Repository;

class [Model]Repository extends Repository {
    protected $table = '[table_name]';
    protected $primaryKey = 'id';
    
    // Custom methods specific to this repository
    public function findByUserId($userId) {
        return $this->findAll(['user_id' => $userId]);
    }
    
    public function findActive() {
        return $this->findAll(['status' => 'active']);
    }
}
```

### Phase 5: Create Services

**Task:** Create service layer for all modules with business logic and caching

**Services to Create:**

**Auth Module:**
- AuthService.php (login, register, logout, password reset)
- VerificationService.php (email verification, phone verification)

**Admin Module:**
- AdminService.php (user management, approval)
- ReportService.php (analytics, statistics)
- BackupService.php (database backup, restore)

**Booking Module:**
- BookingService.php (create booking, cancel booking, status updates)
- CartService.php (add to cart, remove from cart, calculate total)
- PaymentService.php (process payment, refund, status updates)

**Destination Module:**
- DestinationService.php (search, filter, nearby)
- HotelService.php (search, availability, booking)
- RestaurantService.php (search, menu, reservation)
- EventService.php (search, registration, calendar)

**TourGuide Module:**
- TourGuideService.php (profile, availability, matching)
- AvailabilityService.php (schedule, booking slots)
- SupplierService.php (vendor management)

**AI Module:**
- AISearchService.php (AI-powered search)
- SpeechService.php (speech-to-text, text-to-speech)

**Social Module:**
- FavoriteService.php (add/remove favorites, lists)
- ReviewService.php (create review, moderate, sentiment)
- MessageService.php (send message, conversations)

**Content Module:**
- ItineraryService.php (create, import, export)
- AudioGuideService.php (upload, playback, transcribe)
- NotificationService.php (send, mark read, preferences)

**Utility Module:**
- SearchService.php (global search, filters)
- MapService.php (geocoding, routing, markers)
- AddressService.php (cascading dropdowns)

**Gamification Module:**
- GamificationService.php (points, badges, leaderboard)
- PromoCodeService.php (create, validate, apply)

**Service Template:**
```php
<?php
namespace App\Modules\[Module]\Services;

use App\Core\Service;
use App\Modules\[Module]\Repositories\[Model]Repository;

class [Model]Service extends Service {
    private $repository;
    
    public function __construct() {
        $this->repository = new [Model]Repository();
        parent::__construct($this->repository);
    }
    
    public function getAll($cache = true) {
        $key = $this->cacheKey('getAll');
        
        return $this->cacheRemember($key, function() {
            return $this->repository->findAll();
        }, $cache ? 3600 : 0);
    }
    
    public function getById($id, $cache = true) {
        $key = $this->cacheKey('getById', [$id]);
        
        return $this->cacheRemember($key, function() use ($id) {
            return $this->repository->find($id);
        }, $cache ? 3600 : 0);
    }
    
    public function create($data) {
        // Validate
        $validation = $this->validate($data, [
            'field' => 'required'
        ]);
        
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Create
        $id = $this->repository->create($data);
        
        // Clear cache
        $this->cacheClear();
        
        return ['success' => true, 'id' => $id];
    }
    
    public function update($id, $data) {
        // Validate
        $validation = $this->validate($data, [
            'field' => 'required'
        ]);
        
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }
        
        // Update
        $result = $this->repository->update($id, $data);
        
        // Clear cache
        $this->cacheClear();
        
        return ['success' => $result];
    }
    
    public function delete($id) {
        $result = $this->repository->delete($id);
        
        // Clear cache
        $this->cacheClear();
        
        return ['success' => $result];
    }
}
```

### Phase 6: Migrate Views

**Task:** Move views to module folders

**View Mapping:**
```
Auth Module:
- auth/login.php → Modules/Auth/Views/login.php
- auth/register.php → Modules/Auth/Views/register.php
- auth/forgot-password.php → Modules/Auth/Views/forgot-password.php

Admin Module:
- admin/dashboard.php → Modules/Admin/Views/dashboard.php
- admin/users/*.php → Modules/Admin/Views/users/*.php
- admin/reports/*.php → Modules/Admin/Views/reports/*.php

Booking Module:
- bookings/*.php → Modules/Booking/Views/*.php
- cart/*.php → Modules/Booking/Views/cart/*.php
- payment/*.php → Modules/Booking/Views/payment/*.php

Destination Module:
- destinations/*.php → Modules/Destination/Views/*.php
- hotels/*.php → Modules/Destination/Views/hotels/*.php
- restaurants/*.php → Modules/Destination/Views/restaurants/*.php
- events/*.php → Modules/Destination/Views/events/*.php

TourGuide Module:
- tourguide/*.php → Modules/TourGuide/Views/*.php

AI Module:
- aitourguide/*.php → Modules/AI/Views/*.php

Social Module:
- favorites/*.php → Modules/Social/Views/*.php
- reviews/*.php → Modules/Social/Views/reviews/*.php
- messages/*.php → Modules/Social/Views/messages/*.php

Content Module:
- itinerary/*.php → Modules/Content/Views/*.php
- audioguide/*.php → Modules/Content/Views/audioguide/*.php
- notifications/*.php → Modules/Content/Views/notifications/*.php

Utility Module:
- map/*.php → Modules/Utility/Views/map/*.php
- search/*.php → Modules/Utility/Views/search/*.php

Gamification Module:
- gamification/*.php → Modules/Gamification/Views/*.php
```

### Phase 7: Update Routing

**Task:** Update router.php to handle new namespaces

**Current Router:**
```php
// Simple routing based on controller name
$controller = ucfirst($route) . 'Controller';
$controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';
```

**New Router:**
```php
// Parse module and controller
$parts = explode('/', $route);
$module = ucfirst($parts[0] ?? 'Utility');
$controllerName = ucfirst($parts[1] ?? 'Home');
$action = $parts[2] ?? 'index';

// Build namespace
$namespace = "App\\Modules\\{$module}\\Controllers";
$controllerClass = "{$namespace}\\{$controllerName}Controller";

// Load controller
$controllerFile = __DIR__ . "/../app/Modules/{$module}/Controllers/{$controllerName}Controller.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerClass();
    $controller->$action();
}
```

**URL Structure:**
```
Before: /auth/login
After: /auth/auth/login  (or /auth/login with alias)

Before: /admin/dashboard
After: /admin/admin/dashboard (or /admin/dashboard with alias)

Before: /booking/create
After: /booking/booking/create (or /booking/create with alias)
```

**Routing Aliases:**
Create aliases for common routes to keep URLs clean:
```php
$aliases = [
    'auth/login' => 'auth/auth/login',
    'auth/register' => 'auth/auth/register',
    'admin/dashboard' => 'admin/admin/dashboard',
    'booking/create' => 'booking/booking/create',
    'destinations' => 'destination/destination/index',
    // etc.
];
```

### Phase 8: Update Autoloader

**Task:** Update composer.json or create custom autoloader for new namespaces

**Option 1: Composer Autoloader (Recommended)**
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "App\\Modules\\": "app/Modules/"
        }
    }
}
```

Then run:
```bash
composer dump-autoload
```

**Option 2: Custom Autoloader**
```php
// In index.php or bootstrap
spl_autoload_register(function ($class) {
    $prefix = 'App\\Modules\\';
    $base_dir = __DIR__ . '/app/Modules/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
```

### Phase 9: Testing

**Task:** Test all modules after migration

**Test Checklist:**
- [ ] All controllers load correctly
- [ ] All models load correctly
- [ ] All services load correctly
- [ ] All repositories load correctly
- [ ] All views load correctly
- [ ] Routing works for all modules
- [ ] Authentication works
- [ ] Booking flow works
- [ ] Search works
- [ ] Admin dashboard works
- [ ] Caching works
- [ ] Performance is acceptable

**Performance Testing:**
- Benchmark page load times before and after
- Test query performance
- Test cache hit rates
- Test memory usage

### Phase 10: Documentation

**Task:** Update all documentation

**Documents to Update:**
- `docs/03_DESAIN_ARSITEKTUR_APLIKASI.md` - Update architecture diagram
- `docs/04_STRUKTUR_FOLDER_PHP_NATIVE.md` - Update folder structure
- `README.md` - Update project structure
- `docs/DEVELOPER_GUIDE.md` - Update development guide
- Create `docs/MVC_MODULAR_GUIDE.md` - New guide for modular structure

---

## TESTING PROCEDURES

### Unit Tests

**Repository Tests:**
```php
public function testRepositoryFind() {
    $repo = new UserRepository();
    $user = $repo->find(1);
    $this->assertNotNull($user);
    $this->assertEquals(1, $user['id']);
}

public function testRepositoryCreate() {
    $repo = new UserRepository();
    $id = $repo->create(['name' => 'Test', 'email' => 'test@test.com']);
    $this->assertGreaterThan(0, $id);
}
```

**Service Tests:**
```php
public function testServiceGetAll() {
    $service = new UserService();
    $users = $service->getAll();
    $this->assertIsArray($users);
    $this->assertGreaterThan(0, count($users));
}

public function testServiceCache() {
    $service = new UserService();
    $service->enableCache();
    
    // First call - cache miss
    $users1 = $service->getAll();
    
    // Second call - cache hit
    $users2 = $service->getAll();
    
    $this->assertEquals($users1, $users2);
}
```

### Integration Tests

**Controller Tests:**
```php
public function testControllerIndex() {
    $controller = new AuthController();
    $response = $controller->index();
    $this->assertNotNull($response);
}
```

**Routing Tests:**
```php
public function testRouteAuthLogin() {
    $_SERVER['REQUEST_URI'] = '/auth/login';
    $app = new App();
    $app->run();
    // Assert correct controller is loaded
}
```

---

## DEPLOYMENT CONSIDERATIONS

### Rollback Plan
- Keep old controllers/models as backup
- Use feature flags to switch between old and new structure
- Gradual migration (module by module)

### Performance Impact
- Expect initial performance dip due to autoloading
- Caching should improve performance over time
- Monitor query performance
- Optimize slow queries

### Monitoring
- Monitor cache hit rates
- Monitor query times
- Monitor memory usage
- Monitor error rates

---

## SECURITY ASPECTS

### Namespace Security
- Ensure proper access control for each module
- Validate module names in routing
- Prevent directory traversal attacks

### Service Layer Security
- Validate all inputs in service layer
- Sanitize outputs
- Implement rate limiting
- Log all service operations

### Repository Security
- Use prepared statements (already in base Repository)
- Validate query parameters
- Implement row-level security if needed

---

## PERFORMANCE OPTIMIZATION

### Caching Strategy
- Cache frequently accessed data (destinations, tour guides)
- Cache expensive queries (reports, analytics)
- Use cache tags for easy invalidation
- Implement cache warming for critical data

### Query Optimization
- Add indexes to frequently queried columns
- Use JOIN instead of multiple queries
- Implement lazy loading for relationships
- Use query builder for complex queries

### Code Optimization
- Minimize autoloading
- Use lazy loading for services
- Implement dependency injection
- Use opcode cache (OPcache)

---

## TROUBLESHOOTING

### Common Issues

**Class Not Found:**
- Check namespace is correct
- Check autoloader is configured
- Check file path matches namespace

**Cache Not Working:**
- Check cache service is configured
- Check cache is enabled in service
- Check cache key generation

**Routing Not Working:**
- Check router configuration
- Check URL structure
- Check aliases are configured

**Performance Degraded:**
- Check cache hit rates
- Check query performance
- Check for N+1 queries
- Check memory usage

---

## DOCUMENTATION UPDATES

### Architecture Documentation
- Update UML diagrams
- Update sequence diagrams
- Update component diagrams

### Developer Documentation
- Update coding standards
- Update naming conventions
- Add module development guide
- Add service development guide

### API Documentation
- Update API endpoints
- Update request/response formats
- Add authentication docs
- Add error handling docs

---

## COMPLETION CRITERIA

MVC modular reorganization is complete when:
- ✅ All controllers in module folders with correct namespaces
- ✅ All models in module folders with correct namespaces
- ✅ All repositories created for all models
- ✅ All services created for all modules
- ✅ All views in module folders
- ✅ Routing updated for new structure
- ✅ Autoloader configured
- ✅ All unit tests passing
- ✅ All integration tests passing
- ✅ Performance benchmarks show improvement
- ✅ Documentation updated
- ✅ Old structure removed or archived

---

**STATUS:** 📋 READY FOR IMPLEMENTATION  
**ESTIMATED TIME:** 19-27 days (3-4 weeks)  
**PRIORITY:** HIGH
