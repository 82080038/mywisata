-- ====================================================================
-- MyWisata Database Normalization Migration
-- 
-- Goals:
-- 1. Add missing FK constraints for referential integrity
-- 2. Create new master tables for enum columns that need CRUD
-- 3. Add columns to master tables for better manageability
-- 4. Remove redundant data (hotels.facilities longtext, description_* columns)
-- 5. Consolidate duplicate review tables into polymorphic reviews
-- 6. Consolidate duplicate chat/message tables
-- 7. Add is_system flag to master tables (system records cannot be deleted)
-- ====================================================================

-- ====================================================================
-- PART 1: NEW MASTER TABLES (for enum normalization)
-- ====================================================================

-- 1a. hotel_types (replaces hotels.type enum)
CREATE TABLE IF NOT EXISTS hotel_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO hotel_types (name, slug, sort_order, is_system) VALUES
('Hotel', 'hotel', 1, 1),
('Resort', 'resort', 2, 1),
('Homestay', 'homestay', 3, 1),
('Villa', 'villa', 4, 1),
('Guesthouse', 'guesthouse', 5, 1),
('Hostel', 'hostel', 6, 1),
('Apartment', 'apartment', 7, 1),
('Bungalow', 'bungalow', 8, 1),
('Cottage', 'cottage', 9, 1),
('Glamping', 'glamping', 10, 1),
('Cabin', 'cabin', 11, 1),
('Lodging', 'lodging', 12, 1),
('Inn', 'inn', 13, 1),
('Camping', 'camping', 14, 1);

-- 1b. restaurant_types (replaces restaurants.type enum)
CREATE TABLE IF NOT EXISTS restaurant_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO restaurant_types (name, slug, sort_order, is_system) VALUES
('Restoran', 'restoran', 1, 1),
('Warung', 'warung', 2, 1),
('Kafe', 'kafe', 3, 1),
('UMKM', 'umkm', 4, 1),
('Street Food', 'street_food', 5, 1);

-- 1c. event_categories (replaces events.category enum)
CREATE TABLE IF NOT EXISTS event_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO event_categories (name, slug, sort_order, is_system) VALUES
('Festival', 'festival', 1, 1),
('Seni', 'seni', 2, 1),
('Kuliner', 'kuliner', 3, 1),
('Olahraga', 'olahraga', 4, 1),
('Budaya', 'budaya', 5, 1),
('Religi', 'religi', 6, 1),
('Lainnya', 'other', 7, 1);

-- 1d. ticket_types (replaces tickets.ticket_type enum)
CREATE TABLE IF NOT EXISTS ticket_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ticket_types (name, slug, sort_order, is_system) VALUES
('Regular', 'regular', 1, 1),
('Anak', 'child', 2, 1),
('Lansia', 'senior', 3, 1),
('Grup', 'group', 4, 1),
('Wisatawan Asing', 'foreigner', 5, 1);

-- 1e. booking_statuses (replaces bookings.status enum)
CREATE TABLE IF NOT EXISTS booking_statuses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    color VARCHAR(20) NULL,
    sort_order INT DEFAULT 0,
    is_system TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO booking_statuses (name, slug, label, color, sort_order) VALUES
('pending', 'pending', 'Menunggu', 'warning', 1),
('confirmed', 'confirmed', 'Dikonfirmasi', 'success', 2),
('completed', 'completed', 'Selesai', 'primary', 3),
('cancelled', 'cancelled', 'Dibatalkan', 'danger', 4),
('rejected', 'rejected', 'Ditolak', 'danger', 5);

-- 1f. payment_methods (replaces transactions.payment_method enum)
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO payment_methods (name, slug, label, sort_order) VALUES
('transfer', 'transfer', 'Transfer Bank', 1),
('cash', 'cash', 'Tunai', 2),
('e_wallet', 'e_wallet', 'E-Wallet', 3),
('qris', 'qris', 'QRIS', 4),
('other', 'other', 'Lainnya', 5);

-- 1g. event_organizer_types
CREATE TABLE IF NOT EXISTS event_organizer_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    label VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_system TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO event_organizer_types (name, slug, label, sort_order) VALUES
('business', 'business', 'Bisnis', 1),
('government', 'government', 'Pemerintah', 2),
('community', 'community', 'Komunitas', 3),
('individual', 'individual', 'Individu', 4);

-- 1h. user_dietary_preferences (normalizes users.food_allergies & dietary_preferences)
CREATE TABLE IF NOT EXISTS dietary_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('allergy','diet','religious') NOT NULL DEFAULT 'diet',
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO dietary_preferences (name, slug, category, sort_order, is_system) VALUES
('Halal', 'halal', 'religious', 1, 1),
('Vegetarian', 'vegetarian', 'diet', 2, 1),
('Vegan', 'vegan', 'diet', 3, 1),
('Kosher', 'kosher', 'religious', 4, 1),
('Gluten-Free', 'gluten_free', 'allergy', 5, 1),
('Lactose Intolerant', 'lactose', 'allergy', 6, 1),
('Peanut Allergy', 'peanut', 'allergy', 7, 1),
('Seafood Allergy', 'seafood', 'allergy', 8, 1),
('Nut Allergy', 'nut', 'allergy', 9, 1),
('Diabetic', 'diabetic', 'diet', 10, 1);

