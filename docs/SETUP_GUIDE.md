# SETUP AND INSTALLATION GUIDE
# MyWisata Application
# Version: 1.0.0
# Last Updated: 2026-07-18

## TABLE OF CONTENTS

1. [Prerequisites](#prerequisites)
2. [Installation Steps](#installation-steps)
3. [Configuration](#configuration)
4. [Database Setup](#database-setup)
5. [Running the Application](#running-the-application)
6. [Testing](#testing)
7. [Troubleshooting](#troubleshooting)
8. [Next Steps](#next-steps)

---

## PREREQUISITES

### Required Software

#### 1. PHP
- **Version**: 8.1 or higher
- **Extensions Required**:
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `curl`
  - `gd`
  - `xml`
  - `zip`
  - `intl`

**Check PHP Version**:
```bash
php -v
```

**Check PHP Extensions**:
```bash
php -m | grep -E "pdo_mysql|mbstring|json|curl|gd|xml|zip|intl"
```

#### 2. MySQL
- **Version**: 8.0 or higher
- **Tools**: phpMyAdmin (optional but recommended)

**Check MySQL Version**:
```bash
mysql --version
```

#### 3. Node.js and npm
- **Node.js**: 16.0 or higher
- **npm**: 8.0 or higher

**Check Node.js Version**:
```bash
node -v
npm -v
```

#### 4. Web Server
Choose one of:
- Apache (with mod_rewrite enabled) - Recommended for production
- Nginx (with PHP-FPM)
- PHP built-in server - For development only

#### 5. Git
- For cloning the repository

**Check Git Version**:
```bash
git --version
```

### Optional Software

#### 1. Redis Server
- For caching (optional but recommended for production)
- **Version**: 6.0 or higher

#### 2. Composer
- For PHP dependency management (optional - no external PHP dependencies)
- **Version**: 2.0 or higher

---

## INSTALLATION STEPS

### Step 1: Clone Repository

```bash
# Clone the repository
git clone <repository-url>
cd mywisata
```

### Step 2: Install Node.js Dependencies

```bash
# Install npm packages
npm install

# Install Playwright browsers
npx playwright install chromium
```

### Step 3: Install PHP Dependencies (Optional)

```bash
# If using Composer
composer install
```

### Step 4: Configure Environment

#### Option A: Using .env File (Recommended)

```bash
# Copy environment template
cp .env.example .env

# Edit .env with your configuration
nano .env
```

#### Option B: Direct Configuration

Edit configuration files directly:
- `app/config/config.php` - Main application settings
- `app/config/database.php` - Database connections

### Step 5: Set File Permissions

```bash
# Make logs directory writable
chmod 755 logs

# Make uploads directory writable
chmod 755 public/uploads
chmod 644 public/uploads/*

# Make .htaccess readable
chmod 644 public/.htaccess
```

---

## CONFIGURATION

### Main Configuration (`app/config/config.php`)

Key settings to configure:

```php
// Application Environment
define('APP_ENV', 'development');  // development, staging, production
define('APP_DEBUG', true);          // true in development, false in production

// Base URL
define('BASE_URL', 'http://localhost/mywisata/');  // Adjust for your setup

// Security
define('ENCRYPTION_KEY', 'change-this-encryption-key-in-production-use-32-characters');

// Email Settings
define('MAIL_FROM', 'admin@mywisata.com');
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
```

### Database Configuration (`app/config/database.php`)

Configure two database connections:

```php
return [
    'default' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'mywisata',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    
    'address' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'db_alamat',  // Address database for cascading dropdowns
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];
```

### Payment Gateway Configuration

#### Midtrans (Optional)

Edit `app/config/config.php` or `.env`:

```php
define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-xxxxx');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-xxxxx');
define('MIDTRANS_MERCHANT_ID', 'your-merchant-id');
define('MIDTRANS_IS_PRODUCTION', false);  // true for production
```

### OpenAI Configuration (Optional)

Edit `app/config/openai.php` or `.env`:

```php
define('OPENAI_API_KEY', 'sk-your-api-key-here');
define('OPENAI_MODEL', 'gpt-4');
define('OPENAI_TEMPERATURE', 0.7);
define('OPENAI_MAX_TOKENS', 1000);
```

### Redis Configuration (Optional)

Edit `app/config/redis.php` or `.env`:

```php
define('REDIS_ENABLED', true);
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASSWORD', '');
define('REDIS_DATABASE', 0);
```

---

## DATABASE SETUP

### Step 1: Create Main Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
exit;
```

### Step 2: Import Database Schema

```bash
# Import main database schema
mysql -u root -p mywisata < database/migration.sql

# Import seed data (optional)
mysql -u root -p mywisata < database/seed.sql
```

### Step 3: Create Address Database

```bash
# Login to MySQL
mysql -u root -p

# Create address database
CREATE DATABASE db_alamat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit MySQL
exit;
```

### Step 4: Import Address Data (If Available)

```bash
# If you have address data SQL file
mysql -u root -p db_alamat < database/address_data.sql
```

**Note**: If you don't have address data, the API will still work but return empty results. You can use the existing `db_alamat` database if it's already set up on your system.

### Step 5: Verify Database Setup

```bash
# Check main database
mysql -u root -p -e "USE mywisata; SHOW TABLES;"

# Check address database
mysql -u root -p -e "USE db_alamat; SHOW TABLES;"
```

Expected output for `mywisata`:
- users, roles, user_roles
- destinations, hotels, restaurants, events, tour_guides
- bookings, transactions, tickets
- favorites, reviews, messages
- notifications, settings, promo_codes
- And more (33 tables total)

Expected output for `db_alamat`:
- provinces
- regencies
- districts
- villages

---

## RUNNING THE APPLICATION

### Option A: PHP Built-in Server (Development)

```bash
# Start PHP server
php -S localhost:8080

# Access application
# Open browser: http://localhost:8080
```

**Advantages**:
- Quick setup
- No server configuration needed
- Good for development

**Disadvantages**:
- Not suitable for production
- Limited performance
- No URL rewriting by default

### Option B: XAMPP/LAMPP (Development)

```bash
# Start XAMPP/LAMPP
sudo /opt/lampp/lampp start

# Place project in htdocs
# /opt/lampp/htdocs/mywisata

# Access application
# Open browser: http://localhost/mywisata
```

**Advantages**:
- Complete stack (Apache, MySQL, PHP)
- Easy to use
- phpMyAdmin included

**Disadvantages**:
- Not for production
- Limited configuration options

### Option C: Apache (Production)

```bash
# Install Apache
sudo apt install apache2

# Enable mod_rewrite
sudo a2enmod rewrite

# Configure VirtualHost
sudo nano /etc/apache2/sites-available/mywisata.conf
```

VirtualHost configuration:
```apache
<VirtualHost *:80>
    ServerName mywisata.local
    DocumentRoot /var/www/mywisata/public
    
    <Directory /var/www/mywisata/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/mywisata_error.log
    CustomLog ${APACHE_LOG_DIR}/mywisata_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite mywisata.conf

# Restart Apache
sudo systemctl restart apache2

# Add to /etc/hosts
echo "127.0.0.1 mywisata.local" | sudo tee -a /etc/hosts
```

### Option D: Nginx (Production)

```bash
# Install Nginx and PHP-FPM
sudo apt install nginx php-fpm

# Configure Nginx
sudo nano /etc/nginx/sites-available/mywisata
```

Nginx configuration:
```nginx
server {
    listen 80;
    server_name mywisata.local;
    root /var/www/mywisata/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/mywisata /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

---

## TESTING

### Run Playwright Tests

```bash
# Run all tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/homepage.spec.ts

# Run with browser visible (headed mode)
npx playwright test --headed

# Run with HTML report
npx playwright test --reporter=html

# View HTML report
npx playwright show-report
```

### Test Categories

1. **Homepage Tests** - Basic homepage functionality
2. **Authentication Tests** - Login, register, logout
3. **Destination Tests** - Destination listing and details
4. **Hotel Tests** - Hotel listing and Islamic-friendly features
5. **Restaurant Tests** - Restaurant listing and halal features
6. **Event Tests** - Event listing and registration
7. **Booking Tests** - Booking system
8. **Payment Tests** - Payment processing
9. **Map Tests** - Interactive map
10. **Address API Tests** - Cascading dropdowns API
11. **Role-Based Access Tests** - Authentication and authorization
12. **API Tests** - API endpoints

### Expected Test Results

- **Total Tests**: 100
- **Expected Pass**: 57 (57%)
- **Expected Fail**: 43 (43%)

**Note**: Some tests fail due to missing external configurations (OpenAI API, Redis, CDN). Core functionality tests should all pass.

---

## TROUBLESHOOTING

### Issue: Database Connection Failed

**Symptoms**:
- Error: "Database connection failed"
- Application shows database error

**Solutions**:
1. Check MySQL is running:
   ```bash
   sudo systemctl status mysql
   # or for XAMPP
   sudo /opt/lampp/lampp status
   ```

2. Verify database exists:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```

3. Check credentials in `app/config/database.php`

4. Test connection manually:
   ```bash
   mysql -u root -p mywisata
   ```

### Issue: 404 Errors

**Symptoms**:
- Pages not found
- URLs not working

**Solutions**:
1. Check `.htaccess` exists in `public/` directory

2. Enable mod_rewrite (Apache):
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. Check `BASE_URL` in configuration

4. For PHP built-in server, use `?url=` parameter:
   ```
   http://localhost:8080/?url=destinations
   ```

### Issue: File Upload Failed

**Symptoms**:
- Cannot upload images
- Upload errors

**Solutions**:
1. Check directory permissions:
   ```bash
   chmod 755 public/uploads
   chmod 644 public/uploads/*
   ```

2. Check `MAX_UPLOAD_SIZE` in PHP.ini and config

3. Verify file type is in `ALLOWED_IMAGE_TYPES`

4. Check disk space

### Issue: Session Not Working

**Symptoms**:
- User not staying logged in
- Session lost

**Solutions**:
1. Check session save path permissions

2. Verify `session_start()` is called in `index.php`

3. Check session configuration in `app/config/config.php`

4. Clear browser cookies

### Issue: Playwright Tests Failing

**Symptoms**:
- Tests fail to run
- Browser not launching

**Solutions**:
1. Ensure PHP server is running:
   ```bash
   php -S localhost:8080
   ```

2. Reinstall Playwright browsers:
   ```bash
   npx playwright install chromium
   ```

3. Check test URLs are correct

4. Run tests in headed mode to see what's happening:
   ```bash
   npx playwright test --headed
   ```

### Issue: Address Cascading Dropdowns Not Working

**Symptoms**:
- Address dropdowns empty
- API returns errors

**Solutions**:
1. Verify `db_alamat` database exists

2. Check database configuration in `app/config/database.php`

3. Test API directly:
   ```bash
   curl "http://localhost:8080/?url=address/getProvinces"
   ```

4. Ensure address database has data

### Issue: Payment Gateway Not Working

**Symptoms**:
- Payment fails
- Midtrans errors

**Solutions**:
1. Verify Midtrans API keys in configuration

2. Check Midtrans account status

3. Test with sandbox mode first

4. Ensure callback URL is accessible

### Issue: AI Features Not Working

**Symptoms**:
- AI chat not responding
- Recommendations not working

**Solutions**:
1. Verify OpenAI API key in configuration

2. Check OpenAI account has credits

3. Test API key manually:
   ```bash
   curl https://api.openai.com/v1/models \
     -H "Authorization: Bearer YOUR_API_KEY"
   ```

4. Check network connectivity

---

## NEXT STEPS

### After Successful Installation

1. **Create Admin Account**
   - Register first user
   - Manually set role to 'admin' in database
   - Or use seed data if available

2. **Configure Email**
   - Set up SMTP credentials
   - Test email sending
   - Configure email templates

3. **Setup Payment Gateway** (Optional)
   - Create Midtrans account
   - Get API keys
   - Configure in application
   - Test with sandbox mode

4. **Setup Redis** (Optional)
   - Install Redis server
   - Configure in application
   - Enable caching
   - Monitor performance

5. **Setup CDN** (Optional)
   - Create Cloudflare account
   - Configure CDN
   - Update asset URLs
   - Test CDN delivery

6. **Configure OpenAI** (Optional)
   - Create OpenAI account
   - Get API key
   - Configure in application
   - Test AI features

7. **Run Full Test Suite**
   ```bash
   npx playwright test
   ```

8. **Review Documentation**
   - Read [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md)
   - Review [`docs/PROJECT_STRUCTURE.md`](docs/PROJECT_STRUCTURE.md)
   - Check other documentation in `docs/` folder

### For Production Deployment

1. **Security Checklist**
   - Change all default passwords
   - Set `APP_DEBUG = false`
   - Use strong `ENCRYPTION_KEY`
   - Enable HTTPS/SSL
   - Configure firewall
   - Set up regular backups

2. **Performance Optimization**
   - Enable Redis caching
   - Configure CDN
   - Optimize database queries
   - Enable GZIP compression
   - Minify CSS/JS files

3. **Monitoring**
   - Set up error logging
   - Configure uptime monitoring
   - Set up performance monitoring
   - Configure database backups
   - Set up log rotation

4. **Deployment**
   - Follow deployment guide in [`docs/25_DEPLOYMENT_SERVER.md`](docs/25_DEPLOYMENT_SERVER.md)
   - Use version control
   - Deploy to staging first
   - Test thoroughly
   - Deploy to production

---

## ADDITIONAL RESOURCES

### Documentation
- [Developer Guide](docs/DEVELOPER_GUIDE.md) - Comprehensive developer documentation
- [Project Structure](docs/PROJECT_STRUCTURE.md) - Detailed project structure
- [Test Report](docs/PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md) - Playwright test results
- [Installation Guide (Indonesian)](docs/27_PANDUAN_INSTALASI_LOKAL.md) - Indonesian installation guide

### Configuration Files
- [.env.example](.env.example) - Environment variables template
- [app/config/config.php](app/config/config.php) - Main configuration
- [app/config/database.php](app/config/database.php) - Database configuration
- [playwright.config.ts](playwright.config.ts) - Playwright configuration

### External Links
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Playwright Documentation](https://playwright.dev/)
- [Midtrans Documentation](https://docs.midtrans.com/)
- [OpenAI Documentation](https://platform.openai.com/docs)

---

## SUPPORT

If you encounter issues not covered in this guide:

1. Check the [Developer Guide](docs/DEVELOPER_GUIDE.md) troubleshooting section
2. Review the [Test Report](docs/PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md) for known issues
3. Check existing documentation in the `docs/` folder
4. Search for similar issues in the repository
5. Contact the development team

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18  
**Application Status**: Development Complete (39/39 modules)  
**Testing Status**: 57/100 tests passing (57%)
