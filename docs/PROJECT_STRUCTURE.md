# PROJECT STRUCTURE
# MyWisata Application
# Version: 1.0.0

## DIRECTORY OVERVIEW

```
mywisata/
├── app/                          # Application core
│   ├── config/                   # Configuration files
│   ├── core/                     # Core system classes
│   ├── controllers/              # HTTP request handlers
│   ├── models/                   # Data models
│   ├── services/                 # Business logic services
│   ├── helpers/                  # Utility classes
│   ├── middleware/               # Request middleware
│   └── views/                    # View templates
├── public/                       # Public web root
│   ├── assets/                   # Static assets
│   ├── uploads/                  # User uploads
│   └── js/                       # JavaScript files
├── database/                     # Database files
├── tests/                        # Test files
├── docs/                         # Documentation
├── prompting/                    # AI prompting system
├── logs/                         # Application logs
├── vendor/                       # Composer dependencies
└── node_modules/                 # NPM dependencies
```

## DETAILED STRUCTURE

### `/app/` - Application Core

#### `/app/config/` - Configuration Files
```
config/
├── config.php          # Main application configuration
├── database.php        # Database connection settings
├── payment.php         # Payment gateway configuration
├── redis.php           # Redis caching configuration
├── cdn.php             # CDN integration configuration
└── openai.php          # OpenAI API configuration
```

**Purpose**: Centralized configuration for all application settings including database connections, API keys, and feature flags.

#### `/app/core/` - Core System Classes
```
core/
├── App.php             # Main application router and request handler
├── Controller.php      # Base controller with common functionality
├── Model.php           # Base model with database operations
├── View.php            # View rendering engine
└── Database.php        # PDO database connection manager (singleton)
```

**Purpose**: Foundation classes that provide core functionality for the entire application.

**Key Classes**:
- `App.php`: Handles URL routing, controller instantiation, and request processing
- `Database.php`: Manages database connections using singleton pattern
- `Controller.php`: Base class for all controllers with common methods
- `Model.php`: Base class for all models with database operations
- `View.php`: Handles template rendering

#### `/app/controllers/` - HTTP Request Handlers
```
controllers/
├── AIController.php              # AI-powered features
├── AITourGuideController.php     # AI tour guide chat
├── AddressController.php         # Address cascading dropdowns
├── AdminController.php           # Admin dashboard
├── ApiController.php             # API endpoints
├── AudioGuideController.php      # Audio guide features
├── AuthController.php            # Authentication (login, register, logout)
├── AvailabilityController.php    # Tour guide availability
├── BackupController.php          # Database backup
├── BookingController.php         # Booking system
├── CartController.php            # Shopping cart
├── DataImportController.php      # Data import/export
├── DestinationController.php     # Destination management
├── EventController.php           # Event management
├── FavoriteController.php        # User favorites
├── GamificationController.php    # Gamification features
├── HomeController.php            # Homepage and basic pages
├── HotelController.php           # Hotel/accommodation management
├── ItineraryController.php       # Trip itinerary
├── LanguageController.php        # Multi-language support
├── MapController.php             # Interactive map
├── MessageController.php         # Messaging system
├── NotificationController.php    # User notifications
├── PaymentController.php         # Payment processing
├── PromoCodeController.php       # Promo code management
├── ReportController.php          # Reports and analytics
├── RestaurantController.php      # Restaurant management
├── ReviewController.php          # Reviews and ratings
├── SearchController.php          # Search functionality
├── SupplierController.php        # Supplier management
├── TestController.php            # Test utilities
├── TicketController.php           # E-ticket management
├── TourGuideController.php       # Tour guide management
└── VerificationController.php    # User verification
```

**Purpose**: Handle HTTP requests, process business logic, and return responses.

**Naming Convention**: `{Feature}Controller.php` (e.g., `DestinationController.php`)

**Base Class**: All controllers extend `Controller` class

