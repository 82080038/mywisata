-- Migration 060: Visual Itinerary Timeline Enhancement
-- This migration adds features for visual itinerary timeline
-- Date: 2026-07-18

-- Create itinerary timeline events table
CREATE TABLE IF NOT EXISTS itinerary_timeline_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    itinerary_id INT NOT NULL,
    day_number INT NOT NULL,
    event_order INT NOT NULL,
    event_type ENUM('accommodation', 'transport', 'activity', 'meal', 'rest', 'free_time', 'meeting', 'other') NOT NULL,
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_minutes INT NOT NULL,
    location_name VARCHAR(255) NULL,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    location_address TEXT NULL,
    booking_reference_id INT NULL,
    booking_reference_type ENUM('hotel', 'restaurant', 'destination', 'tour_guide', 'transport') NULL,
    cost DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    is_paid BOOLEAN DEFAULT FALSE,
    notes TEXT,
    is_mandatory BOOLEAN DEFAULT FALSE,
    weather_dependency BOOLEAN DEFAULT FALSE,
    alternative_option TEXT NULL,
    icon VARCHAR(50) NULL, -- Icon for visual representation
    color VARCHAR(20) NULL, -- Color for visual representation
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_day_number (day_number),
    INDEX idx_event_order (event_order),
    INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary day summaries table
CREATE TABLE IF NOT EXISTS itinerary_day_summaries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    itinerary_id INT NOT NULL,
    day_number INT NOT NULL,
    date DATE NOT NULL,
    start_location_name VARCHAR(255) NULL,
    start_location_lat DECIMAL(10, 8) NULL,
    start_location_lng DECIMAL(11, 8) NULL,
    end_location_name VARCHAR(255) NULL,
    end_location_lat DECIMAL(10, 8) NULL,
    end_location_lng DECIMAL(11, 8) NULL,
    total_distance_km DECIMAL(10, 2) NULL,
    total_cost DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    total_events INT DEFAULT 0,
    total_activities INT DEFAULT 0,
    total_meals INT DEFAULT 0,
    total_transport_time_minutes INT DEFAULT 0,
    weather_forecast JSON,
    special_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_day (itinerary_id, day_number),
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary templates table
CREATE TABLE IF NOT EXISTS itinerary_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    destination_id INT NULL,
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    difficulty_level ENUM('relaxed', 'moderate', 'intensive') DEFAULT 'moderate',
    target_audience ENUM('family', 'couple', 'solo', 'group', 'senior', 'adventure') NOT NULL,
    estimated_cost_per_person DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    includes JSON, -- ["accommodation", "transport", "meals", "activities", "guide"]
    excludes JSON,
    highlights TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    is_popular BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_destination_id (destination_id),
    INDEX idx_duration_days (duration_days),
    INDEX idx_target_audience (target_audience),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary template events table
CREATE TABLE IF NOT EXISTS itinerary_template_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    day_number INT NOT NULL,
    event_order INT NOT NULL,
    event_type ENUM('accommodation', 'transport', 'activity', 'meal', 'rest', 'free_time', 'meeting', 'other') NOT NULL,
    event_title VARCHAR(255) NOT NULL,
    event_description TEXT,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_minutes INT NOT NULL,
    is_optional BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_template_id (template_id),
    INDEX idx_day_number (day_number),
    INDEX idx_event_order (event_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary sharing table
CREATE TABLE IF NOT EXISTS itinerary_sharing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    itinerary_id INT NOT NULL,
    shared_by_user_id INT NOT NULL,
    shared_with_user_id INT NULL,
    share_type ENUM('public', 'private', 'link', 'email') NOT NULL,
    share_token VARCHAR(100) NULL,
    share_link VARCHAR(500) NULL,
    can_edit BOOLEAN DEFAULT FALSE,
    can_comment BOOLEAN DEFAULT TRUE,
    expires_at DATETIME NULL,
    view_count INT DEFAULT 0,
    last_viewed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_shared_by_user_id (shared_by_user_id),
    INDEX idx_shared_with_user_id (shared_with_user_id),
    INDEX idx_share_token (share_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create itinerary comments table
CREATE TABLE IF NOT EXISTS itinerary_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    itinerary_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    parent_comment_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_itinerary_id (itinerary_id),
    INDEX idx_user_id (user_id),
    INDEX idx_parent_comment_id (parent_comment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add timeline-related columns to itineraries table
SET @dbname = DATABASE();
SET @tablename = 'itineraries';
SET @columnname = 'timeline_view_mode';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM("timeline", "map", "list", "calendar") DEFAULT "timeline"')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'is_public';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'template_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insert sample itinerary templates
INSERT IGNORE INTO itinerary_templates (template_name, slug, description, duration_days, duration_nights, difficulty_level, target_audience, estimated_cost_per_person, includes, excludes, highlights, is_active, is_popular) VALUES
('Bali 5 Hari 4 Malam', 'bali-5-hari-4-malam', 'Paket wisata Bali lengkap dengan kunjungan ke tempat wisata populer', 5, 4, 'moderate', 'family', 3500000, '["accommodation", "transport", "meals", "activities", "guide"]', '["flight", "personal_expenses"]', 'Ubud, Kuta, Seminyak, Nusa Dua, Tanah Lot', TRUE, TRUE),
('Yogyakarta 3 Hari 2 Malam', 'yogyakarta-3-hari-2-malam', 'Paket wisata Yogyakarta dengan kunjungan ke Borobudur dan Prambanan', 3, 2, 'relaxed', 'couple', 2500000, '["accommodation", "transport", "meals", "activities"]', '["flight", "personal_expenses"]', 'Borobudur, Prambanan, Malioboro, Kraton', TRUE, TRUE),
('Bandung 2 Hari 1 Malam', 'bandung-2-hari-1-malam', 'Paket wisata Bandung dengan kunjungan ke factory outlet dan Kawah Putih', 2, 1, 'relaxed', 'group', 1500000, '["accommodation", "transport", "meals", "activities"]', '["flight", "personal_expenses"]', 'Factory Outlet, Kawah Putih, Tangkuban Perahu', TRUE, FALSE);
