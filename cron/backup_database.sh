#!/bin/bash
# MyWisata Application - Automated Database Backup Script
# This script performs daily database backup with compression and retention management
# Cron: 0 2 * * * /opt/lampp/htdocs/mywisata/cron/backup_database.sh

# ============================================
# CONFIGURATION
# ============================================
DB_NAME="mywisata"
DB_USER="root"
DB_PASS=""
DB_HOST="localhost"
BACKUP_DIR="/opt/lampp/htdocs/mywisata/database/backup"
LOG_FILE="/opt/lampp/htdocs/mywisata/logs/backup.log"
RETENTION_DAYS=7

# ============================================
# FUNCTIONS
# ============================================

log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

create_backup() {
    local DATE=$(date +%Y%m%d_%H%M%S)
    local FILENAME="${DB_NAME}_${DATE}.sql"
    local OUTPUT_FILE="${BACKUP_DIR}/${FILENAME}"
    
    log_message "Starting database backup: ${FILENAME}"
    
    # Create backup directory if not exists
    mkdir -p "$BACKUP_DIR"
    
    # Perform backup using mysqldump
    if [ -z "$DB_PASS" ]; then
        mysqldump -h"$DB_HOST" -u"$DB_USER" "$DB_NAME" > "$OUTPUT_FILE" 2>> "$LOG_FILE"
    else
        mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$OUTPUT_FILE" 2>> "$LOG_FILE"
    fi
    
    # Check if backup was successful
    if [ $? -eq 0 ] && [ -f "$OUTPUT_FILE" ]; then
        log_message "Backup created successfully: ${FILENAME}"
        
        # Compress the backup
        gzip "$OUTPUT_FILE"
        
        if [ $? -eq 0 ]; then
            local COMPRESSED_FILE="${OUTPUT_FILE}.gz"
            local FILE_SIZE=$(du -h "$COMPRESSED_FILE" | cut -f1)
            log_message "Backup compressed successfully: ${FILENAME}.gz (Size: ${FILE_SIZE})"
        else
            log_message "WARNING: Compression failed for ${FILENAME}"
        fi
    else
        log_message "ERROR: Backup failed for ${FILENAME}"
        return 1
    fi
}

cleanup_old_backups() {
    log_message "Cleaning up backups older than ${RETENTION_DAYS} days"
    
    # Delete backups older than retention period
    DELETED=$(find "$BACKUP_DIR" -name "${DB_NAME}_*.sql.gz" -mtime +$RETENTION_DAYS -delete -print 2>/dev/null | wc -l)
    
    if [ "$DELETED" -gt 0 ]; then
        log_message "Deleted ${DELETED} old backup(s)"
    else
        log_message "No old backups to delete"
    fi
}

verify_latest_backup() {
    log_message "Verifying latest backup"
    
    # Find the latest backup
    local LATEST_BACKUP=$(ls -t "${BACKUP_DIR}/${DB_NAME}"_*.sql.gz 2>/dev/null | head -1)
    
    if [ -z "$LATEST_BACKUP" ]; then
        log_message "WARNING: No backup found to verify"
        return 1
    fi
    
    # Test if the gzip file is valid
    if gzip -t "$LATEST_BACKUP" 2>> "$LOG_FILE"; then
        log_message "Latest backup verification passed: $(basename "$LATEST_BACKUP")"
        return 0
    else
        log_message "ERROR: Latest backup verification failed: $(basename "$LATEST_BACKUP")"
        return 1
    fi
}

# ============================================
# MAIN EXECUTION
# ============================================

log_message "=========================================="
log_message "Starting automated backup process"

# Create backup
if create_backup; then
    # Cleanup old backups
    cleanup_old_backups
    
    # Verify latest backup
    verify_latest_backup
    
    log_message "Backup process completed successfully"
else
    log_message "ERROR: Backup process failed"
    exit 1
fi

log_message "=========================================="

# Exit with success
exit 0
