-- Migration 059: Wisata Pertanian/Agritourism Enhancement
-- This migration adds features for Agritourism
-- Date: 2026-07-18

-- Create farms table
CREATE TABLE IF NOT EXISTS farms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    farm_type ENUM('organic', 'conventional', 'hydroponic', 'aquaculture', 'livestock', 'mixed') NOT NULL,
    main_products JSON, -- ["rice", "vegetables", "fruits", "herbs", "livestock"]
    farm_size_hectares DECIMAL(10, 2) NULL,
    established_year INT NULL,
    certification VARCHAR(100) NULL, -- Organic certification, GAP, etc
    certification_number VARCHAR(100) NULL,
    activities_offered JSON, -- ["fruit_picking", "harvesting", "cooking", "animal_feeding", "fishing"]
    facilities_available JSON, -- ["parking", "restaurant", "toilet", "prayer_room", "accommodation"]
    opening_hours JSON, -- {"monday": "08:00-17:00", "tuesday": "08:00-17:00"}
    best_season VARCHAR(50) NULL,
    educational_tours BOOLEAN DEFAULT FALSE,
    overnight_stay BOOLEAN DEFAULT FALSE,
    location_name VARCHAR(255) NOT NULL,
    location_lat DECIMAL(10, 8) NOT NULL,
    location_lng DECIMAL(11, 8) NOT NULL,
    contact_person VARCHAR(255) NULL,
    contact_phone VARCHAR(20) NULL,
    contact_email VARCHAR(255) NULL,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_farm_type (farm_type),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create farm activities table
