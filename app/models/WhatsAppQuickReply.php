<?php
/**
 * MyWisata Application - WhatsApp Quick Reply Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WhatsAppQuickReply extends Model {
    
    protected $table = 'whatsapp_quick_replies';
    
    /**
     * Get by session state
     */
    public function getBySessionState($sessionState) {
        $sql = "SELECT * FROM {$this->table} WHERE session_state = :session_state AND is_active = 1 ORDER BY display_order";
        return $this->query($sql, ['session_state' => $sessionState]);
    }
}
