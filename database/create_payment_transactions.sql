-- MyWisata Application - Create Payment Transactions Table
-- Create table for payment gateway transactions
-- Created: 2026-07-16

-- Create payment_transactions table
CREATE TABLE IF NOT EXISTS payment_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(100) NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'midtrans, xendit',
    token VARCHAR(255) NULL,
    redirect_url VARCHAR(500) NULL,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, success, failed, unknown',
    payment_status VARCHAR(50) NULL COMMENT 'Original status from payment gateway',
    amount DECIMAL(15, 2) NULL,
    raw_response JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order_id (order_id),
    INDEX idx_provider (provider),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
