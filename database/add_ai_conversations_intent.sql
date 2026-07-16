-- MyWisata Application - Add Intent Column to AI Conversations
-- Add intent column for better AI conversation tracking
-- Created: 2026-07-16

-- Add intent column to ai_conversations table
ALTER TABLE ai_conversations 
ADD COLUMN IF NOT EXISTS intent VARCHAR(50) NULL COMMENT 'Detected user intent',
ADD INDEX IF NOT EXISTS idx_intent (intent);
