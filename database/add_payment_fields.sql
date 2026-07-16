-- MyWisata Application - Add Payment Fields Migration
-- Add payment-related fields to transactions table
-- Created: 2026-07-16

-- Add guide_id column for guide payouts (if not exists)
ALTER TABLE transactions 
ADD COLUMN guide_id BIGINT UNSIGNED NULL AFTER payment_proof;

-- Add index for guide_id
CREATE INDEX idx_guide_id ON transactions(guide_id);

-- Add foreign key for guide_id
ALTER TABLE transactions 
ADD CONSTRAINT fk_transactions_guide_id 
FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE SET NULL;
