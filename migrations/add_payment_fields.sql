-- Migration: Add Payment Fields to Transactions Table
-- Date: 2026-07-18
-- Module: 36 - Payment Gateway Integration

-- Add order_id to transactions table
ALTER TABLE transactions 
ADD COLUMN order_id VARCHAR(100) UNIQUE AFTER id;

-- Add payment_method to transactions table
ALTER TABLE transactions 
ADD COLUMN payment_method VARCHAR(50) AFTER status;

-- Add payment_date to transactions table
ALTER TABLE transactions 
ADD COLUMN payment_date DATETIME NULL AFTER payment_method;

-- Add transaction_id to transactions table (Midtrans transaction ID)
ALTER TABLE transactions 
ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL AFTER payment_date;

-- Add index on order_id for faster lookups
CREATE INDEX idx_order_id ON transactions(order_id);

-- Add index on payment_date for reporting
CREATE INDEX idx_payment_date ON transactions(payment_date);
