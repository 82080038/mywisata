-- MyWisata Application - Create User Favorites Table
-- Create base user_favorites table
-- Created: 2026-07-16

-- Create user_favorites table
CREATE TABLE IF NOT EXISTS user_favorites (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_type VARCHAR(50) NOT NULL COMMENT 'destination, hotel, restaurant, event, tour_guide',
    item_id BIGINT UNSIGNED NOT NULL,
    folder VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_item_type (item_type),
    INDEX idx_item_id (item_id),
    INDEX idx_folder (folder),
    UNIQUE KEY unique_user_item (user_id, item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
