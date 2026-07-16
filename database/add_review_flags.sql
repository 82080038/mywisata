-- MyWisata Application - Add Review Flags Table Migration
-- Create table for flagging reviews for moderation
-- Created: 2026-07-16

-- Create review_flags table
CREATE TABLE IF NOT EXISTS review_flags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_flag (review_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for review_id
CREATE INDEX idx_review_flags_review_id ON review_flags(review_id);

-- Add index for user_id
CREATE INDEX idx_review_flags_user_id ON review_flags(user_id);
