-- Migration 049: ASEAN Destinations Data
-- This migration adds ASEAN country destinations to support regional expansion
-- Date: 2026-07-18
-- Purpose: Add ASEAN destinations for regional expansion

-- Create countries table
CREATE TABLE countries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_code VARCHAR(2) NOT NULL UNIQUE, -- ID, SG, MY, TH, VN, PH, etc.
    country_name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NOT NULL,
    capital VARCHAR(100) NULL,
    currency_code VARCHAR(3) NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    phone_code VARCHAR(10) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_asean BOOLEAN DEFAULT FALSE,
    visa_required BOOLEAN NULL, -- For Indonesian travelers
 visa_info TEXT NULL,
    timezone VARCHAR(50) NOT NULL,
    flag_emoji VARCHAR(10) NULL,
    coordinates JSON NULL, -- Center coordinates
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_country_code (country_code),
    INDEX idx_is_active (is_active),
    INDEX idx_is_asean (is_asean)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert ASEAN countries
INSERT INTO countries (country_code, country_name, native_name, capital, currency_code, language_code, phone_code, is_active, is_asean, visa_required, timezone, flag_emoji, coordinates) VALUES
('ID', 'Indonesia', 'Indonesia', 'Jakarta', 'IDR', 'id', '62', TRUE, TRUE, FALSE, 'Asia/Jakarta', '🇮🇩', '{"lat": -2.5489, "lng": 118.0149}'),
('SG', 'Singapore', 'Singapore', 'Singapore', 'SGD', 'en', '65', TRUE, TRUE, FALSE, 'Asia/Singapore', '🇸🇬', '{"lat": 1.3521, "lng": 103.8198}'),
('MY', 'Malaysia', 'Malaysia', 'Kuala Lumpur', 'MYR', 'ms', '60', TRUE, TRUE, FALSE, 'Asia/Kuala_Lumpur', '🇲🇾', '{"lat": 4.2105, "lng": 101.9758}'),
('TH', 'Thailand', 'ประเทศไทย', 'Bangkok', 'THB', 'th', '66', TRUE, TRUE, FALSE, 'Asia/Bangkok', '🇹🇭', '{"lat": 15.8700, "lng": 100.9925}'),
('VN', 'Vietnam', 'Việt Nam', 'Hanoi', 'VND', 'vi', '84', TRUE, TRUE, TRUE, 'Asia/Ho_Chi_Minh', '🇻🇳', '{"lat": 14.0583, "lng": 108.2772}'),
('PH', 'Philippines', 'Pilipinas', 'Manila', 'PHP', 'fil', '63', TRUE, TRUE, FALSE, 'Asia/Manila', '🇵🇭', '{"lat": 12.8797, "lng": 121.7740}'),
('BN', 'Brunei', 'Negara Brunei Darussalam', 'Bandar Seri Begawan', 'BND', 'ms', '673', FALSE, TRUE, FALSE, 'Asia/Brunei', '🇧🇳', '{"lat": 4.5353, "lng": 114.7277}'),
('KH', 'Cambodia', 'Kampuchea', 'Phnom Penh', 'KHR', 'km', '855', FALSE, TRUE, TRUE, 'Asia/Phnom_Penh', '🇰🇭', '{"lat": 12.5657, "lng": 104.9910}'),
('LA', 'Laos', 'ສປປ ລາວ', 'Vientiane', 'LAK', 'lo', '856', FALSE, TRUE, TRUE, 'Asia/Vientiane', '🇱🇦', '{"lat": 19.8563, "lng": 102.4955}'),
('MM', 'Myanmar', 'မြန်မာ', 'Naypyidaw', 'MMK', 'my', '95', FALSE, TRUE, TRUE, 'Asia/Yangon', '🇲🇲', '{"lat": 21.9162, "lng": 95.9560}');

-- Add country_id to destinations table
ALTER TABLE destinations ADD COLUMN country_id INT NULL;
ALTER TABLE destinations ADD COLUMN country_code VARCHAR(2) NULL;
ALTER TABLE destinations ADD COLUMN is_international BOOLEAN DEFAULT FALSE;

-- Add foreign key for country_id
ALTER TABLE destinations ADD FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL;

-- Create regions table (for grouping destinations within countries)
CREATE TABLE regions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_id INT NOT NULL,
    region_name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NULL,
    region_code VARCHAR(10) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_region (country_id, region_code),
    INDEX idx_country_id (country_id),
    INDEX idx_is_active (is_active),
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add region_id to destinations table
ALTER TABLE destinations ADD COLUMN region_id INT NULL;
ALTER TABLE destinations ADD FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE SET NULL;

-- Insert Indonesian regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(1, 'Java', 'Jawa', 'JW', 'Main island of Indonesia with major cities'),
(1, 'Bali', 'Bali', 'BA', 'Popular tourist destination'),
(1, 'Sumatra', 'Sumatera', 'SM', 'Western island with diverse attractions'),
(1, 'Kalimantan', 'Kalimantan', 'KL', 'Indonesian Borneo'),
(1, 'Sulawesi', 'Sulawesi', 'SL', 'Orchid-shaped island'),
(1, 'Papua', 'Papua', 'PP', 'Easternmost region'),
(1, 'Nusa Tenggara', 'Nusa Tenggara', 'NT', 'Eastern islands');

