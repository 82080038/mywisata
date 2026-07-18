-- Migration 061: Split Payment Enhancement
-- This migration adds features for split payment functionality
-- Date: 2026-07-18

-- Create split payment groups table
CREATE TABLE IF NOT EXISTS split_payment_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    group_code VARCHAR(20) NOT NULL UNIQUE,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    amount_paid DECIMAL(10, 2) DEFAULT 0,
    amount_remaining DECIMAL(10, 2) NOT NULL,
    created_by_user_id INT NOT NULL,
    expires_at DATETIME NULL,
    status ENUM('active', 'completed', 'cancelled', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_id (booking_id),
    INDEX idx_group_code (group_code),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create split payment participants table
CREATE TABLE IF NOT EXISTS split_payment_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    split_payment_group_id INT NOT NULL,
    user_id INT NULL, -- NULL for non-registered users
    participant_name VARCHAR(255) NOT NULL,
    participant_email VARCHAR(255) NULL,
    participant_phone VARCHAR(20) NULL,
    share_amount DECIMAL(10, 2) NOT NULL,
    amount_paid DECIMAL(10, 2) DEFAULT 0,
    amount_remaining DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'partial', 'paid', 'overpaid') DEFAULT 'pending',
    invite_sent BOOLEAN DEFAULT FALSE,
    invite_sent_at DATETIME NULL,
    invite_method ENUM('email', 'whatsapp', 'sms', 'link') NULL,
    invite_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_split_payment_group_id (split_payment_group_id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_invite_token (invite_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create split payment transactions table
CREATE TABLE IF NOT EXISTS split_payment_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    split_payment_group_id INT NOT NULL,
    participant_id INT NOT NULL,
    payment_transaction_id INT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    payment_method ENUM('card', 'bank_transfer', 'ewallet', 'qr_code', 'cash') NOT NULL,
    payment_gateway VARCHAR(50) NULL,
    transaction_reference VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_split_payment_group_id (split_payment_group_id),
    INDEX idx_participant_id (participant_id),
    INDEX idx_payment_transaction_id (payment_transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment reminders table
CREATE TABLE IF NOT EXISTS payment_reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    split_payment_group_id INT NOT NULL,
    participant_id INT NOT NULL,
    reminder_type ENUM('first', 'second', 'final', 'overdue') NOT NULL,
    reminder_date DATETIME NOT NULL,
    reminder_method ENUM('email', 'whatsapp', 'sms', 'push_notification') NOT NULL,
    sent BOOLEAN DEFAULT FALSE,
    sent_at DATETIME NULL,
    opened BOOLEAN DEFAULT FALSE,
    opened_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_split_payment_group_id (split_payment_group_id),
    INDEX idx_participant_id (participant_id),
    INDEX idx_reminder_date (reminder_date),
    INDEX idx_sent (sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add split payment columns to bookings table
SET @dbname = DATABASE();
SET @tablename = 'bookings';
SET @columnname = 'split_payment_enabled';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' BOOLEAN DEFAULT FALSE')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = 'split_payment_group_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = @dbname AND table_name = @tablename AND column_name = @columnname) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
