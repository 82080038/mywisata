<?php
/**
 * MyWisata Application - Invoice Helper
 * 
 * Handles invoice generation for bookings and payments.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class Invoice {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Generate invoice for booking
     * 
     * @param int $bookingId Booking ID
     * @return int|false Invoice ID
     */
    public function generateInvoice($bookingId) {
        try {
            $this->db->beginTransaction();
            
            // Get booking details
            $booking = $this->getBookingDetails($bookingId);
            
            if (!$booking) {
                $this->db->rollBack();
                return false;
            }
            
            // Check if invoice already exists
            $existingInvoice = $this->getInvoiceByBookingId($bookingId);
            if ($existingInvoice) {
                $this->db->rollBack();
                return $existingInvoice['id'];
            }
            
            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Calculate totals
            $subtotal = $booking['amount'];
            $tax = $this->calculateTax($subtotal);
            $discount = $this->calculateDiscount($bookingId, $subtotal);
            $total = $subtotal + $tax - $discount;
            
            // Create invoice
            $sql = "INSERT INTO invoices 
                    (invoice_number, booking_id, user_id, subtotal, tax, discount, total, status, created_at)
                    VALUES (:invoice_number, :booking_id, :user_id, :subtotal, :tax, :discount, :total, 'paid', NOW())";
            
            $this->db->query($sql, [
                'invoice_number' => $invoiceNumber,
                'booking_id' => $bookingId,
                'user_id' => $booking['user_id'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total
            ]);
            
            $invoiceId = $this->db->lastInsertId();
            
            // Add invoice items
            $this->addInvoiceItems($invoiceId, $booking);
            
            $this->db->commit();
            
            Logger::audit('GENERATE_INVOICE', 'invoices', "Generated invoice ID: {$invoiceId}", [], [
                'invoice_id' => $invoiceId,
                'booking_id' => $bookingId,
                'invoice_number' => $invoiceNumber
            ]);
            
            return $invoiceId;
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Failed to generate invoice', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Generate invoice number
     * 
     * @return string
     */
    private function generateInvoiceNumber() {
        $prefix = 'INV';
        $date = date('Ymd');
        $sequence = $this->getNextSequence();
        
        return $prefix . '-' . $date . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get next sequence number
     * 
     * @return int
     */
    private function getNextSequence() {
        $sql = "SELECT COUNT(*) as count FROM invoices WHERE DATE(created_at) = CURDATE()";
        $result = $this->db->query($sql)->fetch();
        
        return ($result['count'] ?? 0) + 1;
    }
    
    /**
     * Calculate tax
     * 
     * @param float $amount Amount
     * @return float
     */
    private function calculateTax($amount) {
        $taxRate = 0.11; // 11% VAT
        return $amount * $taxRate;
    }
    
    /**
     * Calculate discount
     * 
     * @param int $bookingId Booking ID
     * @param float $amount Amount
     * @return float
     */
    private function calculateDiscount($bookingId, $amount) {
        // Check for applied promo codes
        $sql = "SELECT SUM(discount_amount) as total_discount 
                FROM cart_promo_codes 
                WHERE cart_id IN (SELECT id FROM cart_items WHERE booking_id = :booking_id)";
        
        $result = $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
        
        return $result['total_discount'] ?? 0;
    }
    
    /**
     * Add invoice items
     * 
     * @param int $invoiceId Invoice ID
     * @param array $booking Booking data
     * @return bool
     */
    private function addInvoiceItems($invoiceId, $booking) {
        $sql = "INSERT INTO invoice_items 
                (invoice_id, item_type, item_id, description, quantity, unit_price, total)
                VALUES (:invoice_id, :item_type, :item_id, :description, :quantity, :unit_price, :total)";
        
        return $this->db->query($sql, [
            'invoice_id' => $invoiceId,
            'item_type' => $booking['item_type'],
            'item_id' => $booking['item_id'],
            'description' => $booking['item_name'] ?? 'Service',
            'quantity' => 1,
            'unit_price' => $booking['amount'],
            'total' => $booking['amount']
        ]);
    }
    
    /**
     * Get invoice by ID
     * 
     * @param int $invoiceId Invoice ID
     * @return array|false
     */
    public function getInvoiceById($invoiceId) {
        $sql = "SELECT i.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                u.address as user_address
                FROM invoices i
                LEFT JOIN users u ON i.user_id = u.id
                WHERE i.id = :id";
        
        return $this->db->query($sql, ['id' => $invoiceId])->fetch();
    }
    
    /**
     * Get invoice by booking ID
     * 
     * @param int $bookingId Booking ID
     * @return array|false
     */
    public function getInvoiceByBookingId($bookingId) {
        $sql = "SELECT * FROM invoices WHERE booking_id = :booking_id";
        return $this->db->query($sql, ['booking_id' => $bookingId])->fetch();
    }
    
    /**
     * Get invoice items
     * 
     * @param int $invoiceId Invoice ID
     * @return array
     */
    public function getInvoiceItems($invoiceId) {
        $sql = "SELECT * FROM invoice_items WHERE invoice_id = :invoice_id";
        return $this->db->query($sql, ['invoice_id' => $invoiceId])->fetchAll();
    }
    
    /**
     * Get user invoices
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserInvoices($userId) {
        $sql = "SELECT i.*, b.booking_date, b.status as booking_status
                FROM invoices i
                LEFT JOIN bookings b ON i.booking_id = b.id
                WHERE i.user_id = :user_id
                ORDER BY i.created_at DESC";
        
        return $this->db->query($sql, ['user_id' => $userId])->fetchAll();
    }
    
    /**
     * Get booking details
     * 
     * @param int $bookingId Booking ID
     * @return array|false
     */
    private function getBookingDetails($bookingId) {
        $sql = "SELECT b.*, u.id as user_id
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.id = :id";
        
        return $this->db->query($sql, ['id' => $bookingId])->fetch();
    }
    
    /**
     * Generate invoice PDF (placeholder)
     * 
     * @param int $invoiceId Invoice ID
     * @return string|false PDF file path
     */
    public function generatePDF($invoiceId) {
        $invoice = $this->getInvoiceById($invoiceId);
        $items = $this->getInvoiceItems($invoiceId);
        
        if (!$invoice) {
            return false;
        }
        
        // In production, use a PDF library like TCPDF or DomPDF
        // For now, return a placeholder path
        $pdfPath = ROOT_PATH . '/storage/invoices/' . $invoice['invoice_number'] . '.pdf';
        
        // Create directory if it doesn't exist
        $pdfDir = dirname($pdfPath);
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }
        
        // Placeholder - in production, generate actual PDF
        file_put_contents($pdfPath, 'PDF placeholder for invoice ' . $invoice['invoice_number']);
        
        return $pdfPath;
    }
    
    /**
     * Send invoice email
     * 
     * @param int $invoiceId Invoice ID
     * @return bool
     */
    public function sendInvoiceEmail($invoiceId) {
        $invoice = $this->getInvoiceById($invoiceId);
        
        if (!$invoice) {
            return false;
        }
        
        // In production, use email library to send invoice
        Logger::info('Sending invoice email', [
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice['invoice_number'],
            'email' => $invoice['user_email']
        ]);
        
        return true;
    }
    
    /**
     * Get invoice statistics
     * 
     * @param int $userId Optional user ID
     * @return array
     */
    public function getInvoiceStats($userId = null) {
        $sql = "SELECT 
                COUNT(*) as total_invoices,
                SUM(total) as total_revenue,
                SUM(tax) as total_tax,
                SUM(discount) as total_discount,
                AVG(total) as average_invoice
                FROM invoices";
        
        if ($userId) {
            $sql .= " WHERE user_id = :user_id";
        }
        
        $params = $userId ? ['user_id' => $userId] : [];
        
        return $this->db->query($sql, $params)->fetch();
    }
    
    /**
     * Mark invoice as sent
     * 
     * @param int $invoiceId Invoice ID
     * @return bool
     */
    public function markAsSent($invoiceId) {
        $sql = "UPDATE invoices SET sent_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $invoiceId]);
    }
    
    /**
     * Get pending invoices
     * 
     * @return array
     */
    public function getPendingInvoices() {
        $sql = "SELECT * FROM invoices WHERE sent_at IS NULL ORDER BY created_at ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
