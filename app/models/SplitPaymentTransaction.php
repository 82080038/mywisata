<?php
/**
 * MyWisata Application - Split Payment Transaction Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class SplitPaymentTransaction extends Model {
    
    protected $table = 'split_payment_transactions';
    
    /**
     * Create transaction
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (split_payment_group_id, participant_id, payment_transaction_id, amount, currency, payment_method) 
                VALUES (:split_payment_group_id, :participant_id, :payment_transaction_id, :amount, :currency, :payment_method)";
        return $this->execute($sql, $data);
    }
}
