-- Migration 057: WhatsApp Booking Enhancement
-- This migration enhances WhatsApp integration for booking functionality
-- Date: 2026-07-18

-- Create WhatsApp booking sessions table
CREATE TABLE IF NOT EXISTS whatsapp_booking_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL,
    user_id INT NULL,
    session_state ENUM('initiated', 'destination_selection', 'date_selection', 'confirmation', 'payment', 'completed', 'cancelled') DEFAULT 'initiated',
    booking_type ENUM('destination', 'hotel', 'restaurant', 'tour_guide', 'package') NOT NULL,
    selected_destination_id INT NULL,
    selected_hotel_id INT NULL,
    selected_restaurant_id INT NULL,
    selected_tour_guide_id INT NULL,
    selected_package_id INT NULL,
    travel_date DATE NULL,
    number_of_people INT NULL,
    total_price DECIMAL(10, 2) NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    booking_id INT NULL,
    last_message_time TIMESTAMP NULL,
    last_message_content TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_phone_number (phone_number),
    INDEX idx_user_id (user_id),
    INDEX idx_session_state (session_state),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WhatsApp message templates table
CREATE TABLE IF NOT EXISTS whatsapp_message_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(255) NOT NULL,
    template_type ENUM('booking_confirmation', 'payment_reminder', 'booking_reminder', 'cancellation', 'promotion', 'welcome') NOT NULL,
    template_content TEXT NOT NULL,
    language_code VARCHAR(10) DEFAULT 'id',
    variables JSON, -- ["customer_name", "booking_code", "date", "time"]
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_template_type (template_type),
    INDEX idx_language_code (language_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WhatsApp booking analytics table
CREATE TABLE IF NOT EXISTS whatsapp_booking_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    total_sessions INT DEFAULT 0,
    completed_bookings INT DEFAULT 0,
    cancelled_sessions INT DEFAULT 0,
    conversion_rate DECIMAL(5, 2) DEFAULT 0,
    average_session_duration_minutes INT DEFAULT 0,
    most_common_booking_type VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_analytics (date),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WhatsApp quick replies table
CREATE TABLE IF NOT EXISTS whatsapp_quick_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reply_text VARCHAR(255) NOT NULL,
    reply_type ENUM('destination', 'date', 'people', 'confirm', 'cancel', 'help') NOT NULL,
    reply_value VARCHAR(255) NULL,
    language_code VARCHAR(10) DEFAULT 'id',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reply_type (reply_type),
    INDEX idx_language_code (language_code),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample WhatsApp message templates
INSERT IGNORE INTO whatsapp_message_templates (template_name, template_type, template_content, language_code, variables, is_active) VALUES
('Booking Confirmation ID', 'booking_confirmation', 'Halo {customer_name}, booking Anda telah dikonfirmasi! Kode booking: {booking_code}. Tanggal: {date}. Terima kasih telah menggunakan MyWisata.', 'id', '["customer_name", "booking_code", "date"]', TRUE),
('Booking Confirmation EN', 'booking_confirmation', 'Hello {customer_name}, your booking has been confirmed! Booking code: {booking_code}. Date: {date}. Thank you for using MyWisata.', 'en', '["customer_name", "booking_code", "date"]', TRUE),
('Payment Reminder ID', 'payment_reminder', 'Halo {customer_name}, jangan lupa selesaikan pembayaran untuk booking {booking_code} sebelum {deadline}. Total: {amount}.', 'id', '["customer_name", "booking_code", "deadline", "amount"]', TRUE),
('Welcome Message ID', 'welcome', 'Selamat datang di MyWisata! Ketik "booking" untuk mulai reservasi atau "help" untuk bantuan.', 'id', '[]', TRUE),
('Welcome Message EN', 'welcome', 'Welcome to MyWisata! Type "booking" to start reservation or "help" for assistance.', 'en', '[]', TRUE);

-- Insert sample WhatsApp quick replies
INSERT IGNORE INTO whatsapp_quick_replies (reply_text, reply_type, reply_value, language_code, display_order, is_active) VALUES
('Booking Destinasi', 'destination', 'destination', 'id', 1, TRUE),
('Booking Hotel', 'destination', 'hotel', 'id', 2, TRUE),
('Booking Restoran', 'destination', 'restaurant', 'id', 3, TRUE),
('Booking Tour Guide', 'destination', 'tour_guide', 'id', 4, TRUE),
('Konfirmasi', 'confirm', 'confirm', 'id', 10, TRUE),
('Batal', 'cancel', 'cancel', 'id', 11, TRUE),
('Bantuan', 'help', 'help', 'id', 20, TRUE);

-- Add WhatsApp-related columns to bookings table
SET @dbname = DATABASE();
SET @tablename = 'bookings';
SET @columnname = 'whatsapp_session_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(100) NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'booked_via_whatsapp';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
