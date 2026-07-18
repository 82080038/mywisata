-- Migration 053: Wisata Kuliner Enhancement
-- This migration adds features for Culinary Tourism
-- Date: 2026-07-18

-- Create food tours table
CREATE TABLE IF NOT EXISTS food_tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    route_description TEXT,
    stops JSON, -- [{"location": "Warung Nasi", "dish": "Nasi Goreng", "duration": 30}]
    duration_hours INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    max_group_size INT DEFAULT 10,
    min_group_size INT DEFAULT 2,
    dietary_options JSON, -- ["vegetarian", "vegan", "halal", "gluten_free", "no_pork", "no_seafood"]
    includes_tasting BOOLEAN DEFAULT TRUE,
    includes_cooking_demo BOOLEAN DEFAULT FALSE,
    includes_market_visit BOOLEAN DEFAULT FALSE,
    difficulty_level ENUM('easy', 'moderate', 'challenging') DEFAULT 'easy',
    walking_distance_km DECIMAL(5, 2) DEFAULT 0,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cooking classes table
CREATE TABLE IF NOT EXISTS cooking_classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    cuisine_type VARCHAR(100) NOT NULL, -- Indonesian, Thai, Vietnamese, Malaysian, etc
    duration_hours INT NOT NULL,
    price_per_person DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    max_participants INT DEFAULT 8,
    min_participants INT DEFAULT 2,
    skill_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    instructor_id INT NULL,
    includes_ingredients BOOLEAN DEFAULT TRUE,
    includes_equipment BOOLEAN DEFAULT TRUE,
    includes_recipe_book BOOLEAN DEFAULT TRUE,
    includes_meal BOOLEAN DEFAULT TRUE,
    dietary_accommodations JSON, -- ["vegetarian", "vegan", "halal", "gluten_free"]
    location_name VARCHAR(255) NULL,
    location_lat DECIMAL(10, 8) NULL,
    location_lng DECIMAL(11, 8) NULL,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_cuisine_type (cuisine_type),
    INDEX idx_skill_level (skill_level),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cooking class menu items table
CREATE TABLE IF NOT EXISTS cooking_class_menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooking_class_id INT NOT NULL,
    dish_name VARCHAR(255) NOT NULL,
    dish_description TEXT,
    preparation_time_minutes INT,
    difficulty_level ENUM('easy', 'moderate', 'hard') DEFAULT 'moderate',
    ingredients TEXT,
    is_signature_dish BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cooking_class_id (cooking_class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create food tour bookings table
CREATE TABLE IF NOT EXISTS food_tour_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    food_tour_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    tour_date DATE NOT NULL,
    tour_time TIME NOT NULL,
    number_of_participants INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    dietary_restrictions JSON,
    special_requests TEXT,
    contact_person_name VARCHAR(255) NOT NULL,
    contact_person_phone VARCHAR(20) NOT NULL,
    contact_person_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_food_tour_id (food_tour_id),
    INDEX idx_user_id (user_id),
    INDEX idx_tour_date (tour_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cooking class bookings table
CREATE TABLE IF NOT EXISTS cooking_class_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooking_class_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    class_date DATE NOT NULL,
    class_time TIME NOT NULL,
    number_of_participants INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    dietary_restrictions JSON,
    skill_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    special_requests TEXT,
    contact_person_name VARCHAR(255) NOT NULL,
    contact_person_phone VARCHAR(20) NOT NULL,
    contact_person_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cooking_class_id (cooking_class_id),
    INDEX idx_user_id (user_id),
    INDEX idx_class_date (class_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add culinary-related columns to restaurants table
SET @dbname = DATABASE();
SET @tablename = 'restaurants';
SET @columnname = 'cooking_class_available';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'street_food';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'dietary_options';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' JSON')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Insert sample food tours
INSERT IGNORE INTO food_tours (name, slug, description, route_description, duration_hours, price_per_person, max_group_size, min_group_size, dietary_options, includes_tasting, includes_market_visit, walking_distance_km, is_active, is_featured) VALUES
('Jakarta Street Food Tour', 'jakarta-street-food-tour', 'Jelajahi kuliner jalanan Jakarta yang legendaris', 'Mencoba Nasi Goreng Kambing, Sate, Gado-gado, Es Teler', 4, 350000, 10, 2, '["halal", "no_pork"]', TRUE, TRUE, 3.5, TRUE, TRUE),
('Bali Traditional Food Tour', 'bali-traditional-food-tour', 'Wisata kuliner tradisional Bali dengan suasana lokal', 'Babi Guling, Bebek Betutu, Sate Lilit, Lawar', 5, 450000, 8, 2, '["no_pork_available"]', TRUE, TRUE, 2.0, TRUE, TRUE),
('Yogyakarta Culinary Heritage', 'yogyakarta-culinary-heritage', 'Wisata kuliner warisan budaya Yogyakarta', 'Gudeg, Krecek, Bakpia, Wedang Ronde', 3, 300000, 12, 2, '["halal", "vegetarian_available"]', TRUE, FALSE, 2.5, TRUE, FALSE);

-- Insert sample cooking classes
INSERT IGNORE INTO cooking_classes (name, slug, description, cuisine_type, duration_hours, price_per_person, max_participants, min_participants, skill_level, includes_ingredients, includes_equipment, includes_recipe_book, includes_meal, dietary_accommodations, is_active, is_featured) VALUES
('Masak Nasi Goreng Indonesia', 'masak-nasi-goreng-indonesia', 'Belajar masak Nasi Goreng dengan bumbu autentik', 'Indonesian', 2, 250000, 8, 2, 'beginner', TRUE, TRUE, TRUE, TRUE, '["vegetarian", "halal"]', TRUE, TRUE),
('Thai Cooking Masterclass', 'thai-cooking-masterclass', 'Belajar masak Pad Thai dan Tom Yum Goong', 'Thai', 3, 400000, 6, 2, 'intermediate', TRUE, TRUE, TRUE, TRUE, '["vegetarian", "no_pork"]', TRUE, TRUE),
('Vietnamese Spring Rolls', 'vietnamese-spring-rolls', 'Belajar membuat Spring Rolls segar', 'Vietnamese', 2, 300000, 8, 2, 'beginner', TRUE, TRUE, TRUE, TRUE, '["vegetarian", "vegan", "gluten_free"]', TRUE, FALSE);
