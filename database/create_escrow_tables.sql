-- MyWisata Application - Create Escrow Tables
-- Create tables for escrow system
-- Created: 2026-07-16

-- Create escrow_accounts table
CREATE TABLE IF NOT EXISTS escrow_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    guide_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) DEFAULT 'held' COMMENT 'held, released, refunded',
    released_amount DECIMAL(15, 2) DEFAULT 0,
    released_at TIMESTAMP NULL,
    release_reason TEXT NULL,
    refunded_at TIMESTAMP NULL,
    refund_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_guide_id (guide_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create escrow_transactions table
CREATE TABLE IF NOT EXISTS escrow_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    escrow_id BIGINT UNSIGNED NOT NULL,
    transaction_type VARCHAR(50) NOT NULL COMMENT 'release, refund, partial_release',
    amount DECIMAL(15, 2) NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (escrow_id) REFERENCES escrow_accounts(id) ON DELETE CASCADE,
    INDEX idx_escrow_id (escrow_id),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create guide_balances table
CREATE TABLE IF NOT EXISTS guide_balances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id BIGINT UNSIGNED NOT NULL,
    balance DECIMAL(15, 2) DEFAULT 0,
    available_balance DECIMAL(15, 2) DEFAULT 0,
    pending_balance DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guide_id (guide_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
