-- MyWisata Application - Add Itinerary Collaboration
-- Add collaboration features to itineraries
-- Created: 2026-07-16

-- Add collaboration columns to itineraries table
ALTER TABLE itineraries 
ADD COLUMN IF NOT EXISTS is_collaborative TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS owner_id BIGINT UNSIGNED NULL COMMENT 'Explicit owner for collaborative itineraries',
ADD INDEX IF NOT EXISTS idx_is_collaborative (is_collaborative);

-- Create itinerary_collaborators table
CREATE TABLE IF NOT EXISTS itinerary_collaborators (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    itinerary_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('owner', 'editor', 'viewer') DEFAULT 'viewer',
    can_edit TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    can_invite TINYINT(1) DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itinerary_id) REFERENCES itineraries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_itinerary_user (itinerary_id, user_id),
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary_activity_log table
CREATE TABLE IF NOT EXISTS itinerary_activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    itinerary_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'created, updated, item_added, item_removed, etc.',
    details JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (itinerary_id) REFERENCES itineraries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
