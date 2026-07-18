-- Migration: Business Operations (Self-Hosted)
-- Description: Create tables for business operations automation
-- Version: 1.0
-- Date: 2026-07-18

-- AI match engine results
CREATE TABLE IF NOT EXISTS `guide_match_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `matched_guide_id` int(11) NOT NULL,
  `match_score` decimal(5,2) NOT NULL COMMENT '0-100 match score',
  `match_reasons` text DEFAULT NULL COMMENT 'JSON array of match reasons',
  `is_accepted` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_matched_guide_id` (`matched_guide_id`),
  KEY `idx_match_score` (`match_score`),
  CONSTRAINT `fk_guide_match_results_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_guide_match_results_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_guide_match_results_guide` FOREIGN KEY (`matched_guide_id`) REFERENCES `tour_guides` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Smart schedule entries
CREATE TABLE IF NOT EXISTS `smart_schedule_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guide_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `color` varchar(7) DEFAULT '#007bff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_start_datetime` (`start_datetime`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_smart_schedule_entries_guide` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_smart_schedule_entries_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payroll records
CREATE TABLE IF NOT EXISTS `payroll_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guide_id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_bookings` int(11) DEFAULT 0,
  `total_hours` decimal(8,2) DEFAULT 0,
  `base_salary` decimal(10,2) DEFAULT 0,
  `commission` decimal(10,2) DEFAULT 0,
  `bonus` decimal(10,2) DEFAULT 0,
  `deductions` decimal(10,2) DEFAULT 0,
  `net_salary` decimal(10,2) DEFAULT 0,
  `status` enum('draft','pending','approved','paid') DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_period` (`period_start`, `period_end`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_payroll_records_guide` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- GPS clock-in records
CREATE TABLE IF NOT EXISTS `gps_clock_in_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guide_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `clock_in_time` datetime NOT NULL,
  `clock_out_time` datetime DEFAULT NULL,
  `clock_in_latitude` decimal(10,8) DEFAULT NULL,
  `clock_in_longitude` decimal(11,8) DEFAULT NULL,
  `clock_out_latitude` decimal(10,8) DEFAULT NULL,
  `clock_out_longitude` decimal(11,8) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `status` enum('clocked_in','clocked_out','disputed') DEFAULT 'clocked_in',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_clock_in_time` (`clock_in_time`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_gps_clock_in_records_guide` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_gps_clock_in_records_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Express book (walk-in) records
CREATE TABLE IF NOT EXISTS `express_book_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guide_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `service_type` varchar(100) NOT NULL,
  `duration_hours` decimal(5,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','qr','transfer') NOT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `start_datetime` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_start_datetime` (`start_datetime`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `fk_express_book_records_guide` FOREIGN KEY (`guide_id`) REFERENCES `tour_guides` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
