-- MyWisata Application - Create Promo Codes Table
-- Create tables for promo code and voucher management
-- Created: 2026-07-16

-- Create promo_codes table
CREATE TABLE IF NOT EXISTS promo_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10, 2) NOT NULL,
    max_discount DECIMAL(10, 2) NULL COMMENT 'Maximum discount amount for percentage type',
    min_purchase DECIMAL(10, 2) DEFAULT 0 COMMENT 'Minimum purchase amount to use promo',
    usage_limit INT NULL COMMENT 'Total usage limit',
    usage_count INT DEFAULT 0 COMMENT 'Total times used',
    valid_from TIMESTAMP NULL COMMENT 'Valid from date',
    valid_until TIMESTAMP NULL COMMENT 'Valid until date',
    applicable_to VARCHAR(50) DEFAULT 'all' COMMENT 'all, booking, ticket, hotel, restaurant',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_valid_period (valid_from, valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create promo_code_usage table
CREATE TABLE IF NOT EXISTS promo_code_usage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    promo_code VARCHAR(50) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    discount_amount DECIMAL(10, 2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_promo_code (promo_code),
    INDEX idx_user_id (user_id),
    INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
