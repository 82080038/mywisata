-- MyWisata Application - Add Password Reset Fields Migration
-- Add reset_token and reset_token_expiry fields to users table
-- Created: 2026-07-16

-- Add reset_token column
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(255) NULL AFTER remember_token,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token;

-- Add index for reset_token for faster lookups
CREATE INDEX idx_reset_token ON users(reset_token);
