-- MyWisata Application - Promo Codes Database Migration
-- Add promo code tables to existing database
-- Run this after main migration.sql

-- Disable foreign key checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: promo_codes
-- ============================================
CREATE TABLE IF NOT EXISTS promo_codes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    description     TEXT,
    discount_type   ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    discount_value  DECIMAL(10,2) NOT NULL,
    max_discount_amount DECIMAL(10,2) DEFAULT 0,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_uses        INT DEFAULT 0,
    max_uses_per_user INT DEFAULT 0,
    used_count      INT DEFAULT 0,
    start_date      DATETIME NOT NULL,
    end_date        DATETIME NOT NULL,
    applicable_types JSON,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_by      BIGINT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: promo_code_usage
-- ============================================
CREATE TABLE IF NOT EXISTS promo_code_usage (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    promo_id        BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promo_id) REFERENCES promo_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    INDEX idx_promo (promo_id),
    INDEX idx_user (user_id),
    INDEX idx_transaction (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: gift_vouchers
-- ============================================
CREATE TABLE IF NOT EXISTS gift_vouchers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(20) NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    description     TEXT,
    amount          DECIMAL(10,2) NOT NULL,
    balance         DECIMAL(10,2) NOT NULL,
    recipient_email VARCHAR(150),
    recipient_name  VARCHAR(100),
    sender_id       BIGINT UNSIGNED,
    sender_name     VARCHAR(100),
    message         TEXT,
    expiry_date     DATETIME,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    is_used         TINYINT(1) NOT NULL DEFAULT 0,
    used_at         DATETIME,
    used_by         BIGINT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (used_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_active (is_active),
    INDEX idx_expiry (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: gift_voucher_usage
-- ============================================
CREATE TABLE IF NOT EXISTS gift_voucher_usage (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voucher_id      BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    transaction_id  BIGINT UNSIGNED NOT NULL,
    amount_used     DECIMAL(10,2) NOT NULL,
    remaining_balance DECIMAL(10,2) NOT NULL,
    used_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (voucher_id) REFERENCES gift_vouchers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    INDEX idx_voucher (voucher_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Insert sample promo codes
-- ============================================
INSERT INTO promo_codes (code, name, description, discount_type, discount_value, max_discount_amount, min_order_amount, max_uses, max_uses_per_user, start_date, end_date, applicable_types, is_active) VALUES
('WELCOME10', 'Welcome Discount', 'Get 10% off your first booking', 'percentage', 10, 50000, 100000, 1000, 1, NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH), '["booking_guide","ticket","hotel","restaurant","event"]', 1),
('SUMMER20', 'Summer Special', '20% off all bookings this summer', 'percentage', 20, 100000, 200000, 500, 3, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH), '["booking_guide","hotel"]', 1),
('FLAT50K', 'Flat Discount', 'Flat 50,000 IDR discount', 'fixed', 50000, 0, 150000, 200, 2, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), '["ticket","restaurant","event"]', 1);

-- ============================================
-- Migration Complete
-- ============================================