-- Insert Singapore regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(2, 'Central Region', 'Central Region', 'CR', 'Central Singapore'),
(2, 'East Region', 'East Region', 'ER', 'Eastern Singapore'),
(2, 'North Region', 'North Region', 'NR', 'Northern Singapore'),
(2, 'North-East Region', 'North-East Region', 'NER', 'North-Eastern Singapore'),
(2, 'West Region', 'West Region', 'WR', 'Western Singapore');

-- Insert Malaysian regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(3, 'Kuala Lumpur', 'Kuala Lumpur', 'KL', 'Capital city'),
(3, 'Penang', 'Pulau Pinang', 'PG', 'Northern state'),
(3, 'Johor', 'Johor', 'JH', 'Southern state'),
(3, 'Sabah', 'Sabah', 'SB', 'East Malaysia'),
(3, 'Sarawak', 'Sarawak', 'SW', 'East Malaysia');

-- Insert Thai regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(4, 'Bangkok', 'กรุงเทพมหานคร', 'BKK', 'Capital city'),
(4, 'Phuket', 'ภูเก็ต', 'PKT', 'Southern island'),
(4, 'Chiang Mai', 'เชียงใหม่', 'CNX', 'Northern city'),
(4, 'Pattaya', 'พัทยา', 'PTY', 'Coastal city'),
(4, 'Krabi', 'กระบี่', 'KBI', 'Southern province');

-- Insert Vietnamese regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(5, 'Hanoi', 'Hà Nội', 'HAN', 'Capital city'),
(5, 'Ho Chi Minh City', 'Thành phố Hồ Chí Minh', 'SGN', 'Southern city'),
(5, 'Da Nang', 'Đà Nẵng', 'DAD', 'Central city'),
(5, 'Ha Long Bay', 'Vịnh Hạ Long', 'HLB', 'UNESCO site'),
(5, 'Phu Quoc', 'Phú Quốc', 'PQC', 'Island destination');

-- Insert Filipino regions
INSERT INTO regions (country_id, region_name, native_name, region_code, description) VALUES
(6, 'Metro Manila', 'Metro Manila', 'MNL', 'Capital region'),
(6, 'Cebu', 'Cebu', 'CEB', 'Central Visayas'),
(6, 'Boracay', 'Boracay', 'BOR', 'Popular island'),
(6, 'Palawan', 'Palawan', 'PLW', 'Island province'),
(6, 'Davao', 'Davao', 'DVO', 'Mindanao region');

