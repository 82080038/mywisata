-- Migration: Document & Trip Management
-- Description: Create tables for digital wallet, itinerary import, trip timeline, and PDF itineraries
-- Version: 1.0
-- Date: 2026-07-18

-- Digital wallet
CREATE TABLE IF NOT EXISTS `digital_wallet` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(10,2) DEFAULT 0,
  `currency` varchar(3) DEFAULT 'IDR',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_digital_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wallet transactions
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wallet_id` bigint(20) unsigned NOT NULL,
  `transaction_type` enum('credit','debit','refund','bonus') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Booking ID or other reference',
  `reference_type` varchar(50) DEFAULT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_wallet_id` (`wallet_id`),
  KEY `idx_transaction_type` (`transaction_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_wallet_transactions_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `digital_wallet` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Imported itineraries
CREATE TABLE IF NOT EXISTS `imported_itineraries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `import_status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `parsed_data` text DEFAULT NULL COMMENT 'JSON array of parsed itinerary data',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_import_status` (`import_status`),
  CONSTRAINT `fk_imported_itineraries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip timeline entries
CREATE TABLE IF NOT EXISTS `trip_timeline_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `itinerary_id` bigint(20) unsigned NOT NULL,
  `day_number` int(11) NOT NULL,
  `time` time NOT NULL,
  `activity_type` enum('transport','sightseeing','meal','rest','activity','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `cost_estimate` decimal(10,2) DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_itinerary_id` (`itinerary_id`),
  KEY `idx_day_number` (`day_number`),
  KEY `idx_time` (`time`),
  CONSTRAINT `fk_trip_timeline_entries_itinerary` FOREIGN KEY (`itinerary_id`) REFERENCES `itineraries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PDF itineraries
CREATE TABLE IF NOT EXISTS `pdf_itineraries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `itinerary_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `download_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_itinerary_id` (`itinerary_id`),
  KEY `idx_generated_at` (`generated_at`),
  CONSTRAINT `fk_pdf_itineraries_itinerary` FOREIGN KEY (`itinerary_id`) REFERENCES `itineraries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Real-time updates (WebSocket subscriptions)
CREATE TABLE IF NOT EXISTS `websocket_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(100) NOT NULL,
  `subscription_type` enum('booking','itinerary','message','notification') NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_channel` (`channel`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_websocket_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