#### `/app/models/` - Data Models
```
models/
├── Address.php                  # Address data
├── Availability.php              # Tour guide availability
├── Booking.php                  # Booking records
├── Cart.php                     # Shopping cart
├── Destination.php              # Tourist destinations
├── Event.php                    # Cultural events
├── Favorite.php                 # User favorites
├── Hotel.php                    # Hotels/accommodations
├── Message.php                  # Messages
├── Notification.php             # User notifications
├── PromoCode.php                # Promo codes
├── Restaurant.php               # Restaurants
├── Review.php                   # Reviews and ratings
├── Role.php                     # User roles
├── Setting.php                  # Application settings
├── Ticket.php                   # E-tickets
├── TourGuide.php                # Tour guide profiles
├── Transaction.php              # Payment transactions
└── User.php                     # User accounts
```

**Purpose**: Represent database entities and handle data access operations.

**Naming Convention**: `{Entity}.php` (e.g., `Destination.php`)

**Base Class**: All models extend `Model` class

**Common Methods**:
- `find($id)` - Find record by ID
- `all()` - Get all records
- `create($data)` - Create new record
- `update($id, $data)` - Update record
- `delete($id)` - Delete record
- `where($conditions)` - Add WHERE clause
- `orderBy($column, $direction)` - Add ORDER BY clause

#### `/app/services/` - Business Logic Services
```
services/
├── AssetService.php              # CDN asset management
├── ImageService.php              # Image optimization
├── OpenAIService.php             # OpenAI API integration
├── PaymentService.php            # Midtrans payment gateway
└── RedisService.php              # Redis caching operations
```

**Purpose**: Encapsulate business logic and external API integrations.

**Naming Convention**: `{Feature}Service.php` (e.g., `PaymentService.php`)

**Usage**: Called from controllers to handle complex business operations

#### `/app/helpers/` - Utility Classes
```
helpers/
├── Email.php                    # Email sending
├── FileUpload.php               # File upload handling
├── Language.php                 # Multi-language support
├── Logger.php                   # Error logging
├── RateLimiter.php              # Rate limiting
├── Session.php                  # Session management
├── SMS.php                      # SMS sending
└── Validator.php                # Input validation
```

**Purpose**: Provide utility functions and helper classes.

**Usage**: Static methods or singleton instances used throughout the application

#### `/app/middleware/` - Request Middleware
```
middleware/
└── Middleware.php               # CSRF protection, authentication checks
```

**Purpose**: Process requests before they reach controllers.

**Features**:
- CSRF token validation
- Authentication checks
- Rate limiting
- Request logging

#### `/app/views/` - View Templates
```
views/
├── layouts/                     # Layout templates
│   ├── header.php              # Page header
│   ├── footer.php              # Page footer
│   └── sidebar.php             # Sidebar navigation
├── home/                       # Home page views
│   ├── index.php               # Homepage
│   ├── about.php               # About page
│   └── contact.php             # Contact page
├── auth/                       # Authentication views
│   ├── login.php               # Login form
│   ├── register.php            # Registration form
│   └── forgot-password.php     # Password reset
├── destinations/               # Destination views
│   ├── index.php               # Destination listing
│   ├── show.php                # Destination detail
│   └── search.php              # Search results
├── tourguides/                 # Tour guide views
│   ├── index.php               # Guide listing
│   ├── show.php                # Guide detail
│   └── dashboard.php           # Guide dashboard
├── hotels/                     # Hotel views
│   ├── index.php               # Hotel listing
│   └── show.php                # Hotel detail
├── restaurants/                # Restaurant views
│   ├── index.php               # Restaurant listing
│   └── show.php                # Restaurant detail
├── events/                     # Event views
│   ├── index.php               # Event listing
│   └── show.php                # Event detail
├── bookings/                   # Booking views
│   ├── index.php               # Booking list
│   ├── create.php              # Create booking
│   └── show.php                # Booking detail
├── payment/                    # Payment views
│   ├── index.php               # Payment page
│   └── callback.php            # Payment callback
├── map/                        # Map views
│   └── index.php               # Interactive map
├── favorites/                  # Favorites views
│   └── index.php               # User favorites
├── admin/                      # Admin views
│   ├── dashboard.php           # Admin dashboard
│   ├── destinations/           # Destination management
│   ├── users/                  # User management
│   └── guides/                 # Guide management
├── errors/                     # Error pages
│   ├── 404.php                 # Not found
│   ├── 403.php                 # Forbidden
│   └── 500.php                 # Server error
└── aitourguide/                # AI tour guide views
    └── index.php               # AI chat interface
```

