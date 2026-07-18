<?php
/**
 * MyWisata Application - Payment Reminder Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class PaymentReminder extends Model {
    
    protected $table = 'payment_reminders';
    
    /**
     * Create reminder
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (split_payment_group_id, participant_id, reminder_type, scheduled_date, sent_date, status) 
                VALUES (:split_payment_group_id, :participant_id, :reminder_type, :scheduled_date, :sent_date, :status)";
        return $this->execute($sql, $data);
    }
}
