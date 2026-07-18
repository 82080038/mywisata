-- MyWisata Application - Create Payment Records Table
-- Create table for payment flow records
-- Created: 2026-07-16

-- Create payment_records table
CREATE TABLE IF NOT EXISTS payment_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    order_id VARCHAR(100) NOT NULL,
    payment_type VARCHAR(20) NOT NULL COMMENT 'deposit, balance, refund',
    amount DECIMAL(15, 2) NOT NULL,
    total_amount DECIMAL(15, 2) NULL COMMENT 'Total booking amount (for deposit payments)',
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, completed, failed, cancelled',
    refund_reason TEXT NULL,
    completed_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_order_id (order_id),
    INDEX idx_payment_type (payment_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