-- Create popular ASEAN destinations
CREATE TABLE asean_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_id INT NOT NULL,
    region_id INT NULL,
    destination_name VARCHAR(255) NOT NULL,
    native_name VARCHAR(255) NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL, -- beach, cultural, nature, urban, adventure
    popularity_score INT DEFAULT 0, -- 0-100
    is_recommended BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500) NULL,
    coordinates JSON NULL,
    best_time_to_visit VARCHAR(100) NULL,
    average_stay_duration INT NULL, -- in days
    budget_level ENUM('budget', 'mid_range', 'luxury') DEFAULT 'mid_range',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE,
    INDEX idx_country_id (country_id),
    INDEX idx_category (category),
    INDEX idx_popularity_score (popularity_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert popular ASEAN destinations
INSERT INTO asean_destinations (country_id, region_id, destination_name, native_name, description, category, popularity_score, is_recommended, budget_level, best_time_to_visit, average_stay_duration) VALUES
-- Singapore
(2, 1, 'Marina Bay Sands', 'Marina Bay Sands', 'Iconic integrated resort with infinity pool', 'urban', 95, TRUE, 'luxury', 'February-April', 3),
(2, 1, 'Gardens by the Bay', 'Gardens by the Bay', 'Futuristic nature park with supertrees', 'nature', 90, TRUE, 'mid_range', 'February-April', 1),
(2, 2, 'Sentosa Island', 'Sentosa Island', 'Theme parks and beaches', 'beach', 85, TRUE, 'mid_range', 'February-April', 2),

-- Malaysia
(3, 2, 'Petronas Twin Towers', 'Menara Berkembar Petronas', 'Iconic twin skyscrapers', 'urban', 90, TRUE, 'mid_range', 'December-February', 2),
(3, 2, 'George Town', 'George Town', 'UNESCO heritage site', 'cultural', 85, TRUE, 'budget', 'December-February', 2),
(3, 4, 'Mount Kinabalu', 'Gunung Kinabalu', 'Highest peak in Southeast Asia', 'adventure', 80, TRUE, 'mid_range', 'February-April', 3),

-- Thailand
(4, 2, 'Phuket Old Town', 'Phuket Old Town', 'Historic Sino-Portuguese architecture', 'cultural', 85, TRUE, 'mid_range', 'November-February', 3),
(4, 3, 'Doi Suthep', 'Doi Suthep', 'Sacred mountain temple', 'cultural', 80, TRUE, 'budget', 'November-February', 2),
(4, 4, 'Wat Arun', 'วัดอรุณ', 'Temple of Dawn', 'cultural', 90, TRUE, 'budget', 'November-February', 1),

-- Vietnam
(5, 2, 'Cu Chi Tunnels', 'Địa đạo Củ Chi', 'Historic tunnel network', 'cultural', 80, TRUE, 'budget', 'December-February', 1),
(5, 3, 'Hoi An Ancient Town', 'Phố cổ Hội An', 'UNESCO heritage town', 'cultural', 90, TRUE, 'mid_range', 'February-April', 2),
(5, 4, 'Ha Long Bay', 'Vịnh Hạ Long', 'UNESCO limestone karsts', 'nature', 95, TRUE, 'mid_range', 'October-December', 2),

-- Philippines
(6, 2, 'Chocolate Hills', 'Chocolate Hills', 'Unique geological formation', 'nature', 85, TRUE, 'budget', 'December-May', 2),
(6, 3, 'White Beach', 'White Beach', 'Famous white sand beach', 'beach', 95, TRUE, 'mid_range', 'December-May', 3),
(6, 4, 'Puerto Princesa Underground River', 'Puerto Princesa Underground River', 'UNESCO subterranean river', 'nature', 85, TRUE, 'mid_range', 'December-May', 2);

-- Create cross-border travel routes table
CREATE TABLE cross_border_routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    route_name VARCHAR(255) NOT NULL,
    from_country_id INT NOT NULL,
    to_country_id INT NOT NULL,
    travel_mode ENUM('flight', 'bus', 'train', 'ferry', 'car') NOT NULL,
    duration_minutes INT NULL,
    average_price DECIMAL(10, 2) NULL,
    currency VARCHAR(3) NOT NULL,
    frequency VARCHAR(50) NULL, -- daily, weekly, etc
    is_popular BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (to_country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_from_country (from_country_id),
    INDEX idx_to_country (to_country_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert popular cross-border routes
INSERT INTO cross_border_routes (route_name, from_country_id, to_country_id, travel_mode, duration_minutes, average_price, currency, frequency, is_popular) VALUES
('Jakarta to Singapore', 1, 2, 'flight', 110, 1500000, 'IDR', 'daily', TRUE),
('Jakarta to Kuala Lumpur', 1, 3, 'flight', 130, 1200000, 'IDR', 'daily', TRUE),
('Singapore to Bangkok', 2, 4, 'flight', 145, 250, 'SGD', 'daily', TRUE),
('Kuala Lumpur to Singapore', 3, 2, 'bus', 360, 50, 'SGD', 'hourly', TRUE),
('Ho Chi Minh to Phnom Penh', 5, 8, 'bus', 360, 15, 'USD', 'daily', TRUE),
('Bangkok to Phnom Penh', 4, 8, 'bus', 480, 20, 'USD', 'daily', TRUE);

-- Create visa requirements table
CREATE TABLE visa_requirements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_country_id INT NOT NULL,
    to_country_id INT NOT NULL,
    visa_type ENUM('visa_free', 'visa_on_arrival', 'e_visa', 'visa_required') NOT NULL,
    max_stay_days INT NULL,
    cost DECIMAL(10, 2) NULL,
    cost_currency VARCHAR(3) NULL,
    processing_time_days INT NULL,
    requirements TEXT NULL,
    official_url VARCHAR(500) NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_visa (from_country_id, to_country_id),
    INDEX idx_from_country (from_country_id),
    INDEX idx_to_country (to_country_id),
    FOREIGN KEY (from_country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (to_country_id) REFERENCES countries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert visa requirements for Indonesian travelers
INSERT INTO visa_requirements (from_country_id, to_country_id, visa_type, max_stay_days, cost, cost_currency, processing_time_days, requirements) VALUES
(1, 2, 'visa_free', 30, NULL, NULL, NULL, 'Passport valid for 6 months'),
(1, 3, 'visa_free', 30, NULL, NULL, NULL, 'Passport valid for 6 months'),
(1, 4, 'visa_on_arrival', 15, 2000, 'THB', 1, 'Passport valid 6 months, return ticket'),
(1, 5, 'visa_on_arrival', 30, 25, 'USD', 1, 'Passport valid 6 months, return ticket'),
(1, 6, 'visa_free', 30, NULL, NULL, NULL, 'Passport valid 6 months'),
(1, 8, 'visa_on_arrival', 30, 30, 'USD', 1, 'Passport valid 6 months, photo'),
(1, 9, 'visa_required', 30, 30, 'USD', 3, 'Passport valid 6 months, invitation letter'),
(1, 10, 'visa_required', 28, 50, 'USD', 5, 'Passport valid 6 months, e-visa application');
