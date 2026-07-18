-- Migration 046: Multi-Currency Support for Regional/Global Expansion
-- This migration adds support for multiple currencies to enable ASEAN and global expansion
-- Date: 2026-07-18
-- Purpose: Enable pricing in multiple currencies with real-time exchange rates

-- Enable currency support in existing tables
ALTER TABLE destinations ADD COLUMN base_currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE destinations ADD COLUMN price_usd DECIMAL(10, 2) NULL;
ALTER TABLE destinations ADD COLUMN price_sgd DECIMAL(10, 2) NULL;
ALTER TABLE destinations ADD COLUMN price_myr DECIMAL(10, 2) NULL;
ALTER TABLE destinations ADD COLUMN price_thb DECIMAL(10, 2) NULL;

ALTER TABLE hotels ADD COLUMN base_currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE hotels ADD COLUMN price_usd DECIMAL(10, 2) NULL;
ALTER TABLE hotels ADD COLUMN price_sgd DECIMAL(10, 2) NULL;
ALTER TABLE hotels ADD COLUMN price_myr DECIMAL(10, 2) NULL;
ALTER TABLE hotels ADD COLUMN price_thb DECIMAL(10, 2) NULL;

ALTER TABLE hotel_rooms ADD COLUMN base_currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE hotel_rooms ADD COLUMN price_usd DECIMAL(10, 2) NULL;
ALTER TABLE hotel_rooms ADD COLUMN price_sgd DECIMAL(10, 2) NULL;
ALTER TABLE hotel_rooms ADD COLUMN price_myr DECIMAL(10, 2) NULL;
ALTER TABLE hotel_rooms ADD COLUMN price_thb DECIMAL(10, 2) NULL;

ALTER TABLE restaurants ADD COLUMN base_currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE restaurants ADD COLUMN price_range_usd VARCHAR(20) NULL;
ALTER TABLE restaurants ADD COLUMN price_range_sgd VARCHAR(20) NULL;
ALTER TABLE restaurants ADD COLUMN price_range_myr VARCHAR(20) NULL;
ALTER TABLE restaurants ADD COLUMN price_range_thb VARCHAR(20) NULL;

ALTER TABLE tour_guides ADD COLUMN base_currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE tour_guides ADD COLUMN daily_rate_usd DECIMAL(10, 2) NULL;
ALTER TABLE tour_guides ADD COLUMN daily_rate_sgd DECIMAL(10, 2) NULL;
ALTER TABLE tour_guides ADD COLUMN daily_rate_myr DECIMAL(10, 2) NULL;
ALTER TABLE tour_guides ADD COLUMN daily_rate_thb DECIMAL(10, 2) NULL;

ALTER TABLE bookings ADD COLUMN currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE bookings ADD COLUMN original_amount DECIMAL(10, 2) NOT NULL;
ALTER TABLE bookings ADD COLUMN base_amount DECIMAL(10, 2) NULL; -- Amount in base currency (IDR)
ALTER TABLE bookings ADD COLUMN exchange_rate DECIMAL(20, 10) NULL; -- Exchange rate used
ALTER TABLE bookings ADD COLUMN exchange_rate_date DATETIME NULL;

ALTER TABLE payment_transactions ADD COLUMN currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE payment_transactions ADD COLUMN original_amount DECIMAL(10, 2) NOT NULL;
ALTER TABLE payment_transactions ADD COLUMN base_amount DECIMAL(10, 2) NULL;
ALTER TABLE payment_transactions ADD COLUMN exchange_rate DECIMAL(20, 10) NULL;
ALTER TABLE payment_transactions ADD COLUMN exchange_rate_date DATETIME NULL;

-- Create exchange rates table
CREATE TABLE exchange_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(20, 10) NOT NULL,
    source VARCHAR(50) DEFAULT 'manual', -- manual, open_exchange_rates, fixer, ecb
    effective_date DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rate (from_currency, to_currency, effective_date),
    INDEX idx_from_currency (from_currency),
    INDEX idx_to_currency (to_currency),
    INDEX idx_effective_date (effective_date),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create currency configurations table
