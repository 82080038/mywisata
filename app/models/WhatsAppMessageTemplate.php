<?php
/**
 * MyWisata Application - WhatsApp Message Template Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class WhatsAppMessageTemplate extends Model {
    
    protected $table = 'whatsapp_message_templates';
    
    /**
     * Get by type and language
     */
    public function getByTypeAndLanguage($type, $language) {
        $sql = "SELECT * FROM {$this->table} WHERE template_type = :template_type AND language = :language AND is_active = 1 LIMIT 1";
        return $this->query($sql, ['template_type' => $type, 'language' => $language])[0] ?? null;
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        return $this->query($sql, ['id' => $id])[0] ?? null;
    }
}
