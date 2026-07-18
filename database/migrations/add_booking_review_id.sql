-- MyWisata Application - Add Review ID to Bookings Table Migration
-- Add review_id column to bookings table for linking reviews
-- Created: 2026-07-16

-- Add review_id column to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS review_id BIGINT UNSIGNED NULL AFTER total_amount,
ADD INDEX IF NOT EXISTS idx_review_id (review_id);
