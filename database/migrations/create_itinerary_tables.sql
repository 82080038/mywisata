-- MyWisata Application - Create Itinerary Tables
-- Create tables for itinerary builder functionality
-- Created: 2026-07-16

-- Create itineraries table
CREATE TABLE IF NOT EXISTS itineraries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    budget DECIMAL(15, 2) NULL,
    participants INT DEFAULT 1,
    is_public TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary_items table
CREATE TABLE IF NOT EXISTS itinerary_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    itinerary_id BIGINT UNSIGNED NOT NULL,
    item_type VARCHAR(50) NOT NULL COMMENT 'destination, hotel, restaurant, event, tour_guide',
    item_id BIGINT UNSIGNED NOT NULL,
    day_number INT DEFAULT 1,
    start_time TIME NULL,
    end_time TIME NULL,
    notes TEXT NULL,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (itinerary_id) REFERENCES itineraries(id) ON DELETE CASCADE,
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_day_number (day_number),
    INDEX idx_order_index (order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary_shares table
CREATE TABLE IF NOT EXISTS itinerary_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    itinerary_id BIGINT UNSIGNED NOT NULL,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itinerary_id) REFERENCES itineraries(id) ON DELETE CASCADE,
    INDEX idx_share_token (share_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
