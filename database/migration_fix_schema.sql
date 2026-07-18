-- ============================================
-- MyWisata Schema Fix Migration
-- Fixes mismatches between DB schema and code
-- ============================================

-- 1. bookings: add missing columns
ALTER TABLE `bookings` 
  ADD COLUMN `rejection_reason` text DEFAULT NULL AFTER `notes`,
  ADD COLUMN `cancellation_reason` text DEFAULT NULL AFTER `rejection_reason`;

-- 2. transactions: add missing columns and fix enums
ALTER TABLE `transactions`
  ADD COLUMN `booking_id` bigint(20) unsigned DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `guide_id` bigint(20) unsigned DEFAULT NULL AFTER `booking_id`,
  ADD COLUMN `tax_amount` decimal(12,2) DEFAULT 0.00 AFTER `discount`,
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_guide_id` (`guide_id`);

-- Modify type enum to include 'cart'
ALTER TABLE `transactions`
  MODIFY COLUMN `type` enum('booking_guide','ticket','hotel','restaurant','event','cart','refund') NOT NULL;

-- Modify payment_method enum to include 'pending'
ALTER TABLE `transactions`
  MODIFY COLUMN `payment_method` enum('pending','transfer','cash','e_wallet','other') NOT NULL DEFAULT 'pending';

-- Modify payment_status enum to include 'cancelled'
ALTER TABLE `transactions`
  MODIFY COLUMN `payment_status` enum('pending','paid','failed','refunded','expired','cancelled') NOT NULL DEFAULT 'pending';

-- 3. tour_guides: add missing columns
ALTER TABLE `tour_guides`
  ADD COLUMN `name` varchar(100) DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `phone` varchar(20) DEFAULT NULL AFTER `name`,
  ADD COLUMN `bio` text DEFAULT NULL AFTER `phone`,
  ADD COLUMN `city` varchar(100) DEFAULT NULL AFTER `bio`,
  ADD COLUMN `avatar` varchar(255) DEFAULT NULL AFTER `city`;

-- 4. ticket_orders: add missing columns
ALTER TABLE `ticket_orders`
  ADD COLUMN `destination_id` bigint(20) unsigned DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `quantity` int(11) NOT NULL DEFAULT 1 AFTER `visit_date`,
  ADD COLUMN `unit_price` decimal(10,2) DEFAULT 0.00 AFTER `quantity`,
  ADD KEY `idx_destination` (`destination_id`);

-- 5. destination_images: add is_primary column
ALTER TABLE `destination_images`
  ADD COLUMN `is_primary` tinyint(1) NOT NULL DEFAULT 0 AFTER `caption`;

-- Set first image of each destination as primary
UPDATE `destination_images` di
SET `is_primary` = 1
WHERE `id` = (
  SELECT MIN(id) FROM (SELECT * FROM `destination_images`) di2 WHERE di2.destination_id = di.destination_id
);

-- 6. Create guide_reviews table (referenced by code but missing)
CREATE TABLE IF NOT EXISTS `guide_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guide_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guide_id` (`guide_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Create languages table (referenced by code but missing)
CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `native_name` varchar(50) DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common languages
INSERT INTO `languages` (`name`, `native_name`, `code`) VALUES
('Indonesia', 'Bahasa Indonesia', 'id'),
('English', 'English', 'en'),
('Japanese', '日本語', 'ja'),
('Mandarin', '普通话', 'zh'),
('Korean', '한국어', 'ko'),
('Arabic', 'العربية', 'ar'),
('Spanish', 'Español', 'es'),
('French', 'Français', 'fr'),
('Dutch', 'Nederlands', 'nl'),
('German', 'Deutsch', 'de');

-- Add language_id column to guide_languages (code uses language_id + JOIN)
ALTER TABLE `guide_languages`
  ADD COLUMN `language_id` int(11) DEFAULT NULL AFTER `guide_id`,
  ADD KEY `idx_language_id` (`language_id`);

-- Migrate existing varchar language values to languages table
UPDATE `guide_languages` gl
LEFT JOIN `languages` l ON gl.`language` = l.`name`
SET gl.`language_id` = l.`id`
WHERE l.`id` IS NOT NULL;

-- 8. Create specializations table (referenced by code but missing)
CREATE TABLE IF NOT EXISTS `specializations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common specializations
INSERT INTO `specializations` (`name`, `description`) VALUES
('Cultural Tour', 'Tours focusing on local culture and traditions'),
('Adventure Tour', 'Hiking, climbing, and outdoor adventures'),
('Historical Tour', 'Historical sites and heritage tours'),
('Nature Tour', 'Nature reserves, parks, and wildlife'),
('Photography Tour', 'Photography-focused tours'),
('Food & Culinary', 'Local cuisine and food tours'),
('Religious Tour', 'Religious and spiritual sites'),
('City Tour', 'Urban sightseeing tours'),
('Custom Tour', 'Customized tour experiences');

-- Add specialization_id column to guide_specializations
ALTER TABLE `guide_specializations`
  ADD COLUMN `specialization_id` int(11) DEFAULT NULL AFTER `guide_id`,
  ADD KEY `idx_specialization_id` (`specialization_id`);

-- Migrate existing varchar specialization values
UPDATE `guide_specializations` gs
LEFT JOIN `specializations` s ON gs.`specialization` = s.`name`
SET gs.`specialization_id` = s.`id`
WHERE s.`id` IS NOT NULL;
