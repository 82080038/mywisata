-- MyWisata Application - Add Favorite Enhancements Migration
-- Add folder and sharing features to favorites system
-- Created: 2026-07-16

-- Add folder and notes columns to user_favorites
ALTER TABLE user_favorites 
ADD COLUMN IF NOT EXISTS folder VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS notes TEXT NULL,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
ADD INDEX IF NOT EXISTS idx_folder (folder);

-- Create favorite_folders table
CREATE TABLE IF NOT EXISTS favorite_folders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    UNIQUE KEY unique_user_name (user_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create favorite_shares table for sharing wishlists
CREATE TABLE IF NOT EXISTS favorite_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    folder VARCHAR(100) NULL,
    share_token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_share_token (share_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