**Purpose**: HTML templates for rendering pages.

**Naming Convention**: `{action}.php` (e.g., `index.php`, `show.php`)

**Layout System**: Views extend layouts (header, footer, sidebar)

### `/public/` - Public Web Root

```
public/
├── .htaccess                    # Apache rewrite rules
├── manifest.json                # PWA manifest
├── sw.js                        # Service worker
├── offline.html                 # PWA offline page
├── assets/                      # Static assets
│   ├── css/                     # Stylesheets
│   │   ├── bootstrap.min.css    # Bootstrap CSS
│   │   └── custom.css          # Custom styles
│   ├── js/                      # JavaScript files
│   │   ├── bootstrap.bundle.min.js
│   │   ├── jquery.min.js
│   │   └── main.js              # Custom JavaScript
│   ├── images/                  # Images
│   │   ├── destinations/        # Destination images
│   │   ├── hotels/              # Hotel images
│   │   ├── restaurants/         # Restaurant images
│   │   └── events/              # Event images
│   └── fonts/                   # Fonts
├── js/                          # Additional JavaScript
│   ├── main.js                  # Main application JS
│   ├── sw-registration.js       # Service worker registration
│   ├── indexeddb-helper.js      # IndexedDB helper
│   └── push-notification.js     # Push notifications
└── uploads/                     # User uploads
    ├── destinations/            # Destination images
    ├── hotels/                  # Hotel images
    ├── restaurants/             # Restaurant images
    ├── profiles/                # User profile images
    └── documents/               # Document uploads
```

**Purpose**: Publicly accessible files served by the web server.

**Security**: Only files in this directory should be accessible from the web.

**Key Files**:
- `.htaccess`: URL rewriting and security rules
- `manifest.json`: PWA configuration
- `sw.js`: Service worker for offline support
- `offline.html`: Offline fallback page

### `/database/` - Database Files

```
database/
├── migration.sql                # Database schema
├── seed_data.sql                # Sample data
└── migrations/                  # Individual migration files
    ├── add_payment_fields.sql
    └── ... (other migrations)
```

**Purpose**: Database schema and seed data.

**Usage**: Import these files to set up the database.

### `/tests/` - Test Files

```
tests/
├── e2e/                         # Playwright E2E tests
│   ├── address-ui.spec.ts       # Address UI interaction tests
│   ├── address.spec.ts          # Address API tests
│   ├── admin.spec.ts            # Admin dashboard tests
│   ├── api.spec.ts              # API endpoint tests
│   ├── auth.spec.ts             # Authentication tests
│   ├── aitourguide.spec.ts     # AI tour guide tests
│   ├── booking.spec.ts          # Booking system tests
│   ├── destinations.spec.ts     # Destination tests
│   ├── events.spec.ts           # Event tests
│   ├── favorites.spec.ts        # Favorites tests
│   ├── homepage.spec.ts         # Homepage tests
│   ├── hotels.spec.ts           # Hotel tests
│   ├── map.spec.ts              # Map tests
│   ├── payment.spec.ts          # Payment tests
│   ├── restaurants.spec.ts       # Restaurant tests
│   ├── roles.spec.ts            # Role-based access tests
│   └── tourguides.spec.ts      # Tour guide tests
├── bootstrap.php                # Test bootstrap file
└── README.md                    # Test documentation
```

**Purpose**: End-to-end tests using Playwright.

**Running Tests**: `npx playwright test`

**Test Coverage**: 100 tests covering core functionality

### `/docs/` - Documentation

```
docs/
├── DEVELOPER_GUIDE.md           # This guide
├── PROJECT_STRUCTURE.md         # This file
├── PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md
├── ai_enhancement_analysis.md
├── ai_enhancement_implementation.md
├── ai_integration_guide.md
├── openai_setup_guide.md
├── cdn_integration_analysis.md
├── cdn_integration_implementation.md
├── cdn_integration_guide.md
├── cloudflare_setup_guide.md
├── payment_gateway_analysis.md
├── payment_gateway_implementation.md
├── payment_gateway_guide.md
├── redis_caching_analysis.md
├── redis_caching_implementation.md
├── redis_caching_guide.md
├── redis_installation_guide.md
├── pwa_guide.md
├── 27_PANDUAN_INSTALASI_LOKAL.md
└── ... (40+ documentation files)
```

