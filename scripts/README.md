# Scripts Directory

This directory contains all scripts for the MyWisata application, organized by purpose.

## Directory Structure

```
scripts/
├── deployment/       # Deployment-related scripts
├── maintenance/      # Maintenance scripts (backup, log rotation, security)
├── testing/          # Testing scripts (load tests, unit tests)
├── utilities/        # Utility scripts (icon generation)
└── README.md         # This file
```

## Maintenance Scripts

### backup_database.sh
Automated database backup script with compression and retention policy.

**Usage:**
```bash
./scripts/maintenance/backup_database.sh
```

**Features:**
- Daily database backup
- Compression with gzip
- Retention policy (keep last 7 days)
- Email notification on failure

### log_rotation.php
Log rotation script to manage log file sizes.

**Usage:**
```bash
php scripts/maintenance/log_rotation.php
```

**Features:**
- Rotate logs when they exceed size limit
- Compress old logs
- Retention policy
- Email notification on errors

### security_monitor.php
Security monitoring script for detecting suspicious activities.

**Usage:**
```bash
php scripts/maintenance/security_monitor.php
```

**Features:**
- Monitor failed login attempts
- Detect rate limit violations
- Monitor suspicious activities
- Email alerts on threshold exceeded

### verify_backup.sh
Verify database backup integrity.

**Usage:**
```bash
./scripts/maintenance/verify_backup.sh
```

**Features:**
- Verify backup file integrity
- Test restore capability
- Email notification on failure

## Testing Scripts

### run_load_test.sh
Run load testing scenarios using k6.

**Usage:**
```bash
./scripts/testing/run_load_test.sh
```

**Features:**
- Run predefined load test scenarios
- Generate reports
- Save results to load-tests/results/

### run_tests.sh
Run all tests (unit and integration).

**Usage:**
```bash
./scripts/testing/run_tests.sh
```

**Features:**
- Run PHPUnit tests
- Run Playwright E2E tests
- Generate coverage reports
- Save results to tests/results/

## Utility Scripts

### generate_icons.php
Generate app icons for PWA and various platforms.

**Usage:**
```bash
php scripts/utilities/generate_icons.php
```

**Features:**
- Generate icons from source image
- Multiple sizes for different platforms
- Favicon generation
- PWA icon generation

## Cron Jobs

Add these to your crontab for automated execution:

```bash
# Daily database backup at 2 AM
0 2 * * * /opt/lampp/htdocs/mywisata/scripts/maintenance/backup_database.sh

# Log rotation daily at 3 AM
0 3 * * * php /opt/lampp/htdocs/mywisata/scripts/maintenance/log_rotation.php

# Security monitoring hourly
0 * * * * php /opt/lampp/htdocs/mywisata/scripts/maintenance/security_monitor.php

# Backup verification daily at 4 AM
0 4 * * * /opt/lampp/htdocs/mywisata/scripts/maintenance/verify_backup.sh
```

## Permissions

Make sure scripts have execute permissions:

```bash
chmod +x scripts/maintenance/*.sh
chmod +x scripts/testing/*.sh
```

## Configuration

Edit scripts to configure:
- Database credentials
- Email notification settings
- Retention policies
- Threshold values
- Paths and directories

## Troubleshooting

If scripts fail:
1. Check permissions: `ls -la scripts/`
2. Check PHP path: `which php`
3. Check error logs: `tail -f logs/error.log`
4. Test manually before adding to cron

---

**Last Updated:** 2026-07-18
