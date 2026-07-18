-- Migration: Sustainability Carbon Tracking
-- Description: Create tables for carbon footprint tracking and eco-scoring
-- Version: 1.0
-- Date: 2026-07-18

-- Carbon emissions table
CREATE TABLE IF NOT EXISTS `carbon_emissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `emission_type` enum('transport','accommodation','food','activity') NOT NULL,
  `transport_mode` varchar(50) DEFAULT NULL COMMENT 'car, bus, train, flight, etc.',
  `distance_km` decimal(10,2) DEFAULT NULL,
  `co2_kg` decimal(10,2) NOT NULL COMMENT 'CO2 emissions in kg',
  `calculation_method` varchar(100) DEFAULT 'ghg_calculator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_emission_type` (`emission_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_carbon_emissions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carbon_emissions_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Eco scores table
CREATE TABLE IF NOT EXISTS `eco_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `score` int(11) NOT NULL COMMENT '0-100 eco score',
  `level` enum('bronze','silver','gold','platinum') NOT NULL,
  `total_co2_saved` decimal(10,2) DEFAULT 0 COMMENT 'Total CO2 saved in kg',
  `eco_actions_count` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  KEY `idx_destination_id` (`destination_id`),
  KEY `idx_score` (`score`),
  KEY `idx_level` (`level`),
  CONSTRAINT `fk_eco_scores_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_eco_scores_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Eco actions table
CREATE TABLE IF NOT EXISTS `eco_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_type` enum('public_transport','eco_accommodation','local_food','carbon_offset','waste_reduction') NOT NULL,
  `description` varchar(255) NOT NULL,
  `co2_saved_kg` decimal(10,2) DEFAULT 0,
  `points_earned` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_eco_actions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Low-carbon routes table
CREATE TABLE IF NOT EXISTS `low_carbon_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `origin_id` int(11) NOT NULL COMMENT 'Destination ID',
  `destination_id` int(11) NOT NULL COMMENT 'Destination ID',
  `transport_mode` varchar(50) NOT NULL,
  `duration_hours` decimal(5,2) NOT NULL,
  `co2_kg` decimal(10,2) NOT NULL,
  `cost_estimate` decimal(10,2) DEFAULT NULL,
  `eco_score` int(11) DEFAULT NULL,
  `is_recommended` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_origin_id` (`origin_id`),
  KEY `idx_destination_id` (`destination_id`),
  KEY `idx_transport_mode` (`transport_mode`),
  KEY `idx_is_recommended` (`is_recommended`),
  CONSTRAINT `fk_low_carbon_routes_origin` FOREIGN KEY (`origin_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_low_carbon_routes_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Destination eco ratings
ALTER TABLE `destinations` ADD COLUMN `eco_rating` int(11) DEFAULT NULL COMMENT '0-100 eco rating';
ALTER TABLE `destinations` ADD COLUMN `eco_features` text DEFAULT NULL COMMENT 'JSON array of eco features';
ALTER TABLE `destinations` ADD INDEX `idx_eco_rating` (`eco_rating`);
