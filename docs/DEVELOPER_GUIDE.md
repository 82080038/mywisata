# DEVELOPER GUIDE
# MyWisata Application - Tour Guide Platform
# Version: 1.0.0
# Last Updated: 2026-07-18

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Project Structure](#project-structure)
4. [Technology Stack](#technology-stack)
5. [Development Setup](#development-setup)
6. [Configuration](#configuration)
7. [Database](#database)
8. [Coding Standards](#coding-standards)
9. [API Endpoints](#api-endpoints)
10. [Testing](#testing)
11. [Deployment](#deployment)
12. [Troubleshooting](#troubleshooting)
13. [Contributing](#contributing)

---

## OVERVIEW

MyWisata Application is a comprehensive tour guide marketplace platform that connects travelers with professional tour guides, destinations, hotels, restaurants, and cultural events across Indonesia.

### Key Features
- **Tour Guide Booking**: Find and book professional tour guides
- **Destination Management**: Browse and discover tourist destinations
- **Hotel & Accommodation**: Search and book hotels/homestays
- **Restaurant & Culinary**: Discover local cuisine and restaurants
- **Event & Festival**: Browse cultural events and festivals
- **E-Ticket System**: Digital tickets with QR code verification
- **Interactive Map**: OpenStreetMap integration with geolocation
- **AI Tour Guide**: Chatbot for destination recommendations
- **Payment Integration**: Midtrans payment gateway
- **PWA Support**: Progressive Web App capabilities
- **Multi-language**: Support for Indonesian and English
- **Address System**: Cascading dropdowns for Indonesian regions

### Application Status
- **Development Phase**: Complete (39 modules finished)
- **Testing Status**: 57/100 Playwright tests passing (57%)
- **Production Ready**: Core features are production-ready
- **Last Major Update**: 2026-07-18

---

## ARCHITECTURE

### Design Pattern
The application follows a **Simple MVC (Model-View-Controller)** architecture with additional layers:

```
┌─────────────────────────────────────────┐
│         Presentation Layer               │
│  (Views, Templates, Frontend Assets)    │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Controller Layer                 │
│  (Request Handling, Business Logic)     │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Service Layer                    │
│  (Business Logic, External APIs)        │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Model Layer                      │
│  (Data Access, Database Operations)     │
└─────────────────────────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│         Database Layer                   │
│  (MySQL, PDO, Connections)              │
└─────────────────────────────────────────┘
```

### Key Components

#### 1. Core System (`app/core/`)
- **App.php**: Main application router and request handler
- **Controller.php**: Base controller with common functionality
- **Model.php**: Base model with database operations
- **View.php**: View rendering engine
- **Database.php**: PDO database connection manager (singleton pattern)

#### 2. Controllers (`app/controllers/`)
Handle HTTP requests and coordinate between models and views:
- `HomeController`: Homepage and basic pages
- `AuthController`: Authentication (login, register, logout)
- `DestinationController`: Destination management
- `TourGuideController`: Tour guide booking
- `HotelController`: Hotel/accommodation management
- `RestaurantController`: Restaurant management
- `EventController`: Event management
- `BookingController`: Booking system
- `PaymentController`: Payment processing
- `AddressController`: Address cascading dropdowns
- `AIController`: AI-powered features
- And more...

#### 3. Models (`app/models/`)
Represent database entities and handle data operations:
- `User`: User accounts and authentication
- `Destination`: Tourist destinations
- `TourGuide`: Tour guide profiles
- `Hotel`: Hotel/accommodation data
- `Restaurant`: Restaurant data
- `Event`: Cultural events
- `Booking`: Booking records
- `Transaction`: Payment transactions
- And more...

#### 4. Services (`app/services/`)
Business logic and external integrations:
- `OpenAIService`: OpenAI API integration
- `PaymentService`: Midtrans payment gateway
- `RedisService`: Redis caching operations
- `AssetService`: CDN asset management
- `ImageService`: Image optimization
- And more...

#### 5. Helpers (`app/helpers/`)
Utility functions and classes:
- `Session`: Session management
- `Validator`: Input validation
- `Logger`: Error logging
- `FileUpload`: File upload handling
- `Email`: Email sending
- `RateLimiter`: Rate limiting
- `Language`: Multi-language support

#### 6. Middleware (`app/middleware/`)
Request processing middleware:
- `Middleware.php`: CSRF protection, authentication checks

---

## PROJECT STRUCTURE

```
mywisata/
├── app/
│   ├── config/              # Configuration files
│   │   ├── config.php      # Main application config
│   │   ├── database.php    # Database connections
│   │   ├── payment.php     # Payment gateway config
│   │   ├── redis.php       # Redis config
│   │   ├── cdn.php         # CDN config
│   │   └── openai.php      # OpenAI config
│   ├── core/               # Core system files
│   │   ├── App.php         # Main application router
│   │   ├── Controller.php  # Base controller
│   │   ├── Model.php       # Base model
│   │   ├── View.php        # View renderer
│   │   └── Database.php    # Database connection
│   ├── controllers/        # HTTP request handlers
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── DestinationController.php
│   │   └── ... (30+ controllers)
│   ├── models/             # Data models
│   │   ├── User.php
│   │   ├── Destination.php
│   │   └── ... (30+ models)
│   ├── services/           # Business logic services
│   │   ├── OpenAIService.php
│   │   ├── PaymentService.php
│   │   ├── RedisService.php
│   │   └── ... (10+ services)
│   ├── helpers/            # Utility classes
│   │   ├── Session.php
│   │   ├── Validator.php
│   │   ├── Logger.php
│   │   └── ... (10+ helpers)
│   ├── middleware/         # Request middleware
│   │   └── Middleware.php
│   └── views/              # View templates
│       ├── layouts/        # Layout templates
│       │   ├── header.php
│       │   ├── footer.php
│       │   └── sidebar.php
│       ├── home/           # Home views
│       ├── auth/           # Auth views
│       ├── destinations/   # Destination views
│       └── ... (30+ view folders)
├── public/                 # Public web root
│   ├── assets/            # Static assets
│   │   ├── css/           # Stylesheets
│   │   ├── js/            # JavaScript files
│   │   ├── images/        # Images
│   │   └── fonts/         # Fonts
│   ├── uploads/           # User uploads
│   ├── .htaccess          # Apache rewrite rules
│   ├── manifest.json      # PWA manifest
│   ├── sw.js              # Service worker
│   └── offline.html       # PWA offline page
├── database/              # Database files
│   ├── migration.sql      # Database schema
│   └── seed_data.sql      # Sample data
├── tests/                 # Test files
│   ├── e2e/               # Playwright E2E tests
│   │   ├── homepage.spec.ts
│   │   ├── auth.spec.ts
│   │   └── ... (17 test files)
│   ├── bootstrap.php      # Test bootstrap
│   └── README.md          # Test documentation
├── docs/                  # Documentation
│   ├── DEVELOPER_GUIDE.md  # This file
│   ├── PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md
│   ├── ai_integration_guide.md
│   ├── openai_setup_guide.md
│   └── ... (40+ documentation files)
├── prompting/             # Prompting system (AI development)
│   ├── config.json        # Multi-environment config
│   ├── state.json         # Development state
│   └── ... (prompting templates)
├── logs/                  # Application logs
│   ├── error.log          # Error logs
│   └── audit.log          # Audit logs
├── vendor/                # Composer dependencies
├── node_modules/          # NPM dependencies
├── .env.example           # Environment variables template
├── .gitignore             # Git ignore rules
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
├── playwright.config.ts    # Playwright config
└── index.php              # Application entry point
```

---

## TECHNOLOGY STACK

### Backend
- **Language**: PHP 8.1+
- **Framework**: Custom MVC (No framework dependency)
- **Database**: MySQL 8.0+
- **Database Access**: PDO (PHP Data Objects)
- **Session Management**: Native PHP Sessions
- **Caching**: Redis (optional)

### Frontend
- **CSS Framework**: Bootstrap 5.3
- **JavaScript Library**: jQuery 3.7
- **UI Components**: SweetAlert2 (alerts)
- **Icons**: Font Awesome 6.4
- **Maps**: OpenStreetMap + Leaflet
- **PWA**: Service Worker + IndexedDB

### Third-Party Integrations
- **Payment Gateway**: Midtrans
- **AI**: OpenAI GPT-4
- **CDN**: Cloudflare (optional)
- **Email**: SMTP (Gmail/other)

### Development Tools
- **Testing**: Playwright (E2E)
- **Package Manager**: Composer (PHP), npm (Node.js)
- **Version Control**: Git
- **Web Server**: Apache (XAMPP) or PHP built-in server

---

## DEVELOPMENT SETUP

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer (PHP package manager)
- Node.js 16+ and npm
- Apache web server (or PHP built-in server)
- Git

### Installation Steps

#### 1. Clone Repository
```bash
git clone <repository-url>
cd mywisata
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Install Node.js Dependencies
```bash
npm install
```

#### 4. Install Playwright Browsers
```bash
npx playwright install chromium
```

#### 5. Configure Environment
```bash
cp .env.example .env
# Edit .env with your configuration
```

Or configure directly in:
- `app/config/config.php`
- `app/config/database.php`

#### 6. Setup Database
```bash
# Create database
mysql -u root -p
CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
mysql -u root -p mywisata < database/migration.sql

# Import seed data (optional)
mysql -u root -p mywisata < database/seed_data.sql
```

#### 7. Setup Address Database (for cascading dropdowns)
```bash
# Create address database
mysql -u root -p
CREATE DATABASE db_alamat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import address data (if available)
# or use existing db_alamat database
```

#### 8. Configure Web Server

**Option A: Apache (XAMPP)**
```bash
# Ensure XAMPP is running
sudo /opt/lampp/lampp start

# Access at: http://localhost/mywisata
```

**Option B: PHP Built-in Server**
```bash
php -S localhost:8080

# Access at: http://localhost:8080
```

#### 9. Run Tests
```bash
# Run all Playwright tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/homepage.spec.ts

# Run with HTML report
npx playwright test --reporter=html
```

---

## CONFIGURATION

### Main Configuration (`app/config/config.php`)

Key settings:
```php
APP_ENV = 'development'          // Environment mode
APP_DEBUG = true                 // Debug mode
BASE_URL = 'http://localhost/mywisata/'
APP_NAME = 'MyWisata Application'
```

### Database Configuration (`app/config/database.php`)

Two database connections:
```php
'default' => [
    'host' => '127.0.0.1',
    'database' => 'mywisata',
    'username' => 'root',
    'password' => '',
],

'address' => [
    'host' => '127.0.0.1',
    'database' => 'db_alamat',
    'username' => 'root',
    'password' => '',
],
```

### Environment Variables (.env)

See `.env.example` for all available variables:
- Database credentials
- API keys (Midtrans, OpenAI)
- Email settings
- Redis configuration
- CDN settings
- Security settings

### Multi-Environment Configuration

For development across multiple machines (Linux/Windows), use:
- `prompting/config.json` - Contains all environment-specific settings
- Supports multiple environments: local, staging, production
- OS-specific paths and configurations

---

## DATABASE

### Database Schema

The application uses 33 tables organized into:

#### Core Tables
- `users` - User accounts
- `roles` - User roles (admin, user, guide, supplier)
- `user_roles` - User-role relationships

#### Content Tables
- `destinations` - Tourist destinations
- `hotels` - Hotels/accommodations
- `restaurants` - Restaurants
- `events` - Cultural events
- `tour_guides` - Tour guide profiles

#### Booking Tables
- `bookings` - Booking records
- `transactions` - Payment transactions
- `tickets` - E-tickets with QR codes

#### Address Tables (separate database)
- `provinces` - Indonesian provinces
- `regencies` - Regencies/kabupaten
- `districts` - Districts/kecamatan
- `villages` - Villages/desa

#### Support Tables
- `favorites` - User favorites
- `reviews` - Reviews and ratings
- `notifications` - User notifications
- `messages` - Messaging system
- `promo_codes` - Promo codes
- `settings` - Application settings

### Database Connection

Access database using the Database singleton:
```php
$db = Database::getInstance();  // Default connection
$db = Database::getInstance('address');  // Address connection
```

### Query Examples

```php
// Simple query
$result = $db->query("SELECT * FROM users WHERE id = :id", ['id' => 1])->fetchAll();

// Transaction
$db->beginTransaction();
try {
    $db->query("INSERT INTO bookings ...");
    $db->query("UPDATE destinations ...");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
}
```

---

## CODING STANDARDS

### PHP Standards
- Follow PSR-12 coding style
- Use strict types where possible
- Always use prepared statements (PDO)
- Validate all user inputs
- Use meaningful variable names
- Add docblock comments for functions

### Example Controller Method
```php
/**
 * Get destination details
 * 
 * @param int $id Destination ID
 * @return void
 */
public function show($id) {
    try {
        // Validate input
        if (!is_numeric($id)) {
            $this->json(['status' => 'error', 'message' => 'Invalid ID'], 400);
            return;
        }
        
        // Get data
        $destination = $this->model('Destination')->find($id);
        
        if (!$destination) {
            $this->json(['status' => 'error', 'message' => 'Not found'], 404);
            return;
        }
        
        // Return response
        $this->json(['status' => 'success', 'data' => $destination]);
        
    } catch (Exception $e) {
        Logger::error('Destination show error', ['error' => $e->getMessage()]);
        $this->json(['status' => 'error', 'message' => 'Server error'], 500);
    }
}
```

### Security Best Practices
1. **Always use prepared statements** to prevent SQL injection
2. **Validate all inputs** using Validator helper
3. **Escape output** using `$this->escape()` for XSS prevention
4. **Use CSRF tokens** for all form submissions
5. **Never store passwords in plain text** - use password_hash()
6. **Implement rate limiting** for sensitive endpoints
7. **Log errors** for debugging and monitoring

### File Organization
- One class per file
- File name matches class name
- Use namespaces for better organization
- Keep controllers thin - move logic to services
- Use models for data access only

---

## API ENDPOINTS

### REST API Structure

All API endpoints follow this pattern:
```
http://localhost:8080/?url=controller/method
```

### Common Endpoints

#### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration
- `POST /auth/logout` - User logout
- `POST /auth/forgot-password` - Password reset

#### Destinations
- `GET /destinations` - List destinations
- `GET /destinations/show/{id}` - Get destination details
- `POST /destinations/search` - Search destinations

#### Tour Guides
- `GET /tourguides` - List tour guides
- `GET /tourguides/show/{id}` - Get guide details
- `POST /tourguides/book` - Book a guide

#### Bookings
- `GET /bookings` - List user bookings
- `POST /bookings/create` - Create booking
- `GET /bookings/show/{id}` - Get booking details

#### Payments
- `POST /payment/create` - Create payment
- `POST /payment/callback` - Payment callback
- `GET /payment/status/{id}` - Check payment status

#### Address (Cascading Dropdowns)
- `GET /address/getProvinces` - Get all provinces
- `GET /address/getRegencies?province_id={id}` - Get regencies
- `GET /address/getDistricts?regency_id={id}` - Get districts
- `GET /address/getVillages?district_id={id}` - Get villages

#### AI Features
- `POST /ai/recommendations` - Get AI recommendations
- `POST /ai/chat` - AI chat conversation
- `POST /ai/itinerary` - Generate itinerary

### API Response Format

All API responses follow this format:
```json
{
  "status": "success|error",
  "data": {},
  "message": "Optional message",
  "count": 0
}
```

---

## TESTING

### Playwright E2E Tests

Located in `tests/e2e/` directory.

#### Running Tests
```bash
# Run all tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/homepage.spec.ts

# Run with specific browser
npx playwright test --project=chromium

# Run with HTML report
npx playwright test --reporter=html

# Run in headed mode (show browser)
npx playwright test --headed
```

#### Test Structure
```typescript
import { test, expect } from '@playwright/test';

test.describe('Feature Name', () => {
  test('should do something', async ({ page }) => {
    await page.goto('http://localhost:8080');
    // Test logic
    expect(something).toBe(expected);
  });
});
```

#### Current Test Coverage
- **Total Tests**: 100
- **Passed**: 57 (57%)
- **Failed**: 43 (43%)
- **Coverage**: Homepage, Auth, Destinations, Hotels, Restaurants, Events, Bookings, Payments, Map, Favorites, Roles, Tour Guides, API, Admin, Address API

#### Test Categories
1. **Homepage Tests** (5/5 passed)
2. **Authentication Tests** (5/5 passed)
3. **Destinations Tests** (5/5 passed)
4. **Hotels Tests** (9/9 passed)
5. **Restaurants Tests** (9/9 passed)
6. **Events Tests** (5/5 passed)
7. **Booking Tests** (4/4 passed)
8. **Payment Tests** (4/4 passed)
9. **Map Tests** (4/4 passed)
10. **Favorites Tests** (4/4 passed)
11. **Role-Based Access Tests** (8/8 passed)
12. **Tour Guides Tests** (2/2 passed)
13. **API Tests** (6/6 passed)
14. **Admin Tests** (2/2 passed)
15. **Address API Tests** (10/10 passed)
16. **Address UI Tests** (0/33 passed) - JavaScript not implemented
17. **AI Tour Guide Tests** (0/1 passed) - Requires OpenAI config

---

## DEPLOYMENT

### Production Checklist

#### 1. Environment Configuration
- Set `APP_ENV = 'production'`
- Set `APP_DEBUG = false`
- Update `BASE_URL` to production URL
- Configure production database credentials
- Set strong `ENCRYPTION_KEY`
- Update API keys (Midtrans, OpenAI)

#### 2. Security
- Enable HTTPS/SSL
- Configure proper file permissions
- Disable error display
- Enable rate limiting
- Configure firewall rules
- Set up regular backups

#### 3. Performance
- Enable Redis caching
- Configure CDN for static assets
- Optimize database queries
- Enable GZIP compression
- Minify CSS/JS files
- Configure browser caching

#### 4. Monitoring
- Set up error logging
- Configure uptime monitoring
- Set up performance monitoring
- Configure database backups
- Set up log rotation

#### 5. Deployment Steps
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev
npm install --production

# 3. Run migrations
mysql -u root -p production_db < database/migration.sql

# 4. Clear caches
rm -rf logs/*
rm -rf public/uploads/temp/*

# 5. Set permissions
chmod 755 public/uploads
chmod 644 public/uploads/*

# 6. Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

---

## TROUBLESHOOTING

### Common Issues

#### 1. Database Connection Failed
**Problem**: Cannot connect to database
**Solution**:
- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `app/config/database.php`
- Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`
- Check firewall settings

#### 2. 404 Errors
**Problem**: Pages not found
**Solution**:
- Check `.htaccess` is present in `public/`
- Ensure mod_rewrite is enabled: `sudo a2enmod rewrite`
- Restart Apache: `sudo systemctl restart apache2`
- Check `BASE_URL` in config

#### 3. Session Not Working
**Problem**: User not staying logged in
**Solution**:
- Check session save path permissions
- Ensure `session_start()` is called
- Check cookie settings in `app/config/config.php`
- Verify browser cookies are enabled

#### 4. File Upload Failed
**Problem**: Cannot upload files
**Solution**:
- Check `UPLOAD_PATH` permissions: `chmod 755 public/uploads`
- Verify `MAX_UPLOAD_SIZE` in PHP.ini
- Check disk space
- Ensure file type is in `ALLOWED_IMAGE_TYPES`

#### 5. Payment Gateway Error
**Problem**: Midtrans payment fails
**Solution**:
- Verify API keys in config
- Check Midtrans dashboard status
- Ensure callback URL is accessible
- Test with sandbox mode first

#### 6. Playwright Tests Failing
**Problem**: Tests fail to run
**Solution**:
- Ensure PHP server is running: `php -S localhost:8080`
- Check database is accessible
- Verify test URLs are correct
- Update Playwright: `npx playwright install`

---

## CONTRIBUTING

### Development Workflow

1. **Create Branch**
```bash
git checkout -b feature/your-feature-name
```

2. **Make Changes**
- Follow coding standards
- Add tests for new features
- Update documentation

3. **Test Changes**
```bash
npx playwright test
```

4. **Commit Changes**
```bash
git add .
git commit -m "Description of changes"
```

5. **Push Branch**
```bash
git push origin feature/your-feature-name
```

6. **Create Pull Request**
- Describe changes
- Reference related issues
- Include test results

### Code Review Checklist
- [ ] Code follows PSR-12 standards
- [ ] No hardcoded values (use config)
- [ ] Input validation implemented
- [ ] Error handling in place
- [ ] Security best practices followed
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] No debug code left

### Git Commit Messages
Use conventional commit format:
```
feat: add new feature
fix: fix bug in existing feature
docs: update documentation
test: add or update tests
refactor: refactor code
style: formatting changes
chore: maintenance tasks
```

---

## ADDITIONAL RESOURCES

### Documentation
- [Installation Guide](docs/27_PANDUAN_INSTALASI_LOKAL.md)
- [Playwright Test Report](docs/PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md)
- [AI Integration Guide](docs/ai_integration_guide.md)
- [OpenAI Setup Guide](docs/openai_setup_guide.md)
- [Payment Gateway Guide](docs/payment_gateway_guide.md)
- [Redis Caching Guide](docs/redis_caching_guide.md)
- [CDN Integration Guide](docs/cdn_integration_guide.md)
- [PWA Guide](docs/pwa_guide.md)

### Configuration Files
- [Environment Variables](.env.example)
- [Multi-Environment Config](prompting/config.json)
- [Playwright Config](playwright.config.ts)

### External Links
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Playwright Documentation](https://playwright.dev/)
- [Midtrans Documentation](https://docs.midtrans.com/)
- [OpenAI Documentation](https://platform.openai.com/docs)

---

## SUPPORT

For questions or issues:
1. Check this documentation
2. Review existing issues
3. Check test reports
4. Contact development team

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18  
**Maintained By**: Development Team  
**License**: MIT
