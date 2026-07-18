-- MyWisata Application - Add Guide Verifications Table Migration
-- Create table for tour guide verification documents
-- Created: 2026-07-16

-- Create guide_verifications table
CREATE TABLE IF NOT EXISTS guide_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guide_id BIGINT UNSIGNED NOT NULL,
    identity_type VARCHAR(50) NOT NULL COMMENT 'KTP, Passport, SIM, etc.',
    identity_number VARCHAR(100) NOT NULL,
    identity_document VARCHAR(255) NULL COMMENT 'Path to uploaded document',
    certification_type VARCHAR(100) NULL COMMENT 'Tour guide license, etc.',
    certification_number VARCHAR(100) NULL,
    certification_document VARCHAR(255) NULL COMMENT 'Path to uploaded document',
    portfolio_document VARCHAR(255) NULL COMMENT 'Path to uploaded portfolio',
    experience_years INT DEFAULT 0,
    languages TEXT NULL COMMENT 'JSON array of languages',
    specializations TEXT NULL COMMENT 'JSON array of specializations',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT NULL,
    rejection_reason TEXT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_guide_id (guide_id),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add is_verified column to tour_guides if not exists
ALTER TABLE tour_guides 
ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0 AFTER rating_avg,
ADD COLUMN IF NOT EXISTS verified_at TIMESTAMP NULL AFTER is_verified;
