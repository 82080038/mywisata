#!/bin/bash
# MyWisata Application - Backup Verification Script
# This script verifies that the latest backup can be restored
# Cron: 0 3 * * * /opt/lampp/htdocs/mywisata/cron/verify_backup.sh (1 hour after backup)

# ============================================
# CONFIGURATION
# ============================================
DB_NAME="mywisata"
DB_USER="root"
DB_PASS=""
DB_HOST="localhost"
BACKUP_DIR="/opt/lampp/htdocs/mywisata/database/backup"
LOG_FILE="/opt/lampp/htdocs/mywisata/logs/backup_verification.log"
TEST_DB="${DB_NAME}_test_restore"

# ============================================
# FUNCTIONS
# ============================================

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

send_alert() {
    local MESSAGE="$1"
    # Add email notification logic here if needed
    # echo "$MESSAGE" | mail -s "Backup Alert - MyWisata" admin@mywisata.com
    log_message "ALERT: $MESSAGE"
}

# ============================================
# MAIN EXECUTION
# ============================================

log_message "=========================================="
log_message "Starting backup verification process"

# Find the latest backup
LATEST_BACKUP=$(ls -t "${BACKUP_DIR}/${DB_NAME}"_*.sql.gz 2>/dev/null | head -1)

if [ -z "$LATEST_BACKUP" ]; then
    log_message "ERROR: No backup found to verify"
    send_alert "No backup found for verification"
    exit 1
fi

log_message "Verifying backup: $(basename "$LATEST_BACKUP")"

# Create test database
log_message "Creating test database: ${TEST_DB}"

if [ -z "$DB_PASS" ]; then
    mysql -h"$DB_HOST" -u"$DB_USER" -e "DROP DATABASE IF EXISTS ${TEST_DB}" 2>> "$LOG_FILE"
    mysql -h"$DB_HOST" -u"$DB_USER" -e "CREATE DATABASE ${TEST_DB}" 2>> "$LOG_FILE"
else
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${TEST_DB}" 2>> "$LOG_FILE"
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE ${TEST_DB}" 2>> "$LOG_FILE"
fi

if [ $? -ne 0 ]; then
    log_message "ERROR: Failed to create test database"
    send_alert "Failed to create test database for backup verification"
    exit 1
fi

# Restore backup to test database
log_message "Restoring backup to test database"

if [ -z "$DB_PASS" ]; then
    gunzip -c "$LATEST_BACKUP" | mysql -h"$DB_HOST" -u"$DB_USER" "$TEST_DB" 2>> "$LOG_FILE"
else
    gunzip -c "$LATEST_BACKUP" | mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$TEST_DB" 2>> "$LOG_FILE"
fi

if [ $? -ne 0 ]; then
    log_message "ERROR: Failed to restore backup to test database"
    send_alert "Failed to restore backup during verification"
    
    # Cleanup test database
    mysql -h"$DB_HOST" -u"$DB_USER" -e "DROP DATABASE IF EXISTS ${TEST_DB}" 2>> "$LOG_FILE"
    exit 1
fi

# Verify data integrity
log_message "Verifying data integrity"

TABLE_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" "$TEST_DB" -e "SHOW TABLES" 2>/dev/null | wc -l)
USER_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" "$TEST_DB" -e "SELECT COUNT(*) FROM users" 2>/dev/null | tail -1)
BOOKING_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" "$TEST_DB" -e "SELECT COUNT(*) FROM bookings" 2>/dev/null | tail -1)

log_message "Tables: ${TABLE_COUNT}, Users: ${USER_COUNT}, Bookings: ${BOOKING_COUNT}"

# Check thresholds
if [ "$TABLE_COUNT" -lt 10 ] || [ "$USER_COUNT" -lt 1 ]; then
    log_message "ERROR: Backup verification failed - data integrity check failed"
    send_alert "Backup verification failed for $(basename "$LATEST_BACKUP") - data integrity check failed"
    
    # Cleanup test database
    mysql -h"$DB_HOST" -u"$DB_USER" -e "DROP DATABASE IF EXISTS ${TEST_DB}" 2>> "$LOG_FILE"
    exit 1
fi

log_message "Backup verification PASSED: $(basename "$LATEST_BACKUP")"

# Cleanup test database
log_message "Cleaning up test database"

mysql -h"$DB_HOST" -u"$DB_USER" -e "DROP DATABASE IF EXISTS ${TEST_DB}" 2>> "$LOG_FILE"

log_message "Backup verification process completed successfully"
log_message "=========================================="

exit 0
