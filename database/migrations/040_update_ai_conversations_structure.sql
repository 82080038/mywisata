-- Migration: Update AI Conversations Table Structure
-- Description: Update existing ai_conversations table to match modern features requirements
-- Version: 1.0
-- Date: 2026-07-18

-- Drop existing table and recreate with new structure
DROP TABLE IF EXISTS `ai_conversations`;

CREATE TABLE IF NOT EXISTS `ai_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `role` enum('system','user','assistant') NOT NULL,
  `content` text NOT NULL,
  `model` varchar(100) DEFAULT 'llama2',
  `tokens_used` int(11) DEFAULT 0,
  `intent` varchar(100) DEFAULT NULL,
  `feedback` enum('positive','negative','neutral') DEFAULT NULL,
  `feedback_comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_intent` (`intent`),
  CONSTRAINT `fk_ai_conversations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
