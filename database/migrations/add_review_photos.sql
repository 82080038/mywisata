-- MyWisata Application - Review Photos Database Migration
-- Add photo upload capability to reviews system
-- Run this after main migration.sql

-- Disable foreign key checks during migration
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- ALTER TABLE: reviews - Add photo support
-- ============================================
ALTER TABLE reviews 
ADD COLUMN photos JSON AFTER comment,
ADD COLUMN photo_count INT DEFAULT 0 AFTER photos;

-- ============================================
-- TABLE: review_photos
-- ============================================
CREATE TABLE IF NOT EXISTS review_photos (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id       BIGINT UNSIGNED NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    caption         VARCHAR(255),
    sort_order      INT DEFAULT 0,
    is_primary      TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    INDEX idx_review (review_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- Migration Complete
-- ============================================
