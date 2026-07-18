-- Migration 050: Tax Calculation per Country
-- This migration adds support for tax calculation per country for international expansion
-- Date: 2026-07-18
-- Purpose: Enable tax/VAT calculation for different countries

-- Create countries tax configuration table
CREATE TABLE country_tax_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_id INT NOT NULL,
    tax_name VARCHAR(100) NOT NULL, -- VAT, GST, Sales Tax, etc
    tax_rate DECIMAL(5, 2) NOT NULL, -- Percentage
    tax_type ENUM('vat', 'gst', 'sales_tax', 'service_tax', 'other') NOT NULL,
    is_compound BOOLEAN DEFAULT FALSE, -- Whether tax is compounded
    applies_to ENUM('all', 'accommodation', 'transport', 'activities', 'food', 'other') DEFAULT 'all',
    threshold_amount DECIMAL(10, 2) NULL, -- Minimum amount for tax to apply
    threshold_currency VARCHAR(3) NULL,
    is_inclusive BOOLEAN DEFAULT FALSE, -- Whether tax is included in displayed price
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    expiry_date DATE NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_country_id (country_id),
    INDEX idx_is_active (is_active),
    INDEX idx_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert tax configurations for ASEAN countries
INSERT INTO country_tax_config (country_id, tax_name, tax_rate, tax_type, is_compound, applies_to, is_inclusive, effective_date, description) VALUES
-- Indonesia (11% VAT)
(1, 'PPN', 11.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax (Pajak Pertambahan Nilai)'),

-- Singapore (8% GST)
(2, 'GST', 8.00, 'gst', FALSE, 'all', FALSE, '2024-01-01', 'Goods and Services Tax'),

-- Malaysia (6% SST)
(3, 'SST', 6.00, 'sales_tax', FALSE, 'all', FALSE, '2018-09-01', 'Sales and Service Tax'),

-- Thailand (7% VAT)
(4, 'VAT', 7.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax'),

-- Vietnam (10% VAT)
(5, 'VAT', 10.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax'),

-- Philippines (12% VAT)
(6, 'VAT', 12.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax'),

-- Cambodia (10% VAT)
(8, 'VAT', 10.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax'),

-- Laos (10% VAT)
(9, 'VAT', 10.00, 'vat', FALSE, 'all', FALSE, '2024-01-01', 'Value Added Tax'),

-- Myanmar (5% Commercial Tax)
(10, 'Commercial Tax', 5.00, 'sales_tax', FALSE, 'all', FALSE, '2024-01-01', 'Commercial Tax');

-- Create tax exemptions table
CREATE TABLE tax_exemptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_tax_config_id INT NOT NULL,
    exemption_type VARCHAR(100) NOT NULL, -- diplomatic, small_business, tourism_promotion, etc
    exemption_condition JSON NOT NULL, -- Conditions for exemption
    exemption_percentage DECIMAL(5, 2) NULL, -- Partial exemption percentage
    is_full_exemption BOOLEAN DEFAULT FALSE,
    requires_documentation BOOLEAN DEFAULT TRUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (country_tax_config_id) REFERENCES country_tax_config(id) ON DELETE CASCADE,
    INDEX idx_country_tax_config_id (country_tax_config_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tax calculation log table
CREATE TABLE tax_calculation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NULL,
    invoice_id INT NULL,
    country_id INT NOT NULL,
    tax_config_id INT NOT NULL,
    base_amount DECIMAL(10, 2) NOT NULL,
    tax_amount DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL,
    calculation_date DATETIME NOT NULL,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_config_id) REFERENCES country_tax_config(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_country_id (country_id),
    INDEX idx_calculation_date (calculation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add tax columns to bookings table
ALTER TABLE bookings ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN tax_rate DECIMAL(5, 2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN tax_currency VARCHAR(3) NULL;
ALTER TABLE bookings ADD COLUMN tax_included BOOLEAN DEFAULT FALSE;
ALTER TABLE bookings ADD COLUMN country_id INT NULL;

-- Add foreign key for country_id in bookings
ALTER TABLE bookings ADD FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL;

-- Add tax columns to invoices table
ALTER TABLE invoices ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE invoices ADD COLUMN tax_rate DECIMAL(5, 2) DEFAULT 0;
ALTER TABLE invoices ADD COLUMN tax_currency VARCHAR(3) NULL;
ALTER TABLE invoices ADD COLUMN tax_included BOOLEAN DEFAULT FALSE;
ALTER TABLE invoices ADD COLUMN tax_breakdown JSON NULL; -- Detailed tax breakdown

-- Add tax columns to payment_transactions table
ALTER TABLE payment_transactions ADD COLUMN tax_amount DECIMAL(10, 2) DEFAULT 0;
ALTER TABLE payment_transactions ADD COLUMN tax_collected BOOLEAN DEFAULT FALSE;

-- Create tax reporting table
CREATE TABLE tax_reporting (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_date DATE NOT NULL,
    country_id INT NOT NULL,
    tax_config_id INT NOT NULL,
    total_bookings INT DEFAULT 0,
    total_base_amount DECIMAL(10, 2) DEFAULT 0,
    total_tax_collected DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) NOT NULL,
    report_type ENUM('daily', 'monthly', 'quarterly', 'yearly') DEFAULT 'daily',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_report (report_date, country_id, tax_config_id, report_type),
    INDEX idx_report_date (report_date),
    INDEX idx_country_id (country_id),
    INDEX idx_report_type (report_type),
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    FOREIGN KEY (tax_config_id) REFERENCES country_tax_config(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tax number configuration for businesses
CREATE TABLE business_tax_numbers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    country_id INT NOT NULL,
    tax_number VARCHAR(50) NOT NULL, -- VAT number, GST number, etc
    tax_number_type VARCHAR(50) NOT NULL, -- NPWP, GSTIN, VAT ID, etc
    business_name VARCHAR(255) NULL,
    business_address TEXT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_date DATETIME NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_country (user_id, country_id),
    INDEX idx_user_id (user_id),
    INDEX idx_country_id (country_id),
    INDEX idx_tax_number (tax_number),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tax invoice numbers table
CREATE TABLE tax_invoice_numbers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    invoice_id INT NOT NULL,
    country_id INT NOT NULL,
    tax_invoice_number VARCHAR(100) NOT NULL UNIQUE,
    tax_invoice_date DATE NOT NULL,
    tax_invoice_series VARCHAR(50) NOT NULL, -- Series for sequential numbering
    is_valid BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_country_id (country_id),
    INDEX idx_tax_invoice_date (tax_invoice_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tax rate history table for tracking changes
CREATE TABLE tax_rate_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    country_tax_config_id INT NOT NULL,
    old_rate DECIMAL(5, 2) NULL,
    new_rate DECIMAL(5, 2) NOT NULL,
    change_reason VARCHAR(255) NULL,
    changed_by INT NULL, -- User ID who made the change
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (country_tax_config_id) REFERENCES country_tax_config(id) ON DELETE CASCADE,
    INDEX idx_country_tax_config_id (country_tax_config_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
