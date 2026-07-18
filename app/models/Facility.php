<?php

/**
 * MyWisata Application - Facility Model
 *
 * Centralized facility management for all entity types
 * (destinations, hotels, restaurants, events, tour guides).
 */
class Facility extends Model
{
    protected $table = 'facilities';

    /**
     * Get all active facilities
     */
    public function getAllActive()
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY category, sort_order";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get facilities grouped by category
     */
    public function getByCategory()
    {
        $all = $this->getAllActive();
        $grouped = [];
        foreach ($all as $f) {
            $grouped[$f['category']][] = $f;
        }
        return $grouped;
    }

    /**
     * Get facilities for a specific entity
     */
    public function getEntityFacilities($entityType, $entityId)
    {
        $sql = "SELECT f.*, ef.is_available, ef.notes
                FROM {$this->table} f
                INNER JOIN entity_facilities ef ON ef.facility_id = f.id
                WHERE ef.entity_type = :etype AND ef.entity_id = :eid
                ORDER BY f.category, f.sort_order";
        return $this->db->query($sql, ['etype' => $entityType, 'eid' => $entityId])->fetchAll();
    }

    /**
     * Get facilities for entity grouped by category
     */
    public function getEntityFacilitiesGrouped($entityType, $entityId)
    {
        $facilities = $this->getEntityFacilities($entityType, $entityId);
        $grouped = [];
        foreach ($facilities as $f) {
            $grouped[$f['category']][] = $f;
        }
        return $grouped;
    }

    /**
     * Assign facility to entity
     */
    public function assign($entityType, $entityId, $facilityId, $notes = null)
    {
        $sql = "INSERT INTO entity_facilities (entity_type, entity_id, facility_id, notes, is_available)
                VALUES (:etype, :eid, :fid, :notes, 1)
                ON DUPLICATE KEY UPDATE is_available = 1, notes = :notes2";
        return $this->db->query($sql, [
            'etype' => $entityType,
            'eid' => $entityId,
            'fid' => $facilityId,
            'notes' => $notes,
            'notes2' => $notes,
        ]);
    }

    /**
     * Remove facility from entity
     */
    public function remove($entityType, $entityId, $facilityId)
    {
        $sql = "DELETE FROM entity_facilities 
                WHERE entity_type = :etype AND entity_id = :eid AND facility_id = :fid";
        return $this->db->query($sql, ['etype' => $entityType, 'eid' => $entityId, 'fid' => $facilityId]);
    }

    /**
     * Sync facilities for an entity (replace all)
     */
    public function sync($entityType, $entityId, $facilityIds)
    {
        $this->db->query(
            "DELETE FROM entity_facilities WHERE entity_type = :etype AND entity_id = :eid",
            ['etype' => $entityType, 'eid' => $entityId]
        );
        foreach ($facilityIds as $fid) {
            $this->assign($entityType, $entityId, $fid);
        }
    }

    /**
     * Get facility by slug
     */
    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1";
        return $this->db->query($sql, ['slug' => $slug])->fetch();
    }

    /**
     * Create new facility
     */
    public function createFacility($data)
    {
        $sql = "INSERT INTO {$this->table} (name, slug, icon, category, description, is_active, sort_order)
                VALUES (:name, :slug, :icon, :category, :description, 1, :sort_order)";
        $this->db->query($sql, [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? 'fa-check',
            'category' => $data['category'] ?? 'general',
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Category labels in Indonesian
     */
    public static function categoryLabel($category)
    {
        $labels = [
            'general' => 'Umum',
            'accommodation' => 'Penginapan',
            'dining' => 'Kuliner',
            'recreation' => 'Rekreasi',
            'accessibility' => 'Aksesibilitas',
            'safety' => 'Keamanan',
            'transport' => 'Transportasi',
            'service' => 'Layanan',
            'view' => 'Pemandangan',
            'equipment' => 'Peralatan',
        ];
        return $labels[$category] ?? ucfirst($category);
    }

    /**
     * Category icons
     */
    public static function categoryIcon($category)
    {
        $icons = [
            'general' => 'fa-gear',
            'accommodation' => 'fa-bed',
            'dining' => 'fa-utensils',
            'recreation' => 'fa-umbrella-beach',
            'accessibility' => 'fa-wheelchair',
            'safety' => 'fa-shield',
            'transport' => 'fa-bus',
            'service' => 'fa-bell-concierge',
            'view' => 'fa-mountain',
            'equipment' => 'fa-toolbox',
        ];
        return $icons[$category] ?? 'fa-gear';
    }

    /**
     * Render facility badges for an entity
     */
    public function renderBadges($entityType, $entityId, $limit = null)
    {
        $facilities = $this->getEntityFacilities($entityType, $entityId);
        if (empty($facilities)) {
            return '';
        }
        if ($limit) {
            $facilities = array_slice($facilities, 0, $limit);
        }
        $html = '';
        foreach ($facilities as $f) {
            $html .= '<span class="badge bg-light text-dark border me-1 mb-1">'
                . '<i class="fas ' . htmlspecialchars($f['icon']) . ' text-success me-1"></i>'
                . htmlspecialchars($f['name'])
                . '</span>';
        }
        return $html;
    }
}
