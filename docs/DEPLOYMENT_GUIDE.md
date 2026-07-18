# DEPLOYMENT GUIDE
# MyWisata Application
# Version: 1.0.0
# Last Updated: 2026-07-18

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [Server Requirements](#server-requirements)
3. [Initial Server Setup](#initial-server-setup)
4. [LAMP Stack Installation](#lamp-stack-installation)
5. [Application Deployment](#application-deployment)
6. [Apache Configuration](#apache-configuration)
7. [Nginx Configuration](#nginx-configuration)
8. [SSL/HTTPS Setup](#sslhttps-setup)
9. [Cron Jobs Setup](#cron-jobs-setup)
10. [Performance Optimization](#performance-optimization)
11. [Security Hardening](#security-hardening)
12. [Monitoring](#monitoring)
13. [Troubleshooting](#troubleshooting)
14. [Deployment Checklist](#deployment-checklist)

---

## OVERVIEW

This comprehensive guide covers deploying the MyWisata Application to a production Linux VPS server. It includes server setup, web server configuration (Apache/Nginx), SSL setup, cron jobs, performance optimization, and security hardening.

---

## SERVER REQUIREMENTS

### Minimum Requirements
- **OS**: Ubuntu 20.04 LTS / 22.04 LTS or Debian 11+
- **CPU**: 2 cores
- **RAM**: 2 GB (recommended 4 GB+)
- **Storage**: 20 GB SSD (recommended 50 GB SSD)
- **Bandwidth**: 1 TB/month (recommended unlimited)

### Software Requirements
- **PHP**: 8.1+ or 8.2+
- **MySQL**: 8.0+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Git**: For deployment

---

## INITIAL SERVER SETUP

### 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Configure Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

### 3. Create Non-Root User (Optional but Recommended)

```bash
sudo adduser mywisata
sudo usermod -aG sudo mywisata
```

### 4. Set Timezone

```bash
sudo timedatectl set-timezone Asia/Jakarta
```

---

## LAMP STACK INSTALLATION

### Option A: Apache + PHP + MySQL

#### Install Apache

```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
```

#### Install MySQL

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
sudo systemctl enable mysql
sudo systemctl start mysql
```

#### Install PHP 8.1/8.2

```bash
# For PHP 8.1
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.1 libapache2-mod-php8.1 php8.1-mysql php8.1-curl \
    php8.1-gd php8.1-mbstring php8.1-xml php8.1-zip php8.1-intl -y

# For PHP 8.2 (recommended)
sudo apt install php8.2 libapache2-mod-php8.2 php8.2-mysql php8.2-curl \
    php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl -y
```

#### Install Additional Tools

```bash
sudo apt install git unzip composer -y
```

#### Install phpMyAdmin (Optional)

```bash
sudo apt install phpmyadmin -y
```

### Option B: Nginx + PHP-FPM + MySQL

See [Nginx Configuration](#nginx-configuration) section below.

---

## APPLICATION DEPLOYMENT

### 1. Clone/Copy Project

```bash
cd /var/www/
git clone <repository-url> mywisata
# or
sudo cp -r /path/to/mywisata /var/www/mywisata
```

### 2. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/mywisata
sudo chmod -R 755 /var/www/mywisata
sudo chmod -R 775 /var/www/mywisata/public/uploads
sudo chmod -R 775 /var/www/mywisata/logs
```

### 3. Configure Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE mywisata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mywisata'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON mywisata.* TO 'mywisata'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Create Address Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE db_alamat CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON db_alamat.* TO 'mywisata'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Import Database Schema

```bash
mysql -u mywisata -p mywisata < /var/www/mywisata/database/migration.sql
mysql -u mywisata -p mywisata < /var/www/mywisata/database/seed.sql
```

### 6. Update Configuration

Edit `app/config/database.php`:

```php
return [
    'default' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'mywisata',
        'username' => 'mywisata',
        'password' => 'strong_password_here',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'address' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'db_alamat',
        'username' => 'mywisata',
        'password' => 'strong_password_here',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
];
```

Edit `app/config/config.php`:

```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('BASE_URL', 'https://yourdomain.com/');
define('ENCRYPTION_KEY', 'change-this-encryption-key-in-production-use-32-characters');
```

---

## APACHE CONFIGURATION

### 1. Enable Required Modules

```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl restart apache2
```

### 2. Create Virtual Host

```bash
sudo nano /etc/apache2/sites-available/mywisata.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/mywisata/public

    <Directory /var/www/mywisata/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mywisata_error.log
    CustomLog ${APACHE_LOG_DIR}/mywisata_access.log combined
</VirtualHost>
```

### 3. Enable Site

```bash
sudo a2ensite mywisata.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### 4. Configure PHP

Edit `/etc/php/8.1/apache2/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

### 5. Restart Apache

```bash
sudo systemctl restart apache2
```

---

## NGINX CONFIGURATION

### 1. Install Nginx and PHP-FPM

```bash
sudo apt install nginx php8.1-fpm php8.1-mysql php8.1-curl php8.1-gd \
    php8.1-mbstring php8.1-xml php8.1-zip -y
```

### 2. Configure PHP-FPM

Edit `/etc/php/8.1/fpm/pool.d/www.conf`:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### 3. Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/mywisata
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/mywisata/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/mywisata_access.log;
    error_log /var/log/nginx/mywisata_error.log;

    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /(\.env|\.git|composer\.json|composer\.lock|package\.json|package-lock\.json)$ {
        deny all;
    }

    location ~ /app/ {
        deny all;
    }

    location ~ /logs/ {
        deny all;
    }

    location ~ /tests/ {
        deny all;
    }

    location ~ /docs/ {
        deny all;
    }

    location ~ /prompting/ {
        deny all;
    }

    # Allow access to uploads and assets
    location /uploads/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location /assets/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
}
```

### 4. Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/mywisata /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Start Services

```bash
sudo systemctl start nginx
sudo systemctl start php8.1-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.1-fpm
```

---

## SSL/HTTPS SETUP

### 1. Install Certbot

#### For Apache

```bash
sudo apt install certbot python3-certbot-apache -y
```

#### For Nginx

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Obtain SSL Certificate

#### For Apache

```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

#### For Nginx

```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 3. Auto-Renewal Setup

```bash
sudo certbot renew --dry-run
```

Certbot automatically creates a cron job for renewal.

### 4. Advanced SSL Configuration

#### For Apache

Edit `/etc/apache2/sites-available/mywisata-le-ssl.conf`:

```apache
<IfModule mod_ssl.c>
    <VirtualHost *:443>
        ServerName yourdomain.com
        ServerAlias www.yourdomain.com
        DocumentRoot /var/www/mywisata/public
        
        # SSL Configuration
        SSLEngine on
        SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
        SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
        
        # SSL Protocols
        SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1
        
        # SSL Ciphers
        SSLCipherSuite HIGH:!aNULL:!MD5:!3DES
        SSLHonorCipherOrder on
        
        # HSTS
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
        
        # Security Headers
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-XSS-Protection "1; mode=block"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
    </VirtualHost>
</IfModule>
```

#### For Nginx

Certbot automatically updates the configuration. The final config will include:

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    
    # ... rest of configuration
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

### 5. Restart Web Server

#### Apache

```bash
sudo systemctl restart apache2
```

#### Nginx

```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

## CRON JOBS SETUP

### 1. Edit Crontab

```bash
sudo crontab -e
```

### 2. Essential Cron Jobs

```cron
# MyWisata Application Cron Jobs

# Database Backup - Daily at 2 AM
0 2 * * * mysqldump -u mywisata -p'password' mywisata > /var/www/mywisata/backups/db_backup_$(date +\%Y\%m\%d).sql

# Backup Cleanup - Weekly on Sunday at 5 AM
0 5 * * 0 find /var/www/mywisata/backups -name "*.sql" -mtime +30 -delete

# Log Rotation - Monthly on 1st at 4 AM
0 4 1 * * find /var/www/mywisata/logs -name "*.log" -mtime +30 -delete

# Session Cleanup - Daily at 1 AM
0 1 * * * mysql -u mywisata -p'password' mywisata -e "DELETE FROM sessions WHERE expires < NOW()"

# SSL Renewal Check - Daily at 3 AM
0 3 * * * certbot renew --quiet --deploy-hook "systemctl reload apache2"
# or for Nginx:
# 0 3 * * * certbot renew --quiet --deploy-hook "systemctl reload nginx"

# Process Monitoring - Every 5 minutes
*/5 * * * * systemctl status apache2 > /dev/null || systemctl start apache2
*/5 * * * * systemctl status mysql > /dev/null || systemctl start mysql
```

### 3. Monitor Cron Jobs

```bash
# Check cron service status
sudo systemctl status cron

# View cron logs
sudo grep CRON /var/log/syslog

# List current cron jobs
sudo crontab -l
```

---

## PERFORMANCE OPTIMIZATION

### 1. PHP Optimization

Edit `/etc/php/8.1/apache2/php.ini` or `/etc/php/8.1/fpm/php.ini`:

```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

### 2. MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 64M
max_connections = 100
```

### 3. Apache Optimization

Enable compression and caching in `.htaccess`:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 30 days"
    ExpiresByType image/png "access plus 30 days"
    ExpiresByType text/css "access plus 7 days"
    ExpiresByType application/javascript "access plus 7 days"
</IfModule>
```

### 4. Nginx Optimization

Add to server block:

```nginx
# FastCGI Cache
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=mywisata:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

location ~ \.php$ {
    fastcgi_cache mywisata;
    fastcgi_cache_valid 200 60m;
    fastcgi_cache_bypass $skip_cache;
    fastcgi_no_cache $skip_cache;
    add_header X-Cache-Status $upstream_cache_status;
    
    # ... rest of PHP configuration
}

# Browser Caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 5. Enable Redis Caching (Optional)

```bash
sudo apt install redis-server php8.1-redis -y
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

---

## SECURITY HARDENING

### 1. Disable Server Tokens

#### Apache

Edit `/etc/apache2/conf-available/security.conf`:

```apache
ServerTokens Prod
ServerSignature Off
```

#### Nginx

Add to `http` block in `/etc/nginx/nginx.conf`:

```nginx
server_tokens off;
```

### 2. Rate Limiting

#### Nginx

Add to `http` block:

```nginx
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;
```

Add to server block:

```nginx
location /api/ {
    limit_req zone=api burst=5 nodelay;
}

location /auth/login {
    limit_req zone=general burst=5 nodelay;
}
```

### 3. Block Bad Bots

Add to Nginx server block:

```nginx
if ($http_user_agent ~* (bot|crawl|spider|scraper)) {
    return 403;
}
```

### 4. File Permissions

```bash
# Ensure sensitive files are not accessible
sudo chmod 600 /var/www/mywisata/.env
sudo chmod 644 /var/www/mywisata/app/config/*.php
```

### 5. Disable Directory Browsing

#### Apache

Add to `.htaccess` or VirtualHost:

```apache
Options -Indexes
```

#### Nginx

Already configured in the main configuration.

---

## MONITORING

### 1. Log Files

| Log | Path |
|-----|------|
| Apache error | /var/log/apache2/mywisata_error.log |
| Apache access | /var/log/apache2/mywisata_access.log |
| Nginx error | /var/log/nginx/mywisata_error.log |
| Nginx access | /var/log/nginx/mywisata_access.log |
| PHP error | /var/log/php8.1/error.log |
| MySQL | /var/log/mysql/error.log |
| App error | /var/www/mywisata/logs/error.log |
| App audit | /var/www/mywisata/logs/audit.log |

### 2. Monitor Logs

```bash
# Real-time log monitoring
sudo tail -f /var/log/nginx/mywisata_access.log
sudo tail -f /var/log/nginx/mywisata_error.log
sudo tail -f /var/www/mywisata/logs/error.log
```

### 3. System Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop -y

# Monitor system resources
htop
```

### 4. Uptime Monitoring

Set up external monitoring:
- Pingdom
- UptimeRobot
- StatusCake

---

## TROUBLESHOOTING

### 1. Check Web Server Status

#### Apache

```bash
sudo systemctl status apache2
```

#### Nginx

```bash
sudo systemctl status nginx
```

### 2. Check PHP-FPM Status

```bash
sudo systemctl status php8.1-fpm
```

### 3. Check MySQL Status

```bash
sudo systemctl status mysql
```

### 4. Test Configuration

#### Apache

```bash
sudo apache2ctl configtest
```

#### Nginx

```bash
sudo nginx -t
```

### 5. Check Disk Space

```bash
df -h
```

### 6. Check Memory Usage

```bash
free -h
```

### 7. Restart Services

```bash
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
```

---

## DEPLOYMENT CHECKLIST

### Pre-Deployment
- [ ] Server requirements met
- [ ] Domain name pointing to VPS IP
- [ ] SSH access configured
- [ ] Firewall configured

### Application Setup
- [ ] LAMP/LNMP stack installed
- [ ] Database created and schema imported
- [ ] Configuration files updated (BASE_URL, DB credentials)
- [ ] File permissions set correctly
- [ ] Virtual host configured
- [ ] Web server restarted

### Security
- [ ] SSL/HTTPS installed
- [ ] HTTP redirects to HTTPS
- [ ] Security headers configured
- [ ] Server tokens disabled
- [ ] Rate limiting configured
- [ ] Sensitive files protected
- [ ] Directory browsing disabled

### Performance
- [ ] PHP opcache enabled
- [ ] MySQL optimized
- [ ] Gzip compression enabled
- [ ] Browser caching configured
- [ ] Redis caching enabled (optional)

### Maintenance
- [ ] Cron jobs configured
- [ ] Database backup scheduled
- [ ] Log rotation configured
- [ ] SSL auto-renewal working
- [ ] Process monitoring active

### Testing
- [ ] Test login functionality
- [ ] Test basic user flows
- [ ] Test file uploads
- [ ] Test API endpoints
- [ ] Check error logs
- [ ] Monitor system resources

### Monitoring
- [ ] Uptime monitoring configured
- [ ] Error logging active
- [ ] Performance monitoring setup
- [ ] Alerts configured

---

## ADDITIONAL RESOURCES

### Documentation
- [Developer Guide](docs/DEVELOPER_GUIDE.md)
- [Setup Guide](docs/SETUP_GUIDE.md)
- [Project Structure](docs/PROJECT_STRUCTURE.md)

### External Links
- [Apache Documentation](https://httpd.apache.org/docs/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Let's Encrypt](https://letsencrypt.org/)
- [Certbot Documentation](https://certbot.eff.org/docs/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18  
**Application Status**: Production Ready
