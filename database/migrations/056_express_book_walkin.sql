-- Migration 056: Express Book untuk Walk-in
-- This migration adds features for walk-in customer booking
-- Date: 2026-07-18

-- Create walk-in bookings table
CREATE TABLE IF NOT EXISTS walk_in_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_code VARCHAR(20) NOT NULL UNIQUE,
    destination_id INT NULL,
    hotel_id INT NULL,
    restaurant_id INT NULL,
    tour_guide_id INT NULL,
    booking_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'package') NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(255) NULL,
    number_of_people INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    duration_hours INT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    payment_method ENUM('cash', 'card', 'qr_code', 'transfer', 'wallet') NOT NULL,
    payment_status ENUM('pending', 'paid', 'partial', 'refunded') DEFAULT 'pending',
    payment_amount DECIMAL(10, 2) DEFAULT 0,
    special_requests TEXT,
    notes TEXT,
    staff_id INT NULL, -- Staff who processed the walk-in
    processing_device VARCHAR(100) NULL, -- 'mobile', 'tablet', 'desktop'
    processing_location VARCHAR(255) NULL, -- Front desk, ticket counter, etc
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_code (booking_code),
    INDEX idx_booking_date (booking_date),
    INDEX idx_booking_type (booking_type),
    INDEX idx_status (status),
    INDEX idx_staff_id (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create walk-in booking items table (for multiple items in one booking)
CREATE TABLE IF NOT EXISTS walk_in_booking_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    walk_in_booking_id INT NOT NULL,
    item_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'activity', 'transport') NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    start_time TIME NULL,
    end_time TIME NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_walk_in_booking_id (walk_in_booking_id),
    INDEX idx_item_type (item_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create quick booking templates table (for common walk-in scenarios)
CREATE TABLE IF NOT EXISTS quick_booking_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(255) NOT NULL,
    template_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'package') NOT NULL,
    default_duration_hours INT NULL,
    default_price DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    includes_items JSON, -- [{"item_type": "destination", "item_id": 1, "quantity": 1}]
    is_active BOOLEAN DEFAULT TRUE,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_template_type (template_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_favorite (is_favorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create walk-in booking analytics table
CREATE TABLE IF NOT EXISTS walk_in_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    booking_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'package') NOT NULL,
    total_bookings INT DEFAULT 0,
    total_revenue DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'IDR',
    average_booking_value DECIMAL(10, 2) DEFAULT 0,
    peak_hour TIME NULL,
    payment_method_distribution JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_analytics (date, booking_type),
    INDEX idx_date (date),
    INDEX idx_booking_type (booking_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample quick booking templates
INSERT IGNORE INTO quick_booking_templates (template_name, template_type, default_duration_hours, default_price, includes_items, is_active, is_favorite) VALUES
('Standard Destination Visit', 'destination', 2, 50000, '[{"item_type": "destination", "quantity": 1}]', TRUE, TRUE),
('Hotel Room Booking', 'hotel', 24, 500000, '[{"item_type": "hotel", "quantity": 1}]', TRUE, TRUE),
('Restaurant Table Booking', 'restaurant', 2, 100000, '[{"item_type": "restaurant", "quantity": 1}]', TRUE, TRUE),
('Tour Guide Half Day', 'tour_guide', 4, 300000, '[{"item_type": "tour_guide", "quantity": 1}]', TRUE, FALSE);

-- Add walk-in related columns to bookings table
SET @dbname = DATABASE();
SET @tablename = 'bookings';
SET @columnname = 'is_walk_in';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'walk_in_booking_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