CREATE TABLE currency_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    currency_code VARCHAR(3) NOT NULL UNIQUE,
    currency_name VARCHAR(50) NOT NULL,
    currency_symbol VARCHAR(10) NOT NULL,
    decimal_places INT DEFAULT 2,
    decimal_separator VARCHAR(1) DEFAULT ',',
    thousands_separator VARCHAR(1) DEFAULT '.',
    symbol_position ENUM('before', 'after') DEFAULT 'before',
    is_active BOOLEAN DEFAULT TRUE,
    is_base_currency BOOLEAN DEFAULT FALSE,
    supported_regions JSON, -- List of countries/regions where this currency is used
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create currency conversion log table
CREATE TABLE currency_conversion_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    from_amount DECIMAL(10, 2) NOT NULL,
    to_amount DECIMAL(10, 2) NOT NULL,
    exchange_rate DECIMAL(10, 6) NOT NULL,
    conversion_context VARCHAR(50) NOT NULL, -- booking, payment, refund, display
    context_id INT NULL, -- ID of the related record
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_from_currency (from_currency),
    INDEX idx_to_currency (to_currency),
    INDEX idx_context (conversion_context, context_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default currency configurations
INSERT INTO currency_config (currency_code, currency_name, currency_symbol, decimal_places, decimal_separator, thousands_separator, symbol_position, is_active, is_base_currency, supported_regions) VALUES
('IDR', 'Indonesian Rupiah', 'Rp', 0, ',', '.', 'before', TRUE, TRUE, '["ID"]'),
('USD', 'United States Dollar', '$', 2, '.', ',', 'before', TRUE, FALSE, '["US"]'),
('SGD', 'Singapore Dollar', 'S$', 2, '.', ',', 'before', TRUE, FALSE, '["SG"]'),
('MYR', 'Malaysian Ringgit', 'RM', 2, '.', ',', 'before', TRUE, FALSE, '["MY"]'),
('THB', 'Thai Baht', '฿', 2, '.', ',', 'before', TRUE, FALSE, '["TH"]'),
('EUR', 'Euro', '€', 2, ',', '.', 'before', FALSE, FALSE, '["AT","BE","CY","DE","EE","ES","FI","FR","GR","IE","IT","LU","LV","MT","NL","PT","SK","SI"]'),
('GBP', 'British Pound', '£', 2, '.', ',', 'before', FALSE, FALSE, '["GB"]'),
('JPY', 'Japanese Yen', '¥', 0, ',', '.', 'before', FALSE, FALSE, '["JP"]'),
('AUD', 'Australian Dollar', 'A$', 2, '.', ',', 'before', FALSE, FALSE, '["AU"]'),
('CNY', 'Chinese Yuan', '¥', 2, '.', ',', 'before', FALSE, FALSE, '["CN"]'),
('VND', 'Vietnamese Dong', '₫', 0, ',', '.', 'before', FALSE, FALSE, '["VN"]'),
('PHP', 'Philippine Peso', '₱', 2, '.', ',', 'before', FALSE, FALSE, '["PH"]');

-- Insert initial exchange rates (sample rates - should be updated from API)
INSERT INTO exchange_rates (from_currency, to_currency, rate, source, effective_date, expires_at) VALUES
('USD', 'IDR', 16000.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'USD', 0.0000625, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('SGD', 'IDR', 11800.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'SGD', 0.0000847, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('MYR', 'IDR', 3400.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'MYR', 0.0002941, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('THB', 'IDR', 430.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'THB', 0.0023256, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('EUR', 'IDR', 17500.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'EUR', 0.0000571, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('GBP', 'IDR', 20300.00, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR)),
('IDR', 'GBP', 0.0000493, 'manual', NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR));

-- Create user currency preference table
CREATE TABLE user_currency_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    preferred_currency VARCHAR(3) NOT NULL,
    auto_detect BOOLEAN DEFAULT TRUE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_preferred_currency (preferred_currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create currency buffer settings (for margin protection)
CREATE TABLE currency_buffer_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    buffer_percentage DECIMAL(5, 2) DEFAULT 2.00, -- 2% buffer by default
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_buffer (from_currency, to_currency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default buffer settings
INSERT INTO currency_buffer_settings (from_currency, to_currency, buffer_percentage) VALUES
('USD', 'IDR', 2.00),
('SGD', 'IDR', 2.00),
('MYR', 'IDR', 2.00),
('THB', 'IDR', 2.00),
('EUR', 'IDR', 2.00),
('GBP', 'IDR', 2.00);

-- Add currency column to promo codes
ALTER TABLE promo_codes ADD COLUMN currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE promo_codes ADD COLUMN discount_amount_usd DECIMAL(10, 2) NULL;
ALTER TABLE promo_codes ADD COLUMN discount_amount_sgd DECIMAL(10, 2) NULL;
ALTER TABLE promo_codes ADD COLUMN discount_amount_myr DECIMAL(10, 2) NULL;
ALTER TABLE promo_codes ADD COLUMN discount_amount_thb DECIMAL(10, 2) NULL;

-- Add currency column to invoices
ALTER TABLE invoices ADD COLUMN currency VARCHAR(3) DEFAULT 'IDR';
ALTER TABLE invoices ADD COLUMN original_amount DECIMAL(10, 2) NOT NULL;
ALTER TABLE invoices ADD COLUMN base_amount DECIMAL(10, 2) NULL;
ALTER TABLE invoices ADD COLUMN exchange_rate DECIMAL(10, 6) NULL;
ALTER TABLE invoices ADD COLUMN exchange_rate_date DATETIME NULL;

-- Create currency rate update job log
CREATE TABLE currency_rate_update_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source VARCHAR(50) NOT NULL,
    currencies_updated JSON NOT NULL, -- List of currency pairs updated
    success BOOLEAN NOT NULL,
    error_message TEXT NULL,
    execution_time_ms INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source),
    INDEX idx_created_at (created_at),
    INDEX idx_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
