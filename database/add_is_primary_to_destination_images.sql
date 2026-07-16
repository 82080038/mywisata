-- Add is_primary column to destination_images table
ALTER TABLE destination_images ADD COLUMN is_primary TINYINT(1) NOT NULL DEFAULT 0 AFTER sort_order;
ADD INDEX idx_primary (is_primary);
