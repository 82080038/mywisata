-- Migration 048: Multi-Language Support for ASEAN Languages
-- This migration adds support for ASEAN languages (Bahasa Melayu, Thai, Vietnamese, Filipino)
-- Date: 2026-07-18
-- Purpose: Enable regional expansion to ASEAN countries

-- Create languages table
CREATE TABLE languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL UNIQUE, -- id, en, ms, th, vi, fil
    language_name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_rtl BOOLEAN DEFAULT FALSE, -- Right-to-left support
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    locale VARCHAR(20) NOT NULL, -- en_US, id_ID, ms_MY, th_TH, vi_VN, fil_PH
    flag_emoji VARCHAR(10) NULL,
    supported_regions JSON NOT NULL, -- List of countries where this language is used
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language_code (language_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert ASEAN languages
INSERT INTO languages (language_code, language_name, native_name, is_active, is_rtl, direction, locale, flag_emoji, supported_regions) VALUES
('id', 'Indonesian', 'Bahasa Indonesia', TRUE, FALSE, 'ltr', 'id_ID', '🇮🇩', '["ID"]'),
('en', 'English', 'English', TRUE, FALSE, 'ltr', 'en_US', '🇬🇧', '["GB","US","AU","SG","MY","PH"]'),
('ms', 'Malay', 'Bahasa Melayu', TRUE, FALSE, 'ltr', 'ms_MY', '🇲🇾', '["MY"]'),
('th', 'Thai', 'ภาษาไทย', TRUE, FALSE, 'ltr', 'th_TH', '🇹🇭', '["TH"]'),
('vi', 'Vietnamese', 'Tiếng Việt', TRUE, FALSE, 'ltr', 'vi_VN', '🇻🇳', '["VN"]'),
('fil', 'Filipino', 'Filipino', TRUE, FALSE, 'ltr', 'fil_PH', '🇵🇭', '["PH"]'),
('zh', 'Chinese', '中文', FALSE, FALSE, 'ltr', 'zh_CN', '🇨🇳', '["CN","SG","MY"]'),
('ja', 'Japanese', '日本語', FALSE, FALSE, 'ltr', 'ja_JP', '🇯🇵', '["JP"]'),
('ko', 'Korean', '한국어', FALSE, FALSE, 'ltr', 'ko_KR', '🇰🇷', '["KR"]'),
('ar', 'Arabic', 'العربية', FALSE, TRUE, 'rtl', 'ar_SA', '🇸🇦', '["SA","AE","MY","ID"]');

-- Create translations table
CREATE TABLE translations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL,
    translation_key VARCHAR(255) NOT NULL,
    translation_value TEXT NOT NULL,
    context VARCHAR(50) NULL, -- UI context (button, label, message, etc.)
    module VARCHAR(50) NULL, -- Module name (booking, hotel, restaurant, etc.)
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_translation (language_code, translation_key),
    INDEX idx_language_code (language_code),
    INDEX idx_translation_key (translation_key),
    INDEX idx_module (module),
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user language preferences table
CREATE TABLE user_language_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    preferred_language VARCHAR(10) NOT NULL,
    auto_detect BOOLEAN DEFAULT TRUE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (preferred_language) REFERENCES languages(language_code) ON DELETE CASCADE,
    INDEX idx_preferred_language (preferred_language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add language columns to existing tables
ALTER TABLE destinations ADD COLUMN translatable_content JSON NULL;
ALTER TABLE destinations ADD COLUMN available_languages JSON NULL;

ALTER TABLE hotels ADD COLUMN translatable_content JSON NULL;
ALTER TABLE hotels ADD COLUMN available_languages JSON NULL;

ALTER TABLE restaurants ADD COLUMN translatable_content JSON NULL;
ALTER TABLE restaurants ADD COLUMN available_languages JSON NULL;

ALTER TABLE tour_guides ADD COLUMN languages_spoken JSON NULL;
ALTER TABLE tour_guides ADD COLUMN bio_translations JSON NULL;

-- Create translation queue table for auto-translation
CREATE TABLE translation_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source_language VARCHAR(10) NOT NULL,
    target_language VARCHAR(10) NOT NULL,
    content_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'ui_text') NOT NULL,
    content_id INT NULL,
    source_text TEXT NOT NULL,
    translation_key VARCHAR(255) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    translation_service VARCHAR(50) NULL, -- google_translate, deepl, openai
    translated_text TEXT NULL,
    error_message TEXT NULL,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_source_target (source_language, target_language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create translation memory table for reuse
CREATE TABLE translation_memory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source_language VARCHAR(10) NOT NULL,
    target_language VARCHAR(10) NOT NULL,
    source_text TEXT NOT NULL,
    translated_text TEXT NOT NULL,
    usage_count INT DEFAULT 1,
    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    quality_score DECIMAL(3, 2) DEFAULT 1.00, -- 0.00 to 1.00
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_translation (source_language, target_language, source_text(255)),
    INDEX idx_source_target (source_language, target_language),
    INDEX idx_quality_score (quality_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add language column to users table
ALTER TABLE users ADD COLUMN preferred_language VARCHAR(10) DEFAULT 'id';
ALTER TABLE users ADD COLUMN language_detected VARCHAR(10) NULL;

-- Create RTL support configuration
CREATE TABLE rtl_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL UNIQUE,
    css_direction VARCHAR(10) DEFAULT 'rtl',
    flip_layout BOOLEAN DEFAULT TRUE,
    mirror_icons BOOLEAN DEFAULT TRUE,
    custom_css TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert RTL config for Arabic
INSERT INTO rtl_config (language_code, css_direction, flip_layout, mirror_icons) VALUES
('ar', 'rtl', TRUE, TRUE);

-- Create language-specific date/time formats
CREATE TABLE language_datetime_formats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL UNIQUE,
    date_format VARCHAR(50) NOT NULL, -- d/m/Y, m/d/Y, Y-m-d
    time_format VARCHAR(50) NOT NULL, -- H:i, g:i A
    datetime_format VARCHAR(50) NOT NULL,
    timezone VARCHAR(50) NULL,
    first_day_of_week INT DEFAULT 0, -- 0 = Sunday, 1 = Monday
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert datetime formats for ASEAN languages
INSERT INTO language_datetime_formats (language_code, date_format, time_format, datetime_format, timezone, first_day_of_week) VALUES
('id', 'd/m/Y', 'H:i', 'd/m/Y H:i', 'Asia/Jakarta', 0),
('en', 'd/m/Y', 'H:i', 'd/m/Y H:i', 'UTC', 0),
('ms', 'd/m/Y', 'H:i', 'd/m/Y H:i', 'Asia/Kuala_Lumpur', 0),
('th', 'd/m/Y', 'H:i', 'd/m/Y H:i', 'Asia/Bangkok', 0),
('vi', 'd/m/Y', 'H:i', 'd/m/Y H:i', 'Asia/Ho_Chi_Minh', 0),
('fil', 'm/d/Y', 'h:i A', 'm/d/Y h:i A', 'Asia/Manila', 0);

-- Create language-specific number formats
CREATE TABLE language_number_formats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    language_code VARCHAR(10) NOT NULL UNIQUE,
    decimal_separator VARCHAR(1) NOT NULL,
    thousands_separator VARCHAR(1) NOT NULL,
    decimal_places INT DEFAULT 2,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert number formats for ASEAN languages
INSERT INTO language_number_formats (language_code, decimal_separator, thousands_separator, decimal_places) VALUES
('id', ',', '.', 0),
('en', '.', ',', 2),
('ms', '.', ',', 2),
('th', '.', ',', 2),
('vi', ',', '.', 0),
('fil', '.', ',', 2);

-- Create translation analytics table
CREATE TABLE translation_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    total_translations INT DEFAULT 0,
    verified_translations INT DEFAULT 0,
    auto_translated INT DEFAULT 0,
    human_translated INT DEFAULT 0,
    average_quality_score DECIMAL(3, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_analytics (date, language_code),
    INDEX idx_date (date),
    INDEX idx_language_code (language_code),
    FOREIGN KEY (language_code) REFERENCES languages(language_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert basic UI translations for ASEAN languages
INSERT INTO translations (language_code, translation_key, translation_value, context, module) VALUES
-- Indonesian
('id', 'nav.home', 'Beranda', 'navigation', 'core'),
('id', 'nav.destinations', 'Destinasi', 'navigation', 'core'),
('id', 'nav.hotels', 'Hotel', 'navigation', 'core'),
('id', 'nav.restaurants', 'Restoran', 'navigation', 'core'),
('id', 'nav.tour_guides', 'Pemandu Wisata', 'navigation', 'core'),
('id', 'nav.booking', 'Pemesanan', 'navigation', 'core'),
('id', 'btn.book_now', 'Pesan Sekarang', 'button', 'core'),
('id', 'btn.search', 'Cari', 'button', 'core'),
('id', 'lbl.price', 'Harga', 'label', 'core'),
('id', 'lbl.duration', 'Durasi', 'label', 'core'),
('id', 'lbl.rating', 'Peringkat', 'label', 'core'),
('id', 'msg.booking_success', 'Pemesanan berhasil!', 'message', 'booking'),
('id', 'msg.booking_failed', 'Pemesanan gagal. Silakan coba lagi.', 'message', 'booking'),

-- Malay
('ms', 'nav.home', 'Utama', 'navigation', 'core'),
('ms', 'nav.destinations', 'Destinasi', 'navigation', 'core'),
('ms', 'nav.hotels', 'Hotel', 'navigation', 'core'),
('ms', 'nav.restaurants', 'Restoran', 'navigation', 'core'),
('ms', 'nav.tour_guides', 'Pemandu Pelancong', 'navigation', 'core'),
('ms', 'nav.booking', 'Tempahan', 'navigation', 'core'),
('ms', 'btn.book_now', 'Tempah Sekarang', 'button', 'core'),
('ms', 'btn.search', 'Cari', 'button', 'core'),
('ms', 'lbl.price', 'Harga', 'label', 'core'),
('ms', 'lbl.duration', 'Tempoh', 'label', 'core'),
('ms', 'lbl.rating', 'Penilaian', 'label', 'core'),
('ms', 'msg.booking_success', 'Tempahan berjaya!', 'message', 'booking'),
('ms', 'msg.booking_failed', 'Tempahan gagal. Sila cuba lagi.', 'message', 'booking'),

-- Thai
('th', 'nav.home', 'หน้าแรก', 'navigation', 'core'),
('th', 'nav.destinations', 'สถานที่ท่องเที่ยว', 'navigation', 'core'),
('th', 'nav.hotels', 'โรงแรม', 'navigation', 'core'),
('th', 'nav.restaurants', 'ร้านอาหาร', 'navigation', 'core'),
('th', 'nav.tour_guides', 'ไกด์ทัวร์', 'navigation', 'core'),
('th', 'nav.booking', 'การจอง', 'navigation', 'core'),
('th', 'btn.book_now', 'จองเลย', 'button', 'core'),
('th', 'btn.search', 'ค้นหา', 'button', 'core'),
('th', 'lbl.price', 'ราคา', 'label', 'core'),
('th', 'lbl.duration', 'ระยะเวลา', 'label', 'core'),
('th', 'lbl.rating', 'คะแนน', 'label', 'core'),
('th', 'msg.booking_success', 'การจองสำเร็จ!', 'message', 'booking'),
('th', 'msg.booking_failed', 'การจองล้มเหลว กรุณาลองอีกครั้ง', 'message', 'booking'),

-- Vietnamese
('vi', 'nav.home', 'Trang chủ', 'navigation', 'core'),
('vi', 'nav.destinations', 'Điểm đến', 'navigation', 'core'),
('vi', 'nav.hotels', 'Khách sạn', 'navigation', 'core'),
('vi', 'nav.restaurants', 'Nhà hàng', 'navigation', 'core'),
('vi', 'nav.tour_guides', 'Hướng dẫn viên', 'navigation', 'core'),
('vi', 'nav.booking', 'Đặt chỗ', 'navigation', 'core'),
('vi', 'btn.book_now', 'Đặt ngay', 'button', 'core'),
('vi', 'btn.search', 'Tìm kiếm', 'button', 'core'),
('vi', 'lbl.price', 'Giá', 'label', 'core'),
('vi', 'lbl.duration', 'Thời lượng', 'label', 'core'),
('vi', 'lbl.rating', 'Đánh giá', 'label', 'core'),
('vi', 'msg.booking_success', 'Đặt chỗ thành công!', 'message', 'booking'),
('vi', 'msg.booking_failed', 'Đặt chỗ thất bại. Vui lòng thử lại.', 'message', 'booking'),

-- Filipino
('fil', 'nav.home', 'Home', 'navigation', 'core'),
('fil', 'nav.destinations', 'Destinasyon', 'navigation', 'core'),
('fil', 'nav.hotels', 'Hotel', 'navigation', 'core'),
('fil', 'nav.restaurants', 'Restawran', 'navigation', 'core'),
('fil', 'nav.tour_guides', 'Tour Guide', 'navigation', 'core'),
('fil', 'nav.booking', 'Pag-book', 'navigation', 'core'),
('fil', 'btn.book_now', 'Book Ngayon', 'button', 'core'),
('fil', 'btn.search', 'Maghanap', 'button', 'core'),
('fil', 'lbl.price', 'Presyo', 'label', 'core'),
('fil', 'lbl.duration', 'Durasyon', 'label', 'core'),
('fil', 'lbl.rating', 'Rating', 'label', 'core'),
('fil', 'msg.booking_success', 'Matagumpay na booking!', 'message', 'booking'),
('fil', 'msg.booking_failed', 'Nabigo ang booking. Mangyaring subukan muli.', 'message', 'booking');
