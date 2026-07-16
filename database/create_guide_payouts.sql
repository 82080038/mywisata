-- MyWisata Application - Create Guide Payouts Table
-- Create table for guide payout requests
-- Created: 2026-07-16

-- Create guide_payouts table
CREATE TABLE IF NOT EXISTS guide_payouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, processing, completed, rejected, failed',
    admin_note TEXT NULL,
    rejection_reason TEXT NULL,
    processed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_guide_id (guide_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
