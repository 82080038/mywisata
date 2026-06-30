# Project Context — Tour Guide Application

## Project Overview
Tour Guide Application is a tourism marketplace web app built with PHP Native (Simple MVC), MySQL, Bootstrap 5, jQuery, and OpenStreetMap/Leaflet. It connects tourists with tour guides, destinations, hotels, restaurants, and cultural events.

## Tech Stack
- **Backend:** PHP 8.1+ (Native, no framework — custom MVC in `app/core/`)
- **Database:** MySQL 8.0+ (utf8mb4, 33 tables, InnoDB)
- **Frontend:** Bootstrap 5.3, jQuery 3.7, Font Awesome 6, SweetAlert2, DataTables, Select2, Chart.js
- **Map:** OpenStreetMap + Leaflet 1.9 (free, no API key needed)
- **Web Server:** Apache (mod_rewrite) or Nginx (PHP-FPM)
- **Local Dev:** XAMPP/LAMPP at `/opt/lampp/htdocs/wisata/`

## Architecture
- **Pattern:** Simple MVC (Model-View-Controller)
- **Entry Point:** `index.php` (front controller → `app/core/App.php` router)
- **Routing:** URL-based (`?url=controller/method/params`) via `.htaccess` rewrite
- **Database:** PDO Singleton (`app/core/Database.php`), prepared statements only
- **Autoload:** `spl_autoload_register()` — no Composer needed (optional)
- **Auth:** Session-based, bcrypt password hash, RBAC middleware
- **API:** AJAX + JSON, all responses via `$this->json()` method

## Three User Roles
1. **admin** — Full system access (dashboard, user mgmt, approvals, reports, settings)
2. **wisatawan** — Consumer (search guide, book, buy tickets, order food, register events)
3. **tour_guide** — Provider (profile, schedule, accept bookings, earnings, reviews)

## Key Directories
- `docs/` — 33 documentation files (the blueprint for building this app)
- `app/config/` — Configuration files (config.php, database.php)
- `app/core/` — Framework classes (App, Controller, Model, View, Database, Middleware, etc.)
- `app/controllers/` — Business logic controllers
- `app/models/` — Database interaction models (PDO)
- `app/views/` — HTML templates (layouts, auth, admin, wisatawan, tourguide, errors)
- `public/assets/` — CSS, JS, images, third-party libraries
- `public/uploads/` — User uploaded files (images, audio, documents, QR codes)
- `database/` — SQL migration and seed files
- `logs/` — Error log and audit log
- `cron/` — Cron job scripts (event reminder, rate limit cleanup)

## Important Constants (defined in app/config/config.php)
- `BASE_URL` — App base URL (e.g., `http://localhost/wisata/`)
- `BASE_PATH` — Absolute path to project root
- `APP_ENV` — `development` or `production`
- `APP_DEBUG` — `true` in dev, `false` in prod
- `CSRF_TOKEN` — Cross-site request forgery token
- `MAX_UPLOAD_SIZE` — Max file upload size (default 5MB)

## Security Practices
- All SQL queries use PDO prepared statements (no string concatenation)
- All form POST requires CSRF token
- All output uses `Helper::e()` (htmlspecialchars) for XSS prevention
- Password hashed with `password_hash($pass, PASSWORD_BCRYPT)`
- Session: HttpOnly, Secure, SameSite, 30-minute timeout
- Rate limiting: 60 API requests/minute per user
- Audit log for all important actions
- File upload: MIME check, size limit, random filename

## Coding Conventions
- **PHP:** PascalCase classes, camelCase methods/variables, 4-space indent
- **JS:** camelCase functions/variables, 2-space indent, `const`/`let` (no `var`)
- **CSS:** kebab-case with `tg-` prefix for custom classes, 2-space indent
- **SQL:** UPPERCASE keywords, lowercase identifiers, snake_case tables/columns
- **Files:** PascalCase for class files, snake_case for view files

## Database
- 33 tables in `database/migration.sql`
- Seed data in `database/seed.sql`
- Default admin: `admin@tourguide.app` / `admin123`
- All PKs: `BIGINT UNSIGNED AUTO_INCREMENT`
- Coordinates: `DECIMAL(10,7)` for GPS accuracy

## How to Run Locally
1. Start XAMPP/LAMPP (Apache + MySQL)
2. Create database `tour_guide_app` (utf8mb4)
3. Import `database/migration.sql` then `database/seed.sql`
4. Configure `app/config/database.php` and `app/config/config.php`
5. Set permissions: `chmod -R 777 public/uploads logs database/backup`
6. Open `http://localhost/wisata/` in browser

## Current Status
- **Phase:** Documentation complete (33 docs), code not yet implemented
- **Next Step:** Begin Fase 1 MVP — setup project structure, core classes, auth module
- **Roadmap:** 22 weeks (5.5 months) from MVP to Go Live (see `docs/26_ROADMAP_PENGEMBANGAN.md`)

## Documentation Index
See `docs/00_DAFTAR_ISI.md` for complete table of contents of all 33 documentation files.
