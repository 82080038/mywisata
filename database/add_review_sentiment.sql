-- MyWisata Application - Add Sentiment Analysis to Reviews
-- Add sentiment columns to reviews table
-- Created: 2026-07-16

-- Add sentiment columns to reviews table
ALTER TABLE reviews 
ADD COLUMN IF NOT EXISTS sentiment VARCHAR(20) NULL COMMENT 'positive, neutral, negative',
ADD COLUMN IF NOT EXISTS sentiment_score DECIMAL(3, 2) NULL COMMENT 'Sentiment score from -1 to 1',
ADD INDEX IF NOT EXISTS idx_sentiment (sentiment);
