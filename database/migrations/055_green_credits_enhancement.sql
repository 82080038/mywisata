-- Migration 055: Green Credits Enhancement
-- This migration enhances the existing carbon tracking with green credits system
-- Date: 2026-07-18

-- Create green credits table
CREATE TABLE IF NOT EXISTS green_credits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    credits_balance INT DEFAULT 0,
    credits_earned INT DEFAULT 0,
    credits_spent INT DEFAULT 0,
    tier ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') DEFAULT 'bronze',
    eco_score INT DEFAULT 0, -- 0-100
    carbon_offset_kg DECIMAL(10, 2) DEFAULT 0,
    trees_planted_equivalent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_tier (tier),
    INDEX idx_eco_score (eco_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create green credit transactions table
CREATE TABLE IF NOT EXISTS green_credit_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    transaction_type ENUM('earned', 'spent', 'bonus', 'penalty') NOT NULL,
    amount INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    related_booking_id INT NULL,
    related_destination_id INT NULL,
    carbon_offset_kg DECIMAL(10, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create green credit rewards table
CREATE TABLE IF NOT EXISTS green_credit_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reward_name VARCHAR(255) NOT NULL,
    reward_type ENUM('discount', 'free_upgrade', 'free_tour', 'merchandise', 'donation') NOT NULL,
    description TEXT NOT NULL,
    credits_required INT NOT NULL,
    discount_percentage DECIMAL(5, 2) NULL,
    discount_amount DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    upgrade_type VARCHAR(100) NULL,
    merchandise_type VARCHAR(100) NULL,
    donation_amount DECIMAL(10, 2) NULL,
    donation_recipient VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_limited BOOLEAN DEFAULT FALSE,
    total_available INT NULL,
    total_claimed INT DEFAULT 0,
    valid_from DATE NULL,
    valid_until DATE NULL,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reward_type (reward_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create green credit claims table
CREATE TABLE IF NOT EXISTS green_credit_claims (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reward_id INT NOT NULL,
    credits_spent INT NOT NULL,
    claim_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'fulfilled') DEFAULT 'pending',
    fulfillment_details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_reward_id (reward_id),
    INDEX idx_claim_date (claim_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create eco-certified destinations table
CREATE TABLE IF NOT EXISTS eco_certified_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    certification_body VARCHAR(255) NOT NULL,
    certification_number VARCHAR(100) NOT NULL,
    certification_date DATE NOT NULL,
    expiry_date DATE NOT NULL,
    certification_level ENUM('bronze', 'silver', 'gold', 'platinum') NOT NULL,
    sustainability_score INT DEFAULT 0, -- 0-100
    green_practices JSON, -- ["solar_power", "water_recycling", "waste_management", "local_employment"]
    carbon_footprint_per_visitor DECIMAL(10, 2) NULL, -- kg CO2
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_destination_cert (destination_id, certification_number),
    INDEX idx_destination_id (destination_id),
    INDEX idx_certification_level (certification_level),
    INDEX idx_sustainability_score (sustainability_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create low-carbon routes table
CREATE TABLE IF NOT EXISTS low_carbon_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_destination_id INT NOT NULL,
    to_destination_id INT NOT NULL,
    transport_mode ENUM('walking', 'cycling', 'electric_vehicle', 'public_transport', 'train', 'bus') NOT NULL,
    distance_km DECIMAL(10, 2) NOT NULL,
    duration_minutes INT NOT NULL,
    carbon_emission_kg DECIMAL(10, 2) NOT NULL,
    alternative_carbon_emission_kg DECIMAL(10, 2) NOT NULL, -- Emission for alternative (e.g., car)
    carbon_savings_kg DECIMAL(10, 2) NOT NULL,
    route_description TEXT,
    is_recommended BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_from_destination (from_destination_id),
    INDEX idx_to_destination (to_destination_id),
    INDEX idx_transport_mode (transport_mode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample green credit rewards
INSERT IGNORE INTO green_credit_rewards (reward_name, reward_type, description, credits_required, discount_percentage, discount_amount, currency, is_active) VALUES
('Eco Discount 5%', 'discount', 'Diskon 5% untuk booking apa saja', 100, 5.00, NULL, 'IDR', TRUE),
('Eco Discount 10%', 'discount', 'Diskon 10% untuk booking apa saja', 200, 10.00, NULL, 'IDR', TRUE),
('Free Tour Guide Upgrade', 'free_upgrade', 'Upgrade gratis ke tour guide premium', 300, NULL, NULL, 'IDR', TRUE),
('Plant a Tree Donation', 'donation', 'Donasi untuk penanaman 1 pohon', 50, NULL, 15000, 'IDR', TRUE),
('Carbon Offset Donation', 'donation', 'Donasi untuk offset 10kg CO2', 75, NULL, 25000, 'IDR', TRUE);

-- Insert sample eco-certified destinations
INSERT IGNORE INTO eco_certified_destinations (destination_id, certification_body, certification_number, certification_date, expiry_date, certification_level, sustainability_score, green_practices, carbon_footprint_per_visitor) VALUES
(NULL, 'Green Tourism Indonesia', 'GTI-2024-001', '2024-01-01', '2025-12-31', 'gold', 85, '["solar_power", "water_recycling", "waste_management", "local_employment"]', 2.5),
(NULL, 'Eco-Cert Asia', 'ECA-2024-002', '2024-03-15', '2025-03-14', 'silver', 75, '["water_recycling", "waste_management", "local_employment"]', 3.2),
(NULL, 'Sustainable Travel Alliance', 'STA-2024-003', '2024-06-01', '2025-05-31', 'platinum', 92, '["solar_power", "water_recycling", "waste_management", "local_employment", "carbon_offset"]', 1.8);

-- Add green credit columns to bookings table
SET @dbname = DATABASE();
SET @tablename = 'bookings';
SET @columnname = 'green_credits_earned';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT DEFAULT 0')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'carbon_offset_kg';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' DECIMAL(10, 2) DEFAULT 0')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'eco_friendly_booking';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
