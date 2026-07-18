-- Migration 051: GDPR Compliance Features
-- This migration adds GDPR compliance features for EU market expansion
-- Date: 2026-07-18
-- Purpose: Enable GDPR compliance for European users

-- Create data processing records table (Article 30 GDPR)
CREATE TABLE data_processing_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_name VARCHAR(255) NOT NULL,
    process_description TEXT NOT NULL,
    data_categories JSON NOT NULL, -- Categories of personal data processed
    purposes JSON NOT NULL, -- Purposes of processing
    data_subjects JSON NOT NULL, -- Categories of data subjects
    recipients JSON NOT NULL, -- Categories of recipients
    transfers JSON NULL, -- International transfers
    retention_period VARCHAR(100) NOT NULL,
    security_measures TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_process_name (process_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data processing records
INSERT INTO data_processing_records (process_name, process_description, data_categories, purposes, data_subjects, recipients, retention_period, security_measures) VALUES
('User Registration', 'Processing user registration data', '["name", "email", "phone", "address"]', '["account_creation", "authentication"]', '["customers"]', '["internal_staff"]', '2 years after account closure', 'Encryption at rest and in transit, access controls'),
('Booking Processing', 'Processing booking and payment data', '["name", "email", "phone", "payment_details", "travel_details"]', '["booking_fulfillment", "payment_processing"]', '["customers"]', '["payment_providers", "hotels", "tour_guides"]', '3 years after booking completion', 'PCI DSS compliance, encryption, access logs'),
('Marketing Communications', 'Processing marketing preferences and communications', '["email", "preferences", "behavioral_data"]', '["marketing", "personalization"]', '["customers"]', '["marketing_partners"]', 'Until consent withdrawal', 'Consent management, unsubscribe functionality'),
('Analytics', 'Processing analytics and usage data', '["ip_address", "user_agent", "behavioral_data", "session_data"]', '["analytics", "service_improvement"]', '["users"]', '["analytics_providers"]', '2 years', 'Anonymization, aggregation, access controls');

-- Create consent records table
CREATE TABLE consent_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    consent_type ENUM('marketing', 'analytics', 'cookies', 'data_sharing', 'terms_of_service', 'privacy_policy') NOT NULL,
    consent_given BOOLEAN NOT NULL,
    consent_date DATETIME NOT NULL,
    consent_method ENUM('checkbox', 'banner', 'popup', 'api', 'email') NOT NULL,
    consent_version VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    withdrawn_at DATETIME NULL,
    withdrawal_method VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_consent_given (consent_given),
    INDEX idx_consent_date (consent_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create data subject requests table (GDPR Article 15-21)
CREATE TABLE data_subject_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    request_type ENUM('access', 'rectification', 'erasure', 'restriction', 'portability', 'objection') NOT NULL,
    request_status ENUM('pending', 'processing', 'completed', 'rejected', 'requires_verification') DEFAULT 'pending',
    request_details TEXT NULL,
    response_details TEXT NULL,
    requested_at DATETIME NOT NULL,
    processed_at DATETIME NULL,
    processed_by INT NULL,
    rejection_reason TEXT NULL,
    verification_method VARCHAR(50) NULL,
    verification_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_request_type (request_type),
    INDEX idx_request_status (request_status),
    INDEX idx_requested_at (requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create data retention policy table
CREATE TABLE data_retention_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data_category VARCHAR(100) NOT NULL,
    retention_period VARCHAR(100) NOT NULL, -- e.g., "2 years", "until consent withdrawal"
    retention_basis VARCHAR(255) NOT NULL, -- Legal basis for retention
    deletion_method ENUM('secure_delete', 'anonymization', 'archival') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_data_category (data_category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default retention policies
INSERT INTO data_retention_policies (data_category, retention_period, retention_basis, deletion_method) VALUES
('user_profile', '2 years after account closure', 'Legal requirement', 'secure_delete'),
('booking_data', '3 years after booking completion', 'Legal requirement', 'secure_delete'),
('payment_data', '7 years', 'Tax law requirement', 'secure_delete'),
('marketing_data', 'Until consent withdrawal', 'Consent-based', 'secure_delete'),
('analytics_data', '2 years', 'Legitimate interest', 'anonymization'),
('support_tickets', '2 years after resolution', 'Legal requirement', 'secure_delete'),
('passport_scans', '90 days after travel completion', 'Data minimization', 'secure_delete');

-- Create data breach log table (GDPR Article 33)
CREATE TABLE data_breach_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    breach_id VARCHAR(100) NOT NULL UNIQUE,
    breach_type VARCHAR(100) NOT NULL,
    breach_description TEXT NOT NULL,
    affected_data_categories JSON NOT NULL,
    affected_users_count INT DEFAULT 0,
    affected_user_ids JSON NULL,
    breach_discovery_date DATETIME NOT NULL,
    breach_occurred_date DATETIME NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    is_contained BOOLEAN DEFAULT FALSE,
    containment_date DATETIME NULL,
    notification_sent_to_authority BOOLEAN DEFAULT FALSE,
    notification_date DATETIME NULL,
    authority_reference VARCHAR(100) NULL,
    users_notified BOOLEAN DEFAULT FALSE,
    user_notification_date DATETIME NULL,
    mitigation_steps TEXT NULL,
    responsible_person_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_breach_id (breach_id),
    INDEX idx_breach_discovery_date (breach_discovery_date),
    INDEX idx_severity (severity),
    FOREIGN KEY (responsible_person_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cookie consent table
CREATE TABLE cookie_consent (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL, -- NULL for anonymous users
    session_id VARCHAR(100) NULL,
    consent_given BOOLEAN NOT NULL,
    consent_date DATETIME NOT NULL,
    essential_cookies BOOLEAN DEFAULT TRUE,
    analytics_cookies BOOLEAN DEFAULT FALSE,
    marketing_cookies BOOLEAN DEFAULT FALSE,
    functional_cookies BOOLEAN DEFAULT FALSE,
    cookie_preferences JSON NULL, -- Detailed cookie preferences
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    consent_version VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_consent_date (consent_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create privacy policy versions table
CREATE TABLE privacy_policy_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version VARCHAR(50) NOT NULL UNIQUE,
    effective_date DATE NOT NULL,
    policy_content TEXT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_version (version),
    INDEX idx_effective_date (effective_date),
    INDEX idx_is_current (is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial privacy policy version
INSERT INTO privacy_policy_versions (version, effective_date, policy_content, is_current) VALUES
('1.0', CURDATE(), 'Privacy Policy for MyWisata Application - Version 1.0', TRUE);

-- Create user policy acceptance table
CREATE TABLE user_policy_acceptance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    policy_type ENUM('privacy_policy', 'terms_of_service', 'cookie_policy') NOT NULL,
    policy_version VARCHAR(50) NOT NULL,
    accepted_at DATETIME NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_policy (user_id, policy_type, policy_version),
    INDEX idx_user_id (user_id),
    INDEX idx_policy_type (policy_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create data processing agreements table (Article 28 GDPR)
CREATE TABLE data_processing_agreements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agreement_name VARCHAR(255) NOT NULL,
    vendor_name VARCHAR(255) NOT NULL,
    vendor_contact_email VARCHAR(255) NOT NULL,
    data_categories_shared JSON NOT NULL,
    processing_purposes JSON NOT NULL,
    security_obligations TEXT NOT NULL,
    data_retention_requirements TEXT NOT NULL,
    data_return_provisions TEXT NOT NULL,
    breach_notification_provisions TEXT NOT NULL,
    agreement_start_date DATE NOT NULL,
    agreement_end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    agreement_document_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_vendor_name (vendor_name),
    INDEX idx_is_active (is_active),
    INDEX idx_agreement_start_date (agreement_start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default DPAs for common vendors
INSERT INTO data_processing_agreements (agreement_name, vendor_name, vendor_contact_email, data_categories_shared, processing_purposes, security_obligations, data_retention_requirements, data_return_provisions, breach_notification_provisions, agreement_start_date) VALUES
('Midtrans Payment Processing', 'Midtrans', 'support@midtrans.com', '["payment_details", "billing_address"]', '["payment_processing"]', 'PCI DSS compliance, encryption, access controls', '7 years', 'Data deleted upon agreement termination', 'Notify within 72 hours of breach', CURDATE()),
('Stripe Payment Processing', 'Stripe', 'support@stripe.com', '["payment_details", "billing_address"]', '["payment_processing"]', 'PCI DSS compliance, encryption, access controls', '7 years', 'Data deleted upon agreement termination', 'Notify within 72 hours of breach', CURDATE()),
('OpenAI AI Processing', 'OpenAI', 'support@openai.com', '["user_queries", "preferences"]', '["ai_assistance", "personalization"]', 'Encryption, access controls, data minimization', '30 days', 'Data deleted upon request', 'Notify within 72 hours of breach', CURDATE());

-- Add GDPR-related columns to users table
ALTER TABLE users ADD COLUMN gdpr_consent_given BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN gdpr_consent_date DATETIME NULL;
ALTER TABLE users ADD COLUMN data_processing_consent BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN marketing_consent BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN cookie_consent_given BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN cookie_consent_date DATETIME NULL;
ALTER TABLE users ADD COLUMN privacy_policy_version VARCHAR(50) NULL;
ALTER TABLE users ADD COLUMN terms_of_service_version VARCHAR(50) NULL;

-- Create right to be forgotten requests queue
CREATE TABLE right_to_be_forgotten_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    request_date DATETIME NOT NULL,
    processing_status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    systems_to_erase JSON NOT NULL, -- List of systems where data should be erased
    erasure_completion_date DATETIME NULL,
    verification_required BOOLEAN DEFAULT TRUE,
    verification_completed BOOLEAN DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_processing_status (processing_status),
    INDEX idx_request_date (request_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create anonymization log table
CREATE TABLE anonymization_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    anonymization_type ENUM('partial', 'full') NOT NULL,
    data_fields_anonymized JSON NOT NULL,
    anonymization_method VARCHAR(50) NOT NULL,
    anonymization_date DATETIME NOT NULL,
    performed_by INT NULL,
    reason VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_anonymization_date (anonymization_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create DPO (Data Protection Officer) configuration table
CREATE TABLE dpo_configuration (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dpo_name VARCHAR(255) NOT NULL,
    dpo_email VARCHAR(255) NOT NULL,
    dpo_phone VARCHAR(50) NULL,
    dpo_address TEXT NULL,
    contact_method VARCHAR(50) DEFAULT 'email',
    is_active BOOLEAN DEFAULT TRUE,
    appointed_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default DPO configuration
INSERT INTO dpo_configuration (dpo_name, dpo_email, dpo_phone, contact_method, appointed_date) VALUES
('Data Protection Officer', 'dpo@mywisata.com', '+6281234567890', 'email', CURDATE());
