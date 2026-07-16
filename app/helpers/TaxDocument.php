<?php
/**
 * MyWisata Application - Tax Document Helper
 * 
 * Handles tax document generation for Indonesian tax compliance.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class TaxDocument {
    
    private $db;
    private $taxRate = 0.11; // 11% VAT (PPN)
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate tax document for invoice
     * 
     * @param int $invoiceId Invoice ID
     * @return int|false Tax document ID
     */
    public function generateTaxDocument($invoiceId) {
        try {
            $this->db->beginTransaction();
            
            // Get invoice details
            $invoice = $this->getInvoiceById($invoiceId);
            
            if (!$invoice) {
                $this->db->rollBack();
                return false;
            }
            
            // Check if tax document already exists
            $existingDoc = $this->getTaxDocumentByInvoiceId($invoiceId);
            if ($existingDoc) {
                $this->db->rollBack();
                return $existingDoc['id'];
            }
            
            // Generate tax document number
            $taxNumber = $this->generateTaxNumber();
            
            // Calculate tax amounts
            $ppn = $invoice['tax'];
            $pph = $this->calculatePPh($invoice['total']);
            
            // Create tax document
            $sql = "INSERT INTO tax_documents 
                    (tax_number, invoice_id, document_type, ppn, pph, total_tax, status, created_at)
                    VALUES (:tax_number, :invoice_id, 'faktur', :ppn, :pph, :total_tax, 'issued', NOW())";
            
            $this->db->query($sql, [
                'tax_number' => $taxNumber,
                'invoice_id' => $invoiceId,
                'ppn' => $ppn,
                'pph' => $pph,
                'total_tax' => $ppn + $pph
            ]);
            
            $taxDocId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            Logger::audit('GENERATE_TAX_DOCUMENT', 'tax_documents', "Generated tax document ID: {$taxDocId}", [], [
                'tax_doc_id' => $taxDocId,
                'invoice_id' => $invoiceId,
                'tax_number' => $taxNumber
            ]);
            
            return $taxDocId;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to generate tax document', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Generate tax number
     * 
     * @return string
     */
    private function generateTaxNumber() {
        $prefix = 'FP';
        $date = date('Ymd');
        $sequence = $this->getNextTaxSequence();
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get next tax sequence number
     * 
     * @return int
     */
    private function getNextTaxSequence() {
        $sql = "SELECT COUNT(*) as count FROM tax_documents WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql)->fetch();
        
        return ($result['count'] ?? 0) + 1;
    }
    
    /**
     * Calculate PPh (Income Tax)
     * 
     * @param float $amount Amount
     * @return float
     */
    private function calculatePPh($amount) {
        // PPh rate varies by entity type
        // For individuals: 2.5% of gross revenue
        // For companies: varies (typically 1-2%)
        $pphRate = 0.025; // 2.5%
        return $amount * $pphRate;
    }
    
    /**
     * Get invoice by ID
     * 
     * @param int $invoiceId Invoice ID
     * @return array|false
     */
    private function getInvoiceById($invoiceId) {
        $sql = "SELECT * FROM invoices WHERE id = :id";
        return $this->db->query($sql, ['id' => $invoiceId])->fetch();
    }
    
    /**
     * Get tax document by invoice ID
     * 
     * @param int $invoiceId Invoice ID
     * @return array|false
     */
    public function getTaxDocumentByInvoiceId($invoiceId) {
        $sql = "SELECT * FROM tax_documents WHERE invoice_id = :invoice_id";
        return $this->db->query($sql, ['invoice_id' => $invoiceId])->fetch();
    }
    
    /**
     * Get tax document by ID
     * 
     * @param int $taxDocId Tax document ID
     * @return array|false
     */
    public function getTaxDocumentById($taxDocId) {
        $sql = "SELECT td.*, i.invoice_number, i.total as invoice_total, i.subtotal, i.tax as invoice_tax,
                u.name as user_name, u.npwp as user_npwp, u.address as user_address
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                LEFT JOIN users u ON i.user_id = u.id
                WHERE td.id = :id";
        
        return $this->db->query($sql, ['id' => $taxDocId])->fetch();
    }
    
    /**
     * Get user tax documents
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserTaxDocuments($userId) {
        $sql = "SELECT td.*, i.invoice_number, i.total as invoice_total
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                WHERE i.user_id = :user_id
                ORDER BY td.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Generate monthly tax report
     * 
     * @param int $year Year
     * @param int $month Month
     * @return array
     */
    public function generateMonthlyReport($year, $month) {
        $sql = "SELECT 
                COUNT(*) as total_documents,
                SUM(ppn) as total_ppn,
                SUM(pph) as total_pph,
                SUM(total_tax) as total_tax,
                SUM(i.total) as total_revenue
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                WHERE YEAR(td.created_at) = :year
                AND MONTH(td.created_at) = :month";
        
        $result = $this->db->query($sql, [
            'year' => $year,
            'month' => $month
        ])->fetch();
        
        return $result;
    }
    
    /**
     * Generate quarterly tax report
     * 
     * @param int $year Year
     * @param int $quarter Quarter (1-4)
     * @return array
     */
    public function generateQuarterlyReport($year, $quarter) {
        $months = [
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12]
        ];
        
        $monthList = $months[$quarter] ?? [1, 2, 3];
        
        $sql = "SELECT 
                COUNT(*) as total_documents,
                SUM(ppn) as total_ppn,
                SUM(pph) as total_pph,
                SUM(total_tax) as total_tax,
                SUM(i.total) as total_revenue
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                WHERE YEAR(td.created_at) = :year
                AND MONTH(td.created_at) IN (" . implode(',', $monthList) . ")";
        
        $result = $this->db->query($sql, ['year' => $year])->fetch();
        
        return $result;
    }
    
    /**
     * Generate annual tax report
     * 
     * @param int $year Year
     * @return array
     */
    public function generateAnnualReport($year) {
        $sql = "SELECT 
                COUNT(*) as total_documents,
                SUM(ppn) as total_ppn,
                SUM(pph) as total_pph,
                SUM(total_tax) as total_tax,
                SUM(i.total) as total_revenue
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                WHERE YEAR(td.created_at) = :year";
        
        $result = $this->db->query($sql, ['year' => $year])->fetch();
        
        return $result;
    }
    
    /**
     * Generate tax document PDF (placeholder)
     * 
     * @param int $taxDocId Tax document ID
     * @return string|false PDF file path
     */
    public function generatePDF($taxDocId) {
        $taxDoc = $this->getTaxDocumentById($taxDocId);
        
        if (!$taxDoc) {
            return false;
        }
        
        // In production, use a PDF library like TCPDF or DomPDF
        // For now, return a placeholder path
        $pdfPath = ROOT_PATH . '/storage/tax_documents/' . $taxDoc['tax_number'] . '.pdf';
        
        // Create directory if it doesn't exist
        $pdfDir = dirname($pdfPath);
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        // Placeholder - in production, generate actual PDF
        file_put_contents($pdfPath, 'Tax document placeholder for ' . $taxDoc['tax_number']);
        
        return $pdfPath;
    }
    
    /**
     * Export tax data to CSV
     * 
     * @param int $year Year
     * @param int $month Month (optional)
     * @return string|false CSV file path
     */
    public function exportToCSV($year, $month = null) {
        $sql = "SELECT td.tax_number, i.invoice_number, i.total as invoice_total, 
                td.ppn, td.pph, td.total_tax, td.created_at
                FROM tax_documents td
                LEFT JOIN invoices i ON td.invoice_id = i.id
                WHERE YEAR(td.created_at) = :year";
        
        $params = ['year' => $year];
        
        if ($month) {
            $sql .= " AND MONTH(td.created_at) = :month";
            $params['month'] = $month;
        }
        
        $sql .= " ORDER BY td.created_at ASC";
        
        $results = $this->db->query($sql, $params)->fetchAll();
        
        if (empty($results)) {
            return false;
        }
        
        $csvPath = ROOT_PATH . '/storage/tax_exports/tax_' . $year;
        if ($month) {
            $csvPath .= '_' . str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        $csvPath .= '.csv';
        
        $csvDir = dirname($csvPath);
        if (!is_dir($csvDir)) {
            mkdir($csvDir, 0755, true);
        }
        
        $file = fopen($csvPath, 'w');
        fputcsv($file, ['Tax Number', 'Invoice Number', 'Invoice Total', 'PPN', 'PPh', 'Total Tax', 'Date']);
        
        foreach ($results as $row) {
            fputcsv($file, [
                $row['tax_number'],
                $row['invoice_number'],
                $row['invoice_total'],
                $row['ppn'],
                $row['pph'],
                $row['total_tax'],
                $row['created_at']
            ]);
        }
        
        fclose($file);
        
        return $csvPath;
    }
    
    /**
     * Get tax statistics
     * 
     * @param int $year Year
     * @return array
     */
    public function getTaxStatistics($year = null) {
        $sql = "SELECT 
                COUNT(*) as total_documents,
                SUM(ppn) as total_ppn,
                SUM(pph) as total_pph,
                SUM(total_tax) as total_tax
                FROM tax_documents";
        
        if ($year) {
            $sql .= " WHERE YEAR(created_at) = :year";
        }
        
        $params = $year ? ['year' => $year] : [];
        
        return $this->db->query($sql, $params)->fetch();
    }
    
    /**
     * Mark tax document as reported
     * 
     * @param int $taxDocId Tax document ID
     * @return bool
     */
    public function markAsReported($taxDocId) {
        $sql = "UPDATE tax_documents SET reported_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $taxDocId]);
    }
    
    /**
     * Get unreported tax documents
     * 
     * @return array
     */
    public function getUnreportedDocuments() {
        $sql = "SELECT * FROM tax_documents WHERE reported_at IS NULL ORDER BY created_at ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
