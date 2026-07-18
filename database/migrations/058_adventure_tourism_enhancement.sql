-- Migration 058: Wisata Petualangan Enhancement
-- This migration adds features for Adventure Tourism
-- Date: 2026-07-18

-- Create equipment rentals table
CREATE TABLE IF NOT EXISTS equipment_rentals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_name VARCHAR(255) NOT NULL,
    equipment_type ENUM('rafting', 'diving', 'hiking', 'climbing', 'camping', 'surfing', 'paragliding', 'cycling', 'skiing', 'other') NOT NULL,
    description TEXT,
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    daily_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    total_quantity INT NOT NULL,
    available_quantity INT NOT NULL,
    size_options JSON, -- ["S", "M", "L", "XL"]
    weight_kg DECIMAL(5, 2) NULL,
    safety_check_required BOOLEAN DEFAULT TRUE,
    last_safety_check_date DATE NULL,
    next_safety_check_date DATE NULL,
    condition_rating ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_is_active (is_active),
    INDEX idx_available_quantity (available_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create adventure activities table
CREATE TABLE IF NOT EXISTS adventure_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    activity_type ENUM('rafting', 'diving', 'hiking', 'climbing', 'camping', 'surfing', 'paragliding', 'cycling', 'skiing', 'zip_line', 'bungee_jumping', 'other') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL,
    duration_hours INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    min_age INT DEFAULT 12,
    max_age INT NULL,
    min_participants INT DEFAULT 2,
    max_participants INT DEFAULT 20,
    health_requirements TEXT,
    physical_requirements TEXT,
    skill_requirements TEXT,
    insurance_required BOOLEAN DEFAULT TRUE,
    insurance_included BOOLEAN DEFAULT FALSE,
    insurance_coverage DECIMAL(10, 2) NULL,
    guide_required BOOLEAN DEFAULT TRUE,
    guide_included BOOLEAN DEFAULT FALSE,
    equipment_included BOOLEAN DEFAULT FALSE,
    equipment_rental_available BOOLEAN DEFAULT TRUE,
    best_season VARCHAR(50) NULL,
    weather_dependency BOOLEAN DEFAULT TRUE,
    safety_rating ENUM('very_safe', 'safe', 'moderate', 'risky', 'extreme') DEFAULT 'safe',
    emergency_plan TEXT,
    location_name VARCHAR(255) NOT NULL,
    location_lat DECIMAL(10, 8) NOT NULL,
    location_lng DECIMAL(11, 8) NOT NULL,
    image_url VARCHAR(500) NULL,
    video_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_activity_type (activity_type),
    INDEX idx_difficulty_level (difficulty_level),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create adventure activity bookings table
CREATE TABLE IF NOT EXISTS adventure_activity_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    activity_date DATE NOT NULL,
    activity_time TIME NOT NULL,
    number_of_participants INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    equipment_rental BOOLEAN DEFAULT FALSE,
    equipment_rental_items JSON,
    guide_required BOOLEAN DEFAULT TRUE,
    dietary_requirements JSON,
    medical_conditions JSON,
    emergency_contact_name VARCHAR(255) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    emergency_contact_relationship VARCHAR(100) NOT NULL,
    special_requests TEXT,
    waiver_signed BOOLEAN DEFAULT FALSE,
    waiver_signed_at DATETIME NULL,
    health_declaration_signed BOOLEAN DEFAULT FALSE,
    health_declaration_signed_at DATETIME NULL,
    status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activity_id (activity_id),
    INDEX idx_user_id (user_id),
    INDEX idx_activity_date (activity_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create equipment rental bookings table
CREATE TABLE IF NOT EXISTS equipment_rental_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    rental_start_date DATE NOT NULL,
    rental_end_date DATE NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    size VARCHAR(10) NULL,
    pickup_location VARCHAR(255) NULL,
    return_location VARCHAR(255) NULL,
    damage_waiver_signed BOOLEAN DEFAULT FALSE,
    damage_waiver_signed_at DATETIME NULL,
    damage_deposit DECIMAL(10, 2) NULL,
    damage_deposit_returned BOOLEAN DEFAULT FALSE,
    equipment_condition_at_pickup TEXT NULL,
    equipment_condition_at_return TEXT NULL,
    damage_notes TEXT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'picked_up', 'returned', 'cancelled', 'overdue') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_user_id (user_id),
    INDEX idx_rental_start_date (rental_start_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create safety verification records table
CREATE TABLE IF NOT EXISTS safety_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    activity_id INT NULL,
    equipment_id INT NULL,
    verification_type ENUM('equipment', 'guide', 'location', 'activity') NOT NULL,
    verification_date DATE NOT NULL,
    verified_by INT NOT NULL,
    verification_result ENUM('passed', 'failed', 'conditional') NOT NULL,
    safety_score INT NULL, -- 0-100
    notes TEXT NULL,
    next_verification_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_verification_type (verification_type),
    INDEX idx_verification_date (verification_date),
    INDEX idx_verification_result (verification_result)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample adventure activities
INSERT IGNORE INTO adventure_activities (name, slug, description, activity_type, difficulty_level, duration_hours, price_per_person, min_age, max_participants, health_requirements, insurance_required, guide_required, location_name, location_lat, location_lng, is_active, is_featured) VALUES
('White Water Rafting Citarik', 'white-water-rafting-citarik', 'Arung jeram di Sungai Citarik dengan tingkat kesulitan menengah', 'rafting', 'intermediate', 4, 350000, 12, 20, 'Harus bisa berenang, tidak memiliki penyakit jantung', TRUE, TRUE, 'Sukabumi', -6.9333, 107.1333, TRUE, TRUE),
('Scuba Diving Bali', 'scuba-diving-bali', 'Menyelam di perairan Bali dengan keanekaragaman hayati laut', 'diving', 'beginner', 3, 500000, 12, 15, 'Tidak memiliki penyakit paru-paru, tidak takut kedalaman', TRUE, TRUE, 'Bali', -8.4095, 115.1889, TRUE, TRUE),
('Gunung Rinjani Trekking', 'gunung-rinjani-trekking', 'Pendakian Gunung Rinjani 3.726 mdpl', 'hiking', 'advanced', 48, 2500000, 18, 15, 'Fisik kuat, tidak memiliki penyakit ketinggian', TRUE, TRUE, 'Lombok', -8.4100, 116.4700, TRUE, TRUE),
('Paragliding Puncak', 'paragliding-puncak', 'Terbang layang di Puncak dengan pemandangan indah', 'paragliding', 'beginner', 1, 750000, 16, 2, 'Tidak takut ketinggian, berat badan 40-90kg', TRUE, TRUE, 'Puncak', -6.6500, 106.9500, TRUE, FALSE);

-- Insert sample equipment rentals
INSERT IGNORE INTO equipment_rentals (equipment_name, equipment_type, description, brand, model, daily_price, total_quantity, available_quantity, size_options, safety_check_required, condition_rating, is_active) VALUES
('Rafting Boat', 'rafting', 'Perahu arung jeram kapasitas 6 orang', 'Avon', 'RaftMaster', 500000, 10, 8, '["standard"]', TRUE, 'good', TRUE),
('Diving Gear Set', 'diving', 'Set lengkap peralatan menyelam (BCD, regulator, masker)', 'Scubapro', 'Pro', 150000, 20, 15, '["S", "M", "L", "XL"]', TRUE, 'excellent', TRUE),
('Hiking Backpack', 'hiking', 'Tas punggung kapasitas 60L', 'Deuter', 'Fox 60', 50000, 30, 25, '["S", "M", "L"]', TRUE, 'good', TRUE),
('Camping Tent', 'camping', 'Tenda kemah kapasitas 4 orang', 'Coleman', 'Sundome 4', 100000, 15, 12, '["standard"]', TRUE, 'good', TRUE);