CREATE TABLE IF NOT EXISTS farm_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_id INT NOT NULL,
    activity_name VARCHAR(255) NOT NULL,
    activity_type ENUM('fruit_picking', 'harvesting', 'planting', 'cooking', 'animal_feeding', 'fishing', 'cheese_making', 'beekeeping', 'rice_planting', 'other') NOT NULL,
    description TEXT,
    duration_hours INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    min_participants INT DEFAULT 2,
    max_participants INT DEFAULT 20,
    age_restriction INT NULL,
    seasonal_availability JSON, -- {"january": true, "february": true, ...}
    equipment_provided BOOLEAN DEFAULT TRUE,
    take_home_product BOOLEAN DEFAULT FALSE,
    take_home_product_description VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farm_id (farm_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create farm tour packages table
CREATE TABLE IF NOT EXISTS farm_tour_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_id INT NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    duration_hours INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    min_participants INT DEFAULT 5,
    max_participants INT DEFAULT 30,
    includes_meal BOOLEAN DEFAULT FALSE,
    meal_type ENUM('local', 'organic', 'farm_to_table') NULL,
    includes_transport BOOLEAN DEFAULT FALSE,
    includes_guide BOOLEAN DEFAULT TRUE,
    activities_included JSON, -- [{"activity_id": 1, "quantity": 1}]
    educational_content BOOLEAN DEFAULT FALSE,
    hands_on_experience BOOLEAN DEFAULT TRUE,
    take_home_products JSON, -- ["vegetables", "fruits", "processed_food"]
    suitable_for_groups BOOLEAN DEFAULT TRUE,
    suitable_for_families BOOLEAN DEFAULT TRUE,
    suitable_for_schools BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farm_id (farm_id),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create farm activity bookings table
CREATE TABLE IF NOT EXISTS farm_activity_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_id INT NOT NULL,
    activity_id INT NULL,
    package_id INT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    activity_date DATE NOT NULL,
    activity_time TIME NOT NULL,
    number_of_participants INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    group_type ENUM('family', 'school', 'corporate', 'tourist', 'other') DEFAULT 'tourist',
    age_range VARCHAR(50) NULL,
    special_requirements TEXT,
    dietary_restrictions JSON,
    contact_person_name VARCHAR(255) NOT NULL,
    contact_person_phone VARCHAR(20) NOT NULL,
    contact_person_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farm_id (farm_id),
    INDEX idx_activity_id (activity_id),
    INDEX idx_package_id (package_id),
    INDEX idx_user_id (user_id),
    INDEX idx_activity_date (activity_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create farm products table
CREATE TABLE IF NOT EXISTS farm_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    farm_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_type ENUM('vegetable', 'fruit', 'grain', 'herb', 'dairy', 'meat', 'fish', 'processed', 'other') NOT NULL,
    description TEXT,
    price_per_kg DECIMAL(10, 2) NULL,
    price_per_unit DECIMAL(10, 2) NULL,
    unit VARCHAR(50) NULL, -- kg, bunch, piece, liter
    currency VARCHAR(3) DEFAULT 'IDR',
    is_organic BOOLEAN DEFAULT FALSE,
    is_seasonal BOOLEAN DEFAULT FALSE,
    seasonal_availability JSON,
    available_for_sale BOOLEAN DEFAULT TRUE,
    available_for_pickup BOOLEAN DEFAULT TRUE,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_farm_id (farm_id),
    INDEX idx_product_type (product_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample farms
INSERT IGNORE INTO farms (name, slug, description, farm_type, main_products, farm_size_hectares, established_year, activities_offered, facilities_available, opening_hours, educational_tours, overnight_stay, location_name, location_lat, location_lng, is_active, is_featured) VALUES
('Kebun Buah Malang', 'kebun-buah-malang', 'Kebun buah organik dengan berbagai jenis buah tropis', 'organic', '["strawberry", "apple", "orange", "guava"]', 5.5, 2010, '["fruit_picking", "harvesting", "cooking"]', '["parking", "restaurant", "toilet", "prayer_room"]', '{"monday": "08:00-17:00", "tuesday": "08:00-17:00", "wednesday": "08:00-17:00", "thursday": "08:00-17:00", "friday": "08:00-17:00", "saturday": "07:00-18:00", "sunday": "07:00-18:00"}', TRUE, FALSE, 'Malang', -7.9797, 112.6304, TRUE, TRUE),
('Sawah Organic Cianjur', 'sawah-organic-cianjur', 'Sawah organik dengan sistem padi terintegrasi', 'organic', '["rice", "vegetables", "herbs"]', 8.0, 2015, '["rice_planting", "harvesting", "cooking"]', '["parking", "restaurant", "toilet", "prayer_room", "accommodation"]', '{"monday": "08:00-17:00", "tuesday": "08:00-17:00", "wednesday": "08:00-17:00", "thursday": "08:00-17:00", "friday": "08:00-17:00", "saturday": "07:00-18:00", "sunday": "07:00-18:00"}', TRUE, TRUE, 'Cianjur', -6.7333, 107.0333, TRUE, TRUE),
('Peternakan Sapi Perah Bogor', 'peternakan-sapi-perah-bogor', 'Peternakan sapi perah modern dengan sistem organik', 'livestock', '["milk", "cheese", "yogurt"]', 3.0, 2012, '["animal_feeding", "milking", "cheese_making"]', '["parking", "restaurant", "toilet", "prayer_room"]', '{"monday": "08:00-17:00", "tuesday": "08:00-17:00", "wednesday": "08:00-17:00", "thursday": "08:00-17:00", "friday": "08:00-17:00", "saturday": "07:00-18:00", "sunday": "07:00-18:00"}', TRUE, FALSE, 'Bogor', -6.5950, 106.7892, TRUE, FALSE);

-- Insert sample farm activities
INSERT IGNORE INTO farm_activities (farm_id, activity_name, activity_type, description, duration_hours, price_per_person, min_participants, max_participants, seasonal_availability, equipment_provided, take_home_product, take_home_product_description, is_active) VALUES
(1, 'Strawberry Picking', 'fruit_picking', 'Memetik strawberry segar langsung dari kebun', 2, 75000, 2, 20, '{"january": false, "february": false, "march": true, "april": true, "may": true, "june": true, "july": true, "august": true, "september": true, "october": true, "november": true, "december": true}', TRUE, TRUE, 'Strawberry segar 500g', TRUE),
(2, 'Rice Planting Experience', 'rice_planting', 'Menanam padi di sawah organik', 3, 100000, 5, 30, '{"january": true, "february": true, "march": true, "april": true, "may": false, "june": false, "july": false, "august": false, "september": true, "october": true, "november": true, "december": true}', TRUE, FALSE, NULL, TRUE),
(3, 'Milking Experience', 'animal_feeding', 'Memerah sapi dan belajar proses pembuatan susu', 2, 85000, 3, 15, '{"january": true, "february": true, "march": true, "april": true, "may": true, "june": true, "july": true, "august": true, "september": true, "october": true, "november": true, "december": true}', TRUE, TRUE, 'Susu segar 1 liter', TRUE);
