-- MyWisata Application - Create Audio Guide Content Table
-- Create table for multi-language audio guide content
-- Created: 2026-07-16

-- Create audio_guide_content table
CREATE TABLE IF NOT EXISTS audio_guide_content (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id BIGINT UNSIGNED NULL,
    tour_guide_id BIGINT UNSIGNED NULL,
    language_code VARCHAR(10) NOT NULL DEFAULT 'id',
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    audio_url VARCHAR(500) NULL,
    duration INT NULL COMMENT 'Duration in seconds',
    transcript TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (tour_guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE,
    INDEX idx_destination_id (destination_id),
    INDEX idx_tour_guide_id (tour_guide_id),
    INDEX idx_language_code (language_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
