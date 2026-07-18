-- Migration 062: Location-Based Discovery Enhancement
-- This migration adds features for location-based discovery
-- Date: 2026-07-18

-- Create nearby attractions table (cached results)
CREATE TABLE IF NOT EXISTS nearby_attractions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_destination_id INT NOT NULL,
    nearby_destination_id INT NOT NULL,
    distance_km DECIMAL(10, 2) NOT NULL,
    travel_time_minutes INT NULL,
    travel_mode ENUM('walking', 'driving', 'public_transport', 'cycling') DEFAULT 'driving',
    similarity_score DECIMAL(3, 2) NULL, -- 0.00 to 1.00
    category_match BOOLEAN DEFAULT FALSE,
    last_calculated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference_destination (reference_destination_id),
    INDEX idx_nearby_destination (nearby_destination_id),
    INDEX idx_distance_km (distance_km)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create location-based recommendations table
CREATE TABLE IF NOT EXISTS location_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(100) NULL,
    user_lat DECIMAL(10, 8) NOT NULL,
    user_lng DECIMAL(11, 8) NOT NULL,
    search_radius_km DECIMAL(5, 2) NOT NULL,
    recommended_destination_id INT NOT NULL,
    recommendation_score DECIMAL(3, 2) NOT NULL,
    recommendation_reason JSON, -- ["close_distance", "high_rating", "popular", "user_preference"]
    distance_km DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_user_location (user_lat, user_lng),
    INDEX idx_recommended_destination (recommended_destination_id),
    INDEX idx_recommendation_score (recommendation_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create geofence zones table
CREATE TABLE IF NOT EXISTS geofence_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    zone_name VARCHAR(255) NOT NULL,
    zone_type ENUM('attraction_area', 'city_center', 'tourist_zone', 'special_event', 'restricted') NOT NULL,
    center_lat DECIMAL(10, 8) NOT NULL,
    center_lng DECIMAL(11, 8) NOT NULL,
    radius_km DECIMAL(5, 2) NOT NULL,
    description TEXT,
    zone_rules JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_zone_type (zone_type),
    INDEX idx_is_active (is_active),
    INDEX idx_center_location (center_lat, center_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create location search history table
CREATE TABLE IF NOT EXISTS location_search_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(100) NULL,
    search_lat DECIMAL(10, 8) NOT NULL,
    search_lng DECIMAL(11, 8) NOT NULL,
    search_radius_km DECIMAL(5, 2) NOT NULL,
    search_query VARCHAR(255) NULL,
    search_filters JSON,
    results_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_search_location (search_lat, search_lng),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create popular routes table (for route-based discovery)
CREATE TABLE IF NOT EXISTS popular_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    route_type ENUM('walking', 'driving', 'cycling', 'public_transport') NOT NULL,
    start_destination_id INT NOT NULL,
    end_destination_id INT NOT NULL,
    waypoints JSON, -- [{"destination_id": 1, "order": 1}]
    total_distance_km DECIMAL(10, 2) NOT NULL,
    total_duration_minutes INT NOT NULL,
    difficulty_level ENUM('easy', 'moderate', 'challenging') DEFAULT 'moderate',
    popularity_score INT DEFAULT 0,
    number_of_stops INT DEFAULT 0,
    estimated_cost DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_route_type (route_type),
    INDEX idx_start_destination (start_destination_id),
    INDEX idx_end_destination (end_destination_id),
    INDEX idx_popularity_score (popularity_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add location-based columns to destinations table
SET @dbname = DATABASE();
SET @tablename = 'destinations';
SET @columnname = 'nearby_attractions_cached';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'nearby_attractions_last_updated';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TIMESTAMP NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insert sample geofence zones
INSERT IGNORE INTO geofence_zones (zone_name, zone_type, center_lat, center_lng, radius_km, description, zone_rules, is_active) VALUES
('Jakarta City Center', 'city_center', -6.2088, 106.8456, 5.0, 'Pusat kota Jakarta dengan banyak atraksi wisata', '["no_driving_restrictions", "pedestrian_priority"]', TRUE),
('Bali Kuta Tourist Zone', 'tourist_zone', -8.7185, 115.1686, 3.0, 'Kawasan wisata Kuta Bali', '["beach_access", "nightlife_allowed"]', TRUE),
('Yogyakarta Heritage Zone', 'attraction_area', -7.7956, 110.3695, 2.5, 'Kawasan warisan budaya Yogyakarta', '["quiet_hours", "cultural_preservation"]', TRUE);

-- Insert sample popular routes
INSERT IGNORE INTO popular_routes (route_name, slug, description, route_type, start_destination_id, end_destination_id, total_distance_km, total_duration_minutes, difficulty_level, popularity_score, number_of_stops, is_active, is_featured) VALUES
('Jakarta Heritage Walk', 'jakarta-heritage-walk', 'Jalan kaki warisan budaya Jakarta', 'walking', NULL, NULL, 5.0, 120, 'easy', 85, 5, TRUE, TRUE),
('Bali Beach Hopping', 'bali-beach-hopping', 'Mengunjungi pantai-pantai populer Bali', 'driving', NULL, NULL, 25.0, 180, 'moderate', 90, 4, TRUE, TRUE),
('Yogyakarta Temple Route', 'yogyakarta-temple-route', 'Rute candi-candi di Yogyakarta', 'driving', NULL, NULL, 40.0, 240, 'moderate', 95, 3, TRUE, TRUE);
