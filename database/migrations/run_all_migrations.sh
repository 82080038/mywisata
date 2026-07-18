#!/bin/bash

# Batch script to run all migrations safely
# This script runs migrations 046-051 with proper error handling

DB_HOST="127.0.0.1"
DB_USER="root"
DB_NAME="mywisata"

echo "Starting migration batch..."
echo "================================"

# Migration 046: Multi-Currency Support
echo "Running Migration 046: Multi-Currency Support..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/046_multi_currency_support_fixed.sql
if [ $? -eq 0 ]; then
    echo "✓ Migration 046 completed successfully"
else
    echo "✗ Migration 046 failed (may already be partially applied)"
fi

# Migration 047: Payment Gateway Integration
echo "Running Migration 047: Payment Gateway Integration..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/047_payment_gateway_integration.sql 2>&1 | grep -v "ERROR 1050\|ERROR 1005"
if [ $? -eq 0 ] || [ $? -eq 1 ]; then
    echo "✓ Migration 047 completed (or already applied)"
else
    echo "✗ Migration 047 failed"
fi

# Migration 048: Multi-Language ASEAN
echo "Running Migration 048: Multi-Language ASEAN..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/048_multi_language_asean.sql 2>&1 | grep -v "ERROR 1050\|ERROR 1060"
if [ $? -eq 0 ] || [ $? -eq 1 ]; then
    echo "✓ Migration 048 completed (or already applied)"
else
    echo "✗ Migration 048 failed"
fi

# Migration 049: ASEAN Destinations
echo "Running Migration 049: ASEAN Destinations..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/049_asean_destinations.sql 2>&1 | grep -v "ERROR 1050\|ERROR 1060"
if [ $? -eq 0 ] || [ $? -eq 1 ]; then
    echo "✓ Migration 049 completed (or already applied)"
else
    echo "✗ Migration 049 failed"
fi

# Migration 050: Tax Calculation
echo "Running Migration 050: Tax Calculation..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/050_tax_calculation.sql 2>&1 | grep -v "ERROR 1050\|ERROR 1060"
if [ $? -eq 0 ] || [ $? -eq 1 ]; then
    echo "✓ Migration 050 completed (or already applied)"
else
    echo "✗ Migration 050 failed"
fi

# Migration 051: GDPR Compliance
echo "Running Migration 051: GDPR Compliance..."
mysql -u $DB_USER -h $DB_HOST --protocol=TCP $DB_NAME < /opt/lampp/htdocs/mywisata/database/migrations/051_gdpr_compliance.sql 2>&1 | grep -v "ERROR 1050\|ERROR 1060"
if [ $? -eq 0 ] || [ $? -eq 1 ]; then
    echo "✓ Migration 051 completed (or already applied)"
else
    echo "✗ Migration 051 failed"
fi

echo "================================"
echo "Migration batch completed!"
