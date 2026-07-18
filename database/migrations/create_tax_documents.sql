-- MyWisata Application - Create Tax Documents Table
-- Create table for tax document generation
-- Created: 2026-07-16

-- Create tax_documents table
CREATE TABLE IF NOT EXISTS tax_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tax_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(20) DEFAULT 'faktur' COMMENT 'faktur, invoice, receipt',
    ppn DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'VAT/PPN',
    pph DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Income Tax/PPh',
    total_tax DECIMAL(15, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'issued' COMMENT 'issued, reported, cancelled',
    reported_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX idx_tax_number (tax_number),
    INDEX idx_invoice_id (invoice_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
