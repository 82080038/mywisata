-- MyWisata Application - Create Cart Tables
-- Create tables for multi-item shopping cart
-- Created: 2026-07-16

-- Create cart_items table
CREATE TABLE IF NOT EXISTS cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_type VARCHAR(50) NOT NULL COMMENT 'tour_guide, ticket, hotel, restaurant',
    item_id BIGINT UNSIGNED NOT NULL,
    quantity INT DEFAULT 1,
    options JSON NULL COMMENT 'Additional options like dates, room type, etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_item_type (item_type),
    INDEX idx_item_id (item_id),
    UNIQUE KEY unique_user_item (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cart_promo_codes table
CREATE TABLE IF NOT EXISTS cart_promo_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    promo_code VARCHAR(50) NOT NULL,
    discount_amount DECIMAL(10, 2) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
