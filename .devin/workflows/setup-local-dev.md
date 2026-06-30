---
description: How to set up the local development environment for Tour Guide Application
---

# Setup Local Development Environment

## Prerequisites
- XAMPP/LAMPP with PHP 8.1+ and MySQL 8.0+
- Git
- VS Code (recommended) with PHP Intelephense extension

## Steps

1. Clone the repository:
   ```bash
   git clone https://github.com/82080038/wisata.git /opt/lampp/htdocs/wisata
   ```

2. Start Apache and MySQL via XAMPP/LAMPP control panel

3. Create database:
   ```bash
   /opt/lampp/bin/mysql -u root -e "CREATE DATABASE tour_guide_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. Import schema and seed data:
   ```bash
   /opt/lampp/bin/mysql -u root tour_guide_app < /opt/lampp/htdocs/wisata/database/migration.sql
   /opt/lampp/bin/mysql -u root tour_guide_app < /opt/lampp/htdocs/wisata/database/seed.sql
   ```

5. Configure database connection in `app/config/database.php`:
   ```php
   return [
       'host'    => 'localhost',
       'dbname'  => 'tour_guide_app',
       'user'    => 'root',
       'pass'    => '',
       'charset' => 'utf8mb4',
   ];
   ```

6. Configure app in `app/config/config.php`:
   ```php
   define('BASE_URL', 'http://localhost/wisata/');
   define('APP_ENV', 'development');
   define('APP_DEBUG', true);
   ```

7. Set folder permissions (Linux):
   ```bash
   chmod -R 777 /opt/lampp/htdocs/wisata/public/uploads
   chmod -R 777 /opt/lampp/htdocs/wisata/logs
   chmod -R 777 /opt/lampp/htdocs/wisata/database/backup
   ```

8. Open browser: `http://localhost/wisata/`

9. Login with default admin: `admin@tourguide.app` / `admin123`

## Troubleshooting
- **Blank page:** Check `logs/error.log`, ensure `display_errors = On` in php.ini (dev only)
- **404 error:** Ensure `mod_rewrite` is enabled and `.htaccess` exists in project root
- **DB connection failed:** Verify MySQL is running and credentials in `database.php` are correct
- **Upload failed:** Check folder permissions on `public/uploads/`

## Full guide
See `docs/27_PANDUAN_INSTALASI_LOKAL.md` for detailed instructions.
