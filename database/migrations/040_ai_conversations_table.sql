-- Migration: AI Conversations Table
-- Description: Create table for storing AI conversations with Ollama
-- Version: 1.0
-- Date: 2026-07-18

CREATE TABLE IF NOT EXISTS `ai_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `role` enum('system','user','assistant') NOT NULL,
  `content` text NOT NULL,
  `model` varchar(100) DEFAULT 'llama2',
  `tokens_used` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_ai_conversations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add intent field for conversation categorization
ALTER TABLE `ai_conversations` ADD COLUMN `intent` varchar(100) DEFAULT NULL AFTER `content`;
ALTER TABLE `ai_conversations` ADD INDEX `idx_intent` (`intent`);

-- Add feedback field for rating AI responses
ALTER TABLE `ai_conversations` ADD COLUMN `feedback` enum('positive','negative','neutral') DEFAULT NULL AFTER `tokens_used`;
ALTER TABLE `ai_conversations` ADD COLUMN `feedback_comment` text DEFAULT NULL AFTER `feedback`;