**Purpose**: Comprehensive documentation for developers and users.

### `/prompting/` - AI Prompting System

```
prompting/
├── config.json                  # Multi-environment configuration
├── state.json                   # Development state tracking
├── README.md                    # Prompting system documentation
├── README_SETUP.md              # Setup guide
├── 01_development/              # Development module templates
│   ├── 05_DESAIN_DATABASE_MYSQL_ERD.md
│   ├── 06_CORE_SYSTEM_MVC.md
│   ├── ... (39 module templates)
├── 02_testing/                  # Testing templates
├── 03_improvement/              # Improvement templates
├── 04_documentation/            # Documentation templates
└── 05_cycle/                    # Development cycle templates
```

**Purpose**: AI-powered autonomous development system.

**Usage**: Used by AI assistants to guide development process.

### `/logs/` - Application Logs

```
logs/
├── error.log                    # Error logs
└── audit.log                    # Audit logs
```

**Purpose**: Store application logs for debugging and monitoring.

**Rotation**: Implement log rotation to prevent disk space issues.

### `/vendor/` - Composer Dependencies

**Purpose**: PHP packages installed via Composer.

**Generated**: Do not manually edit this directory.

**Key Dependencies**:
- None (application uses native PHP)

### `/node_modules/` - NPM Dependencies

**Purpose**: Node.js packages installed via npm.

**Generated**: Do not manually edit this directory.

**Key Dependencies**:
- @playwright/test - E2E testing
- TypeScript - Type checking for tests

## ROOT FILES

### Configuration Files
- `.env.example` - Environment variables template
- `.gitignore` - Git ignore rules
- `composer.json` - PHP dependencies
- `package.json` - Node.js dependencies
- `playwright.config.ts` - Playwright configuration

### Entry Point
- `index.php` - Application entry point and front controller

### Documentation
- `README.md` - Project overview and quick start
- `LICENSE` - License information

## FILE NAMING CONVENTIONS

### PHP Files
- **Classes**: `PascalCase` (e.g., `DestinationController.php`)
- **Views**: `snake_case` (e.g., `destination_list.php`)
- **Config**: `snake_case` (e.g., `database.php`)

### JavaScript Files
- **Scripts**: `kebab-case` (e.g., `main.js`, `sw-registration.js`)

### CSS Files
- **Stylesheets**: `kebab-case` (e.g., `custom.css`)

### Test Files
- **Playwright**: `kebab-case.spec.ts` (e.g., `homepage.spec.ts`)

## DATABASE STRUCTURE

### Main Database: `mywisata`
33 tables organized into:
- User management (users, roles, user_roles)
- Content (destinations, hotels, restaurants, events, tour_guides)
- Bookings (bookings, transactions, tickets)
- Social (favorites, reviews, messages)
- System (settings, notifications, promo_codes)

### Address Database: `db_alamat`
4 tables for Indonesian regions:
- provinces (34 provinces)
- regencies (514 regencies)
- districts (7,000+ districts)
- villages (83,000+ villages)

## ROUTING STRUCTURE

### URL Pattern
```
http://localhost:8080/?url=controller/method/param1/param2
```

### Examples
- `/` → `HomeController::index()`
- `/destinations` → `DestinationController::index()`
- `/destinations/show/1` → `DestinationController::show(1)`
- `/?url=address/getProvinces` → `AddressController::getProvinces()`

## DEPENDENCY FLOW

```
User Request
    ↓
index.php (Entry Point)
    ↓
App.php (Router)
    ↓
Controller (Request Handler)
    ↓
Service (Business Logic) → Model (Data Access)
    ↓
Database (Data Storage)
    ↓
View (Response Rendering)
    ↓
Response to User
```

## SECURITY STRUCTURE

### Public vs Private
- **Public**: `/public/` directory only
- **Private**: All other directories protected by web server

### Sensitive Files
- **Never commit**: `.env`, `logs/`, `uploads/`
- **Configuration**: Use environment variables
- **API Keys**: Store in config files, not in code

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18
