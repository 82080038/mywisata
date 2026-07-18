-- Migration 047: International Payment Gateway Integration
-- This migration adds support for multiple payment gateways (Stripe, PayPal, etc.)
-- Date: 2026-07-18
-- Purpose: Enable international payments for ASEAN and global expansion

-- Create payment gateways table
CREATE TABLE payment_gateways (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gateway_code VARCHAR(50) NOT NULL UNIQUE, -- stripe, paypal, midtrans, adyen, checkout
    gateway_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    supports_3ds BOOLEAN DEFAULT FALSE,
    supports_recurring BOOLEAN DEFAULT FALSE,
    supported_currencies JSON NOT NULL, -- List of supported currency codes
    supported_countries JSON NOT NULL, -- List of supported country codes
    api_config JSON NOT NULL, -- API keys, endpoints, etc (encrypted)
    webhook_config JSON NULL, -- Webhook URLs and secrets
    fee_structure JSON NULL, -- Transaction fees per currency
    priority INT DEFAULT 0, -- For routing logic
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gateway_code (gateway_code),
    INDEX idx_is_active (is_active),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment gateway routing rules table
CREATE TABLE payment_gateway_routing_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rule_name VARCHAR(100) NOT NULL,
    gateway_id INT NOT NULL,
    rule_type ENUM('currency', 'country', 'amount', 'risk', 'channel') NOT NULL,
    rule_condition JSON NOT NULL, -- Condition logic
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE CASCADE,
    INDEX idx_rule_type (rule_type),
    INDEX idx_priority (priority),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update payment_transactions table to support multiple gateways
ALTER TABLE payment_transactions ADD COLUMN gateway_id INT NULL;
ALTER TABLE payment_transactions ADD COLUMN gateway_payment_id VARCHAR(255) NULL; -- External payment ID from gateway
ALTER TABLE payment_transactions ADD COLUMN gateway_response JSON NULL; -- Full response from gateway
ALTER TABLE payment_transactions ADD COLUMN gateway_status VARCHAR(50) NULL; -- Status from gateway
ALTER TABLE payment_transactions ADD COLUMN 3ds_required BOOLEAN DEFAULT FALSE;
ALTER TABLE payment_transactions ADD COLUMN 3ds_completed BOOLEAN DEFAULT FALSE;
ALTER TABLE payment_transactions ADD COLUMN payment_method_type ENUM('card', 'wallet', 'bank_transfer', 'installment', 'other') DEFAULT 'card';
ALTER TABLE payment_transactions ADD COLUMN card_last4 VARCHAR(4) NULL;
ALTER TABLE payment_transactions ADD COLUMN card_brand VARCHAR(20) NULL; -- visa, mastercard, amex, etc
ALTER TABLE payment_transactions ADD COLUMN wallet_type VARCHAR(50) NULL; -- paypal, apple_pay, google_pay, etc
ALTER TABLE payment_transactions ADD COLUMN risk_score INT NULL; -- 0-100, higher = higher risk
ALTER TABLE payment_transactions ADD COLUMN routing_rule_id INT NULL;

-- Add foreign key for gateway_id
ALTER TABLE payment_transactions ADD FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE SET NULL;
ALTER TABLE payment_transactions ADD FOREIGN KEY (routing_rule_id) REFERENCES payment_gateway_routing_rules(id) ON DELETE SET NULL;

-- Create payment method tokens table (for recurring payments)
CREATE TABLE payment_method_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    gateway_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    payment_method_type ENUM('card', 'wallet', 'bank_transfer') NOT NULL,
    card_last4 VARCHAR(4) NULL,
    card_brand VARCHAR(20) NULL,
    card_exp_month INT NULL,
    card_exp_year INT NULL,
    wallet_type VARCHAR(50) NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_token (user_id, token),
    INDEX idx_user_id (user_id),
    INDEX idx_gateway_id (gateway_id),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment disputes table (for chargebacks)
CREATE TABLE payment_disputes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_transaction_id INT NOT NULL,
    gateway_id INT NOT NULL,
    gateway_dispute_id VARCHAR(255) NOT NULL,
    dispute_reason VARCHAR(255) NOT NULL,
    dispute_status ENUM('needs_response', 'under_review', 'won', 'lost', 'expired') NOT NULL,
    dispute_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    response_deadline DATETIME NULL,
    evidence_submitted BOOLEAN DEFAULT FALSE,
    evidence_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (payment_transaction_id) REFERENCES payment_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dispute (gateway_id, gateway_dispute_id),
    INDEX idx_dispute_status (dispute_status),
    INDEX idx_response_deadline (response_deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payment gateway webhook logs table
CREATE TABLE payment_gateway_webhook_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gateway_id INT NOT NULL,
    webhook_id VARCHAR(255) NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    processed BOOLEAN DEFAULT FALSE,
    processing_attempts INT DEFAULT 0,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME NULL,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE CASCADE,
    INDEX idx_gateway_id (gateway_id),
    INDEX idx_webhook_id (webhook_id),
    INDEX idx_event_type (event_type),
    INDEX idx_processed (processed),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create settlement reports table (for reconciliation)
CREATE TABLE payment_settlement_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    gateway_id INT NOT NULL,
    settlement_id VARCHAR(255) NOT NULL,
    settlement_date DATE NOT NULL,
    settlement_currency VARCHAR(3) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    transaction_count INT NOT NULL,
    fee_amount DECIMAL(10, 2) NOT NULL,
    net_amount DECIMAL(10, 2) NOT NULL,
    report_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE CASCADE,
    UNIQUE KEY unique_settlement (gateway_id, settlement_id),
    INDEX idx_settlement_date (settlement_date),
    INDEX idx_gateway_id (gateway_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default payment gateway configurations (encrypted in production)
INSERT INTO payment_gateways (gateway_code, gateway_name, is_active, supports_3ds, supports_recurring, supported_currencies, supported_countries, api_config, fee_structure, priority) VALUES
('midtrans', 'Midtrans', TRUE, TRUE, TRUE, '["IDR"]', '["ID"]', '{"api_key": "", "api_url": "https://api.midtrans.com"}', '{"IDR": {"percentage": 2.9, "fixed": 2000}}', 1),
('stripe', 'Stripe', FALSE, TRUE, TRUE, '["USD","EUR","GBP","SGD","MYR","THB","AUD","JPY"]', '["US","GB","DE","FR","AU","JP","SG","MY","TH"]', '{"api_key": "", "api_url": "https://api.stripe.com"}', '{"USD": {"percentage": 2.9, "fixed": 0.30}, "EUR": {"percentage": 2.9, "fixed": 0.25}}', 2),
('paypal', 'PayPal', FALSE, TRUE, TRUE, '["USD","EUR","GBP","SGD","MYR","THB","AUD"]', '["US","GB","DE","FR","AU","SG","MY","TH"]', '{"api_key": "", "api_url": "https://api.paypal.com"}', '{"USD": {"percentage": 3.49, "fixed": 0.49}}', 3);

-- Insert default routing rules
INSERT INTO payment_gateway_routing_rules (rule_name, gateway_id, rule_type, rule_condition, priority, is_active) VALUES
('Indonesia - Midtrans Only', 1, 'country', '{"country": "ID"}', 10, TRUE),
('USD - Stripe', 2, 'currency', '{"currency": "USD"}', 5, TRUE),
('EUR - Stripe', 2, 'currency', '{"currency": "EUR"}', 5, TRUE),
('GBP - Stripe', 2, 'currency', '{"currency": "GBP"}', 5, TRUE),
('SGD - Stripe', 2, 'currency', '{"currency": "SGD"}', 5, TRUE),
('High Risk - Manual Review', 1, 'risk', '{"min_score": 80}', 20, TRUE);

-- Add payment method preferences to user table
ALTER TABLE users ADD COLUMN preferred_payment_gateway VARCHAR(50) NULL;
ALTER TABLE users ADD COLUMN preferred_payment_method ENUM('card', 'wallet', 'bank_transfer') NULL;

-- Create payment analytics table
CREATE TABLE payment_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    gateway_id INT NOT NULL,
    currency VARCHAR(3) NOT NULL,
    total_transactions INT DEFAULT 0,
    successful_transactions INT DEFAULT 0,
    failed_transactions INT DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    total_fees DECIMAL(10, 2) DEFAULT 0,
    average_transaction_value DECIMAL(10, 2) DEFAULT 0,
    success_rate DECIMAL(5, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (gateway_id) REFERENCES payment_gateways(id) ON DELETE CASCADE,
    UNIQUE KEY unique_analytics (date, gateway_id, currency),
    INDEX idx_date (date),
    INDEX idx_gateway_id (gateway_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
