-- Migration: WhatsApp Integration (Self-Hosted)
-- Description: Create tables for WhatsApp messaging and notifications
-- Version: 1.0
-- Date: 2026-07-18

-- WhatsApp contacts table
CREATE TABLE IF NOT EXISTS `whatsapp_contacts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `whatsapp_id` varchar(100) DEFAULT NULL COMMENT 'WhatsApp business ID',
  `is_verified` tinyint(1) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 1,
  `opt_in` tinyint(1) DEFAULT 1 COMMENT 'User opted in to receive messages',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_phone_number` (`phone_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_whatsapp_id` (`whatsapp_id`),
  CONSTRAINT `fk_whatsapp_contacts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp messages table
CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` bigint(20) unsigned NOT NULL,
  `message_type` enum('booking_confirmation','payment_reminder','review_request','promotion','customer_service','custom') NOT NULL,
  `direction` enum('outbound','inbound') NOT NULL,
  `content` text NOT NULL,
  `template_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','sent','delivered','read','failed') DEFAULT 'pending',
  `external_message_id` varchar(100) DEFAULT NULL COMMENT 'WhatsApp message ID',
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contact_id` (`contact_id`),
  KEY `idx_message_type` (`message_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_whatsapp_messages_contact` FOREIGN KEY (`contact_id`) REFERENCES `whatsapp_contacts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp message templates
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('booking_confirmation','payment_reminder','review_request','promotion','customer_service','custom') NOT NULL,
  `content` text NOT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON array of template variables',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_template_name` (`template_name`),
  KEY `idx_template_type` (`template_type`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- WhatsApp campaign table
CREATE TABLE IF NOT EXISTS `whatsapp_campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(255) NOT NULL,
  `template_id` bigint(20) unsigned NOT NULL,
  `target_audience` text DEFAULT NULL COMMENT 'JSON array of user IDs or criteria',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `total_recipients` int(11) DEFAULT 0,
  `sent_count` int(11) DEFAULT 0,
  `delivered_count` int(11) DEFAULT 0,
  `read_count` int(11) DEFAULT 0,
  `status` enum('draft','scheduled','sending','completed','failed') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_status` (`status`),
  KEY `idx_scheduled_at` (`scheduled_at`),
  CONSTRAINT `fk_whatsapp_campaigns_template` FOREIGN KEY (`template_id`) REFERENCES `whatsapp_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
