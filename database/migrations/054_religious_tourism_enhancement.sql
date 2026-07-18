-- Migration 054: Wisata Religi Enhancement
-- This migration adds features for Religious Tourism
-- Date: 2026-07-18

-- Create pilgrimage packages table
CREATE TABLE IF NOT EXISTS pilgrimage_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    destination_type ENUM('domestic', 'international') NOT NULL,
    main_destinations JSON, -- ["Mecca", "Medina", "Jerusalem", "Vatican"]
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    max_group_size INT DEFAULT 50,
    min_group_size INT DEFAULT 10,
    religious_guide_included BOOLEAN DEFAULT TRUE,
    prayer_schedule_included BOOLEAN DEFAULT TRUE,
    accommodation_type ENUM('economy', 'standard', 'premium', 'luxury') DEFAULT 'standard',
    accommodation_distance_to_holy_site_km DECIMAL(5, 2) NULL,
    includes_visa_assistance BOOLEAN DEFAULT FALSE,
    includes_flight BOOLEAN DEFAULT FALSE,
    includes_transport BOOLEAN DEFAULT TRUE,
    includes_meals BOOLEAN DEFAULT TRUE,
    meals_type ENUM('local', 'international', 'mixed') DEFAULT 'local',
    special_facilities JSON, -- ["wheelchair_access", "elderly_care", "medical_assistance"]
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_destination_type (destination_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create pilgrimage package itinerary table
CREATE TABLE IF NOT EXISTS pilgrimage_package_itinerary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    day_number INT NOT NULL,
    activity_title VARCHAR(255) NOT NULL,
    activity_description TEXT,
    activity_type ENUM('prayer', 'ziarah', 'transport', 'meal', 'rest', 'briefing', 'shopping') NOT NULL,
    holy_site_name VARCHAR(255) NULL,
    holy_site_type ENUM('mosque', 'church', 'temple', 'shrine', 'tomb', 'other') NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location_name VARCHAR(255) NULL,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    is_mandatory BOOLEAN DEFAULT FALSE,
    dress_code VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_package_id (package_id),
    INDEX idx_day_number (day_number),
    INDEX idx_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create pilgrimage bookings table
CREATE TABLE IF NOT EXISTS pilgrimage_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    departure_date DATE NOT NULL,
    return_date DATE NOT NULL,
    number_of_pilgrims INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    special_requests TEXT,
    medical_requirements JSON,
    dietary_requirements JSON,
    room_preference ENUM('shared', 'double', 'triple', 'quad') DEFAULT 'shared',
    gender_group ENUM('male_only', 'female_only', 'mixed', 'family') DEFAULT 'mixed',
    group_leader_name VARCHAR(255) NOT NULL,
    group_leader_phone VARCHAR(20) NOT NULL,
    group_leader_email VARCHAR(255) NOT NULL,
    emergency_contact_name VARCHAR(255) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    emergency_contact_relationship VARCHAR(100) NOT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'visa_processing', 'document_submitted', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    visa_status ENUM('not_required', 'pending', 'approved', 'rejected', 'submitted') NULL,
    passport_details JSON, -- [{"pilgrim_name": "Name", "passport_number": "A1234567", "expiry_date": "2027-12-31"}]
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_package_id (package_id),
    INDEX idx_user_id (user_id),
    INDEX idx_departure_date (departure_date),
    INDEX idx_status (status),
    INDEX idx_visa_status (visa_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create prayer times API cache table
CREATE TABLE IF NOT EXISTS prayer_times_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_lat DECIMAL(10, 8) NOT NULL,
    location_lng DECIMAL(11, 8) NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    country_code VARCHAR(2) NOT NULL,
    date DATE NOT NULL,
    fajr TIME NOT NULL,
    dhuhr TIME NOT NULL,
    asr TIME NOT NULL,
    maghrib TIME NOT NULL,
    isha TIME NOT NULL,
    calculation_method VARCHAR(50) DEFAULT 'MWL', -- Muslim World League
    source VARCHAR(50) DEFAULT 'aladhan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_prayer_time (location_lat, location_lng, date),
    INDEX idx_date (date),
    INDEX idx_city_country (city_name, country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create religious events table
CREATE TABLE IF NOT EXISTS religious_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_name VARCHAR(255) NOT NULL,
    event_type ENUM('ramadan', 'eid_al_fitr', 'eid_al_adha', 'christmas', 'easter', 'vesak', 'diwali', 'other') NOT NULL,
    description TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    location_lat DECIMAL(10, 8) NOT NULL,
    location_lng DECIMAL(11, 8) NOT NULL,
    expected_attendees INT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    registration_required BOOLEAN DEFAULT FALSE,
    registration_fee DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    facilities_available JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_start_date (start_date),
    INDEX idx_location (location_lat, location_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add religious-related columns to destinations table
SET @dbname = DATABASE();
SET @tablename = 'destinations';
SET @columnname = 'religious_significance';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(255) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'religious_site_type';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' ENUM("mosque", "church", "temple", "shrine", "tomb", "monastery", "other") NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insert sample pilgrimage packages
INSERT IGNORE INTO pilgrimage_packages (name, slug, description, destination_type, main_destinations, duration_days, duration_nights, price_per_person, max_group_size, min_group_size, religious_guide_included, prayer_schedule_included, accommodation_type, includes_visa_assistance, includes_flight, includes_transport, includes_meals, meals_type, is_active, is_featured) VALUES
('Umrah Reguler 9 Hari', 'umrah-reguler-9-hari', 'Paket Umrah reguler dengan fasilitas lengkap', 'international', '["Mecca", "Medina"]', 9, 8, 25000000, 50, 10, TRUE, TRUE, 'standard', TRUE, TRUE, TRUE, TRUE, 'local', TRUE, TRUE),
('Umrah Plus Turki 12 Hari', 'umrah-plus-turki-12-hari', 'Paket Umrah dengan kunjungan wisata Turki', 'international', '["Mecca", "Medina", "Istanbul", "Bursa"]', 12, 11, 35000000, 40, 15, TRUE, TRUE, 'premium', TRUE, TRUE, TRUE, TRUE, 'mixed', TRUE, TRUE),
('Ziarah Wali Songo 7 Hari', 'ziarah-wali-songo-7-hari', 'Paket ziarah ke makam Wali Songo di Jawa', 'domestic', '["Surabaya", "Gresik", "Lamongan", "Tuban", "Kudus", "Demak", "Semarang", "Solo", "Madiun"]', 7, 6, 3500000, 40, 20, TRUE, TRUE, 'economy', FALSE, FALSE, TRUE, TRUE, 'local', TRUE, FALSE);

-- Insert sample prayer times cache
INSERT IGNORE INTO prayer_times_cache (location_lat, location_lng, city_name, country_code, date, fajr, dhuhr, asr, maghrib, isha, calculation_method, source) VALUES
(-6.2088, 106.8456, 'Jakarta', 'ID', CURDATE(), '04:45', '12:00', '15:15', '18:00', '19:15', 'MWL', 'aladhan'),
(-6.9175, 107.6191, 'Bandung', 'ID', CURDATE(), '04:40', '11:55', '15:10', '17:55', '19:10', 'MWL', 'aladhan'),
(-7.7956, 110.3695, 'Yogyakarta', 'ID', CURDATE(), '04:35', '11:50', '15:05', '17:50', '19:05', 'MWL', 'aladhan');

-- Insert sample religious events
INSERT IGNORE INTO religious_events (event_name, event_type, description, start_date, end_date, location_name, location_lat, location_lng, expected_attendees, is_public, registration_required) VALUES
('Ramadan 1446H', 'ramadan', 'Bulan suci Ramadan 1446 Hijriah', '2026-03-01', '2026-03-30', 'Indonesia', -2.5489, 118.0149, 200000000, TRUE, FALSE),
('Eid al-Fitr 1446H', 'eid_al_fitr', 'Hari Raya Idul Fitri 1446 Hijriah', '2026-03-31', '2026-04-01', 'Indonesia', -2.5489, 118.0149, 200000000, TRUE, FALSE),
('Eid al-Adha 1446H', 'eid_al_adha', 'Hari Raya Idul Adha 1446 Hijriah', '2026-06-06', '2026-06-07', 'Indonesia', -2.5489, 118.0149, 200000000, TRUE, FALSE);
