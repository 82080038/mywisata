-- MyWisata Application - Add Notification Settings Table Migration
-- Create table for user notification preferences
-- Created: 2026-07-16

-- Create notification_settings table
CREATE TABLE IF NOT EXISTS notification_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    email_booking TINYINT(1) DEFAULT 1,
    email_message TINYINT(1) DEFAULT 1,
    email_review TINYINT(1) DEFAULT 0,
    email_promo TINYINT(1) DEFAULT 0,
    push_booking TINYINT(1) DEFAULT 1,
    push_message TINYINT(1) DEFAULT 1,
    push_review TINYINT(1) DEFAULT 1,
    push_promo TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
