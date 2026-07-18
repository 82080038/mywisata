-- MyWisata Application - Phase 1 & 2 Feature Migrations
-- Adds: Video/UGC Gallery table, updates for events calendar
-- Run after existing migrations

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: destination_videos (Video/UGC Gallery)
-- ============================================
CREATE TABLE IF NOT EXISTS destination_videos (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    destination_id  BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    video_url       VARCHAR(500),
    video_file      VARCHAR(255),
    thumbnail       VARCHAR(255),
    duration        INT UNSIGNED DEFAULT 0,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    like_count      INT UNSIGNED NOT NULL DEFAULT 0,
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_destination (destination_id),
    INDEX idx_user (user_id),
    INDEX idx_approved (is_approved),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: hotel_videos (Video/UGC Gallery for hotels)
-- ============================================
CREATE TABLE IF NOT EXISTS hotel_videos (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hotel_id        BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    video_url       VARCHAR(500),
    video_file      VARCHAR(255),
    thumbnail       VARCHAR(255),
    duration        INT UNSIGNED DEFAULT 0,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    like_count      INT UNSIGNED NOT NULL DEFAULT 0,
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_hotel (hotel_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: restaurant_videos (Video/UGC Gallery for restaurants)
-- ============================================
CREATE TABLE IF NOT EXISTS restaurant_videos (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id   BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    video_url       VARCHAR(500),
    video_file      VARCHAR(255),
    thumbnail       VARCHAR(255),
    duration        INT UNSIGNED DEFAULT 0,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    like_count      INT UNSIGNED NOT NULL DEFAULT 0,
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: event_videos (Video/UGC Gallery for events)
-- ============================================
CREATE TABLE IF NOT EXISTS event_videos (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id        BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    video_url       VARCHAR(500),
    video_file      VARCHAR(255),
    thumbnail       VARCHAR(255),
    duration        INT UNSIGNED DEFAULT 0,
    view_count      INT UNSIGNED NOT NULL DEFAULT 0,
    like_count      INT UNSIGNED NOT NULL DEFAULT 0,
    is_approved     TINYINT(1) NOT NULL DEFAULT 0,
    is_featured     TINYINT(1) NOT NULL DEFAULT 0,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Update existing destinations: add eco_score display columns if not exist
-- (eco_score, eco_badge, is_village_tourism, village_name, community_leader, umkm_count already exist)
-- ============================================

-- Add city column to events for calendar filtering if not exists
-- (location_name already exists, used for city filtering)

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- SEED: Sample data for Phase 1 & 2 features
-- ============================================

-- Update existing destinations with eco scores
UPDATE destinations SET eco_score = 85, eco_badge = 'Gold' WHERE id = 1;
UPDATE destinations SET eco_score = 72, eco_badge = 'Silver' WHERE id = 2;
UPDATE destinations SET eco_score = 65, eco_badge = 'Bronze' WHERE id = 3;

-- Mark some destinations as village tourism
UPDATE destinations SET is_village_tourism = 1, village_name = 'Desa Panglipuran', community_leader = 'I Wayan Sudirta', umkm_count = 5 WHERE id = 1;
UPDATE destinations SET is_village_tourism = 1, village_name = 'Desa Pengotan', community_leader = 'Ni Ketut Suarni', umkm_count = 3 WHERE id = 2;
UPDATE destinations SET is_village_tourism = 1, village_name = 'Kampung Naga', community_leader = 'Asep Kurnia', umkm_count = 4 WHERE id = 3;

-- Insert sample videos for destination 1
INSERT INTO destination_videos (destination_id, user_id, title, description, video_url, thumbnail, is_approved, status) VALUES
(1, 1, 'Tour Desa Panglipuran', 'Video tur lengkap desa adat Panglipuran', 'https://www.youtube.com/embed/s6bBhdTgZ7U', 'https://img.youtube.com/vi/s6bBhdTgZ7U/0.jpg', 1, 'approved'),
(1, 2, 'Kerajinan Bambu Panglipuran', 'Proses pembuatan kerajinan bambu khas desa', 'https://www.youtube.com/embed/2Vv-BfVoq4g', 'https://img.youtube.com/vi/2Vv-BfVoq4g/0.jpg', 1, 'approved'),
(2, 1, 'Festival Galungan', 'Dokumentasi festival Galungan di Desa Pengotan', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://img.youtube.com/vi/dQw4w9WgXcQ/0.jpg', 1, 'approved');

-- Insert sample events for calendar (spread across current and next month)
INSERT INTO events (organizer_id, organizer_type, title, slug, description, category, start_date, end_date, location_name, address, latitude, longitude, price, requires_ticket, registration_type, max_attendees, max_participants, registered_count, is_active, event_status) VALUES
(1, 'community', 'Festival Budaya Nusantara', CONCAT('festival-budaya-nusantara-', UNIX_TIMESTAMP()), 'Festival tahunan menampilkan beragam budaya Nusantara', 'festival', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'Jakarta', 'Jakarta Convention Center', -6.200000, 106.816666, 50000, 1, 'ticket', 500, 500, 120, 1, 'upcoming'),
(1, 'community', 'Pesta Rakyat Desa Wisata', CONCAT('pesta-rakyat-desa-wisata-', UNIX_TIMESTAMP()+1), 'Perayaan panen raya di desa wisata', 'budaya', DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Bali', 'Desa Panglipuran, Bali', -8.431944, 115.378333, 0, 0, 'open', 200, 200, 45, 1, 'upcoming'),
(1, 'business', 'Konser Musik Tradisional', CONCAT('konser-musik-tradisional-', UNIX_TIMESTAMP()+2), 'Konser musik tradisional Indonesia', 'seni', DATE_ADD(CURDATE(), INTERVAL 21 DAY), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'Yogyakarta', 'Tugu Pal, Yogyakarta', -7.797068, 110.370529, 75000, 1, 'ticket', 300, 300, 89, 1, 'upcoming'),
(1, 'community', 'Festival Kuliner Nusantara', CONCAT('festival-kuliner-nusantara-', UNIX_TIMESTAMP()+3), 'Festival kuliner dari seluruh penjuru Indonesia', 'kuliner', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Bandung', 'Lapangan Gasibu, Bandung', -6.917464, 107.619125, 25000, 1, 'ticket', 1000, 1000, 340, 1, 'upcoming'),
(1, 'government', 'Pawai Budaya Kemerdekaan', CONCAT('pawai-budaya-kemerdekaan-', UNIX_TIMESTAMP()+4), 'Pawai budaya memperingati HUT RI', 'budaya', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Surabaya', 'Jalan Pahlawan, Surabaya', -7.257472, 112.752088, 0, 0, 'none', NULL, NULL, 0, 1, 'upcoming'),
(1, 'business', 'Marathon Desa Wisata', CONCAT('marathon-desa-wisata-', UNIX_TIMESTAMP()+5), 'Lari maraton menyusuri desa wisata', 'olahraga', DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Bali', 'Desa Pengotan, Bali', -8.450000, 115.350000, 100000, 1, 'ticket', 200, 200, 56, 1, 'upcoming');
