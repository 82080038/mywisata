-- Migration 052: Wisata Halal Enhancement (Simplified)
-- This migration adds features for Halal Tourism
-- Date: 2026-07-18

-- Create prayer rooms table
CREATE TABLE IF NOT EXISTS prayer_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NULL,
    hotel_id INT NULL,
    restaurant_id INT NULL,
    name VARCHAR(255) NOT NULL,
    location_lat DECIMAL(10, 8) NOT NULL,
    location_lng DECIMAL(11, 8) NOT NULL,
    capacity INT DEFAULT 50,
    facilities JSON,
    prayer_times JSON,
    is_accessible_24h BOOLEAN DEFAULT FALSE,
    is_women_only BOOLEAN DEFAULT FALSE,
    is_men_only BOOLEAN DEFAULT FALSE,
    contact_info JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_destination_id (destination_id),
    INDEX idx_hotel_id (hotel_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_location (location_lat, location_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create halal packages table
CREATE TABLE IF NOT EXISTS halal_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    inclusions TEXT,
    exclusions TEXT,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    max_group_size INT DEFAULT 20,
    min_group_size INT DEFAULT 2,
    halal_certified BOOLEAN DEFAULT TRUE,
    halal_certification_number VARCHAR(100) NULL,
    prayer_facilities_included BOOLEAN DEFAULT TRUE,
    halal_food_included BOOLEAN DEFAULT TRUE,
    alcohol_free BOOLEAN DEFAULT TRUE,
    no_non_halal_activities BOOLEAN DEFAULT TRUE,
    separate_accommodation BOOLEAN DEFAULT FALSE,
    female_guide_available BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create halal package itinerary table
CREATE TABLE IF NOT EXISTS halal_package_itinerary (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    day_number INT NOT NULL,
    activity_title VARCHAR(255) NOT NULL,
    activity_description TEXT,
    activity_type ENUM('sightseeing', 'prayer', 'meal', 'transport', 'accommodation', 'shopping', 'cultural') NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location_name VARCHAR(255) NULL,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    is_prayer_time BOOLEAN DEFAULT FALSE,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NULL,
    is_halal_verified BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_package_id (package_id),
    INDEX idx_day_number (day_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create halal package bookings table
CREATE TABLE IF NOT EXISTS halal_package_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    travel_date DATE NOT NULL,
    number_of_travelers INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    special_requests TEXT,
    dietary_requirements JSON,
    gender_preference ENUM('mixed', 'male_only', 'female_only', 'family_only') DEFAULT 'mixed',
    contact_person_name VARCHAR(255) NOT NULL,
    contact_person_phone VARCHAR(20) NOT NULL,
    contact_person_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_package_id (package_id),
    INDEX idx_user_id (user_id),
    INDEX idx_travel_date (travel_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample halal packages
INSERT IGNORE INTO halal_packages (name, slug, description, inclusions, exclusions, price_per_person, duration_days, duration_nights, max_group_size, min_group_size, halal_certified, prayer_facilities_included, halal_food_included, alcohol_free, no_non_halal_activities, is_active, is_featured) VALUES
('Wisata Halal Bali 5 Hari 4 Malam', 'wisata-halal-bali-5h4m', 'Paket wisata halal lengkap ke Bali dengan fasilitas ibadah dan makanan halal', 'Akomodasi halal, Transportasi AC, Makan 3x sehari halal, Tour guide Muslim, Tiket masuk objek wisata, Prayer time accommodation', 'Tiket pesawat, Pengeluaran pribadi, Tip untuk guide', 3500000, 5, 4, 20, 2, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE),
('Wisata Halal Yogyakarta 3 Hari 2 Malam', 'wisata-halal-yogyakarta-3h2m', 'Paket wisata halal ke Yogyakarta dengan kunjungan ke masjid bersejarah', 'Akomodasi halal, Transportasi, Makan halal, Tour guide Muslim, Tiket Borobudur & Prambanan, Kunjungan masjid', 'Tiket pesawat, Pengeluaran pribadi', 2500000, 3, 2, 15, 2, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE),
('Wisata Halal Lombok 4 Hari 3 Malam', 'wisata-halal-lombok-4h3m', 'Paket wisata halal ke Lombok dengan pantai dan masjid indah', 'Akomodasi halal, Transportasi, Makan halal, Tour guide Muslim, Tiket objek wisata, Prayer facilities', 'Tiket pesawat, Pengeluaran pribadi', 3000000, 4, 3, 15, 2, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE);

-- Insert sample prayer rooms
INSERT IGNORE INTO prayer_rooms (destination_id, name, location_lat, location_lng, capacity, facilities, prayer_times, is_accessible_24h) VALUES
(NULL, 'Masjid Agung Al-Azhar Jakarta', -6.2250, 106.8000, 500, '["wudu_area", "separate_entrance", "prayer_mats", "quran", "ablution_facilities", "parking"]', '{"fajr": "04:45", "dhuhr": "12:00", "asr": "15:15", "maghrib": "18:00", "isha": "19:15"}', TRUE),
(NULL, 'Masjid Istiqlal Jakarta', -6.1754, 106.8272, 200000, '["wudu_area", "separate_entrance", "prayer_mats", "quran", "ablution_facilities", "parking", "library"]', '{"fajr": "04:45", "dhuhr": "12:00", "asr": "15:15", "maghrib": "18:00", "isha": "19:15"}', TRUE),
(NULL, 'Masjid Raya Al-Azhar Surabaya', -7.2670, 112.7400, 1000, '["wudu_area", "separate_entrance", "prayer_mats", "quran", "ablution_facilities", "parking"]', '{"fajr": "04:30", "dhuhr": "11:45", "asr": "15:00", "maghrib": "17:45", "isha": "19:00"}', TRUE);
