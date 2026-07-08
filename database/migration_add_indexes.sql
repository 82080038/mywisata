-- Performance Optimization Migration - Add Database Indexes
-- Date: 2026-07-01
-- Purpose: Add missing indexes for frequently queried columns

-- Note: Run this migration safely - it will skip existing indexes

-- Add index on bookings table for user_id and status (if not exists)
ALTER TABLE bookings ADD INDEX IF NOT EXISTS idx_user_status (user_id, status);

-- Add index on transactions table for user_id and payment_status (if not exists)
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_user_status (user_id, payment_status);

-- Add unique index on users table for email (if not exists)
ALTER TABLE users ADD UNIQUE INDEX IF NOT EXISTS idx_email (email);

-- Add index on users table for role (if not exists)
ALTER TABLE users ADD INDEX IF NOT EXISTS idx_role (role);

-- Add index on tickets table for destination_id (if not exists)
ALTER TABLE tickets ADD INDEX IF NOT EXISTS idx_destination (destination_id);

-- Add index on audit_logs table for user_id (if not exists)
ALTER TABLE audit_logs ADD INDEX IF NOT EXISTS idx_user_id (user_id);

-- Add index on audit_logs table for created_at (if not exists)
ALTER TABLE audit_logs ADD INDEX IF NOT EXISTS idx_created_at (created_at);
