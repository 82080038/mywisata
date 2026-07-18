<?php
/**
 * MyWisata Application - Cooking Class Menu Item Model
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */

class CookingClassMenuItem extends Model {
    
    protected $table = 'cooking_class_menu_items';
    
    /**
     * Get by class ID
     */
    public function getByClassId($classId) {
        $sql = "SELECT * FROM {$this->table} WHERE cooking_class_id = :cooking_class_id ORDER BY display_order";
        return $this->query($sql, ['cooking_class_id' => $classId]);
    }
    
    /**
     * Create menu item
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (cooking_class_id, item_name, description, ingredients, is_vegetarian, is_halal, display_order) 
                VALUES (:cooking_class_id, :item_name, :description, :ingredients, :is_vegetarian, :is_halal, :display_order)";
        return $this->execute($sql, $data);
    }
}