-- 1i. user_dietary_map (junction table)
CREATE TABLE IF NOT EXISTS user_dietary_map (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    dietary_id INT UNSIGNED NOT NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_dietary (user_id, dietary_id),
    INDEX idx_user (user_id),
    INDEX idx_dietary (dietary_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- PART 2: ADD COLUMNS TO EXISTING MASTER TABLES
-- ====================================================================

-- 2a. Add is_system to existing master tables (system records cannot be deleted)
ALTER TABLE destination_categories ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0 AFTER description;
ALTER TABLE destination_categories ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER is_system;
ALTER TABLE destination_categories ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0 AFTER is_active;
ALTER TABLE destination_categories ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0 AFTER description;
ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0 AFTER is_active;
ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE languages ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0;
ALTER TABLE languages ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;
ALTER TABLE languages ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0;
ALTER TABLE languages ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE specializations ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0;
ALTER TABLE specializations ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;
ALTER TABLE specializations ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0;
ALTER TABLE specializations ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Mark system-seeded records
UPDATE destination_categories SET is_system = 1;
UPDATE product_categories SET is_system = 1;
UPDATE languages SET is_system = 1 WHERE code IN ('id','en','ja','zh','ko','es','fr','nl','de','ar');
UPDATE specializations SET is_system = 1;
UPDATE facilities SET is_system = 1 WHERE id <= 79;

-- 2b. Add hotel_type_id to hotels (replaces enum type)
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS hotel_type_id INT UNSIGNED NULL AFTER type;
UPDATE hotels h SET hotel_type_id = (SELECT id FROM hotel_types WHERE slug = h.type);

-- 2c. Add restaurant_type_id to restaurants
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS restaurant_type_id INT UNSIGNED NULL AFTER type;
UPDATE restaurants r SET restaurant_type_id = (SELECT id FROM restaurant_types WHERE slug = r.type);

-- 2d. Add event_category_id to events
ALTER TABLE events ADD COLUMN IF NOT EXISTS event_category_id INT UNSIGNED NULL AFTER category;
UPDATE events e SET event_category_id = (SELECT id FROM event_categories WHERE slug = e.category);

-- 2e. Add ticket_type_id to tickets
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS ticket_type_id INT UNSIGNED NULL AFTER ticket_type;
UPDATE tickets t SET ticket_type_id = (SELECT id FROM ticket_types WHERE slug = t.ticket_type);

-- 2f. Add organizer_type_id to events
ALTER TABLE events ADD COLUMN IF NOT EXISTS organizer_type_id INT UNSIGNED NULL AFTER organizer_type;
UPDATE events e SET organizer_type_id = (SELECT id FROM event_organizer_types WHERE slug = e.organizer_type);

-- ====================================================================
-- PART 3: MIGRATE JSON DATA TO NORMALIZED TABLES
-- ====================================================================

-- 3a. Migrate users.food_allergies & dietary_preferences to user_dietary_map
-- (Run from PHP since MySQL can't easily parse JSON arrays in older versions)
-- This will be handled by a PHP migration script

-- 3b. Migrate hotels.facilities (longtext JSON) to entity_facilities
-- Already partially done — entity_facilities has data for hotels
-- The longtext column will be kept for backward compat but deprecated

-- ====================================================================
-- PART 4: ADD MISSING FOREIGN KEY CONSTRAINTS
-- ====================================================================

-- 4a. Master table FKs
ALTER TABLE hotels ADD CONSTRAINT fk_hotels_type FOREIGN KEY (hotel_type_id) REFERENCES hotel_types(id) ON DELETE SET NULL;
ALTER TABLE restaurants ADD CONSTRAINT fk_restaurants_type FOREIGN KEY (restaurant_type_id) REFERENCES restaurant_types(id) ON DELETE SET NULL;
ALTER TABLE events ADD CONSTRAINT fk_events_category FOREIGN KEY (event_category_id) REFERENCES event_categories(id) ON DELETE SET NULL;
ALTER TABLE events ADD CONSTRAINT fk_events_organizer_type FOREIGN KEY (organizer_type_id) REFERENCES event_organizer_types(id) ON DELETE SET NULL;
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id) ON DELETE SET NULL;

-- 4b. Review table FKs
ALTER TABLE destination_reviews ADD CONSTRAINT fk_dest_reviews_dest FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE;
ALTER TABLE destination_reviews ADD CONSTRAINT fk_dest_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE hotel_reviews ADD CONSTRAINT fk_hotel_reviews_hotel FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE;
ALTER TABLE hotel_reviews ADD CONSTRAINT fk_hotel_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE restaurant_reviews ADD CONSTRAINT fk_rest_reviews_rest FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE;
ALTER TABLE restaurant_reviews ADD CONSTRAINT fk_rest_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE event_reviews ADD CONSTRAINT fk_event_reviews_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE;
ALTER TABLE event_reviews ADD CONSTRAINT fk_event_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- 4c. Booking & Transaction FKs
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE bookings ADD CONSTRAINT fk_bookings_guide FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE CASCADE;

ALTER TABLE transactions ADD CONSTRAINT fk_transactions_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL;
ALTER TABLE transactions ADD CONSTRAINT fk_transactions_guide FOREIGN KEY (guide_id) REFERENCES tour_guides(id) ON DELETE SET NULL;

-- 4d. Other missing FKs
ALTER TABLE guide_languages ADD CONSTRAINT fk_gl_language FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE;
ALTER TABLE guide_specializations ADD CONSTRAINT fk_gs_spec FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE CASCADE;
ALTER TABLE entity_facilities ADD CONSTRAINT fk_ef_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE;
ALTER TABLE user_favorites ADD CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE products ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE user_dietary_map ADD CONSTRAINT fk_udm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE user_dietary_map ADD CONSTRAINT fk_udm_dietary FOREIGN KEY (dietary_id) REFERENCES dietary_preferences(id) ON DELETE CASCADE;

-- ====================================================================
-- PART 5: CLEANUP - REMOVE REDUNDANT COLUMNS (Optional, after verification)
-- ====================================================================

-- These columns are kept for now (backward compat) but should be removed
-- after all code is updated to use the new normalized tables:
-- 
-- ALTER TABLE hotels DROP COLUMN type;
-- ALTER TABLE hotels DROP COLUMN facilities;  -- replaced by entity_facilities
-- ALTER TABLE restaurants DROP COLUMN type;
-- ALTER TABLE events DROP COLUMN category;
-- ALTER TABLE events DROP COLUMN organizer_type;
-- ALTER TABLE tickets DROP COLUMN ticket_type;
-- ALTER TABLE destinations DROP COLUMN description_en;
-- ALTER TABLE destinations DROP COLUMN description_ja;
-- ALTER TABLE destinations DROP COLUMN description_zh;
-- ALTER TABLE users DROP COLUMN food_allergies;
-- ALTER TABLE users DROP COLUMN dietary_preferences;
-- ALTER TABLE users DROP COLUMN allergy_notes;

-- ====================================================================
-- PART 6: CONSOLIDATE DUPLICATE TABLES (Phase 2 - after code update)
-- ====================================================================

-- The following duplicate tables exist and should eventually be consolidated:
--
-- 6a. Review tables:
--   destination_reviews, hotel_reviews, restaurant_reviews, event_reviews, guide_reviews
--   vs polymorphic: reviews (reviewable_type, reviewable_id)
--   → Keep specific tables for now (they have indexes), 
--     but add a migration to sync data into polymorphic `reviews` table
--     Eventually use only `reviews` table
--
-- 6b. Chat/message tables:
--   conversations + conversation_messages
--   vs chat_sessions + chat_messages
--   → Consolidate into one: conversations + conversation_messages
--     chat_sessions/chat_messages appear unused (0 rows)
--
-- 6c. ai_conversations vs conversations:
--   ai_conversations has its own structure, keep separate for AI context

-- ====================================================================
-- PART 7: ADD USEFUL INDEXES
-- ====================================================================

ALTER TABLE destination_categories ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE product_categories ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE languages ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE specializations ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE facilities ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE hotel_types ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE restaurant_types ADD INDEX IF NOT EXISTS idx_sort (sort_order);
ALTER TABLE event_categories ADD INDEX IF NOT EXISTS idx_sort (sort_order);

-- ====================================================================
-- VERIFICATION QUERIES (run after migration)
-- ====================================================================
-- SELECT 'hotel_types' as t, COUNT(*) as c FROM hotel_types;
-- SELECT 'restaurant_types' as t, COUNT(*) as c FROM restaurant_types;
-- SELECT 'event_categories' as t, COUNT(*) as c FROM event_categories;
-- SELECT 'ticket_types' as t, COUNT(*) as c FROM ticket_types;
-- SELECT 'booking_statuses' as t, COUNT(*) as c FROM booking_statuses;
-- SELECT 'payment_methods' as t, COUNT(*) as c FROM payment_methods;
-- SELECT 'event_organizer_types' as t, COUNT(*) as c FROM event_organizer_types;
-- SELECT 'dietary_preferences' as t, COUNT(*) as c FROM dietary_preferences;
-- 
-- -- Verify FK migration
-- SELECT h.name, ht.name as type_name FROM hotels h JOIN hotel_types ht ON h.hotel_type_id = ht.id LIMIT 5;
-- SELECT r.name, rt.name as type_name FROM restaurants r JOIN restaurant_types rt ON r.restaurant_type_id = rt.id LIMIT 5;
-- SELECT e.title, ec.name as cat_name FROM events e JOIN event_categories ec ON e.event_category_id = ec.id LIMIT 5;
