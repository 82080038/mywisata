-- Migration: Social Features
-- Description: Create tables for group trip planning, shared wishlists, split payments, and trip albums
-- Version: 1.0
-- Date: 2026-07-18

-- Group trips
CREATE TABLE IF NOT EXISTS `group_trips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` enum('planning','confirmed','in_progress','completed','cancelled') DEFAULT 'planning',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_destination_id` (`destination_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_group_trips_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_group_trips_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Group trip participants
CREATE TABLE IF NOT EXISTS `group_trip_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_trip_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('organizer','participant','viewer') DEFAULT 'participant',
  `status` enum('invited','accepted','declined','left') DEFAULT 'invited',
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_group_user` (`group_trip_id`, `user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_group_trip_participants_trip` FOREIGN KEY (`group_trip_id`) REFERENCES `group_trips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_group_trip_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared wishlists
CREATE TABLE IF NOT EXISTS `shared_wishlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wishlist_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_shared_wishlists_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared wishlist items
CREATE TABLE IF NOT EXISTS `shared_wishlist_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wishlist_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_wishlist_destination` (`wishlist_id`, `destination_id`),
  KEY `idx_destination_id` (`destination_id`),
  KEY `idx_added_by` (`added_by`),
  CONSTRAINT `fk_shared_wishlist_items_wishlist` FOREIGN KEY (`wishlist_id`) REFERENCES `shared_wishlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_wishlist_items_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_wishlist_items_user` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared wishlist collaborators
CREATE TABLE IF NOT EXISTS `shared_wishlist_collaborators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission` enum('view','edit','admin') DEFAULT 'view',
  `invited_by` int(11) NOT NULL,
  `status` enum('invited','accepted','declined') DEFAULT 'invited',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_wishlist_user` (`wishlist_id`, `user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_shared_wishlist_collaborators_wishlist` FOREIGN KEY (`wishlist_id`) REFERENCES `shared_wishlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_wishlist_collaborators_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_wishlist_collaborators_inviter` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Split payment groups
CREATE TABLE IF NOT EXISTS `split_payment_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `status` enum('active','settled','cancelled') DEFAULT 'active',
  `settlement_deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_split_payment_groups_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_split_payment_groups_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Split payment members
CREATE TABLE IF NOT EXISTS `split_payment_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `share_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0,
  `status` enum('pending','partial','settled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_group_user` (`payment_group_id`, `user_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_split_payment_members_group` FOREIGN KEY (`payment_group_id`) REFERENCES `split_payment_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_split_payment_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip albums
CREATE TABLE IF NOT EXISTS `trip_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_trip_id` int(11) DEFAULT NULL,
  `album_name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `cover_photo` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_group_trip_id` (`group_trip_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_trip_albums_trip` FOREIGN KEY (`group_trip_id`) REFERENCES `group_trips` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_trip_albums_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip album photos
CREATE TABLE IF NOT EXISTS `trip_album_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `taken_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_album_id` (`album_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_trip_album_photos_album` FOREIGN KEY (`album_id`) REFERENCES `trip_albums` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_album_photos_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip album comments
CREATE TABLE IF NOT EXISTS `trip_album_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_photo_id` (`photo_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_trip_album_comments_photo` FOREIGN KEY (`photo_id`) REFERENCES `trip_album_photos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_trip_album_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
