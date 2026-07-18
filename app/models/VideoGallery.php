<?php

/**
 * MyWisata Application - Video Gallery Model
 *
 * Handles user-generated video content for destinations, hotels, restaurants, events.
 *
 * @version 1.0.0
 */

class VideoGallery extends Model
{
    /**
     * Get approved videos for a specific entity
     *
     * @param string $type destination|hotel|restaurant|event
     * @param int $id Entity ID
     * @return array
     */
    public function getVideos($type, $id)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            return [];
        }

        $foreignKey = $type . '_id';

        $sql = "SELECT v.*, u.name as user_name, u.avatar as user_avatar
                FROM {$table} v
                LEFT JOIN users u ON v.user_id = u.id
                WHERE v.{$foreignKey} = :id AND v.is_approved = 1
                ORDER BY v.is_featured DESC, v.created_at DESC";

        return $this->db->query($sql, ['id' => $id])->fetchAll();
    }

    /**
     * Get featured videos for a specific entity
     *
     * @param string $type
     * @param int $id
     * @param int $limit
     * @return array
     */
    public function getFeaturedVideos($type, $id, $limit = 3)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            return [];
        }

        $foreignKey = $type . '_id';

        $sql = "SELECT v.*, u.name as user_name
                FROM {$table} v
                LEFT JOIN users u ON v.user_id = u.id
                WHERE v.{$foreignKey} = :id AND v.is_approved = 1 AND v.is_featured = 1
                ORDER BY v.view_count DESC
                LIMIT :limit";

        return $this->db->query($sql, ['id' => $id, 'limit' => $limit])->fetchAll();
    }

    /**
     * Add a video
     *
     * @param string $type
     * @param array $data
     * @return int|false
     */
    public function addVideo($type, $data)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            return false;
        }

        $foreignKey = $type . '_id';

        $sql = "INSERT INTO {$table}
                ({$foreignKey}, user_id, title, description, video_url, video_file, thumbnail, status, is_approved)
                VALUES
                (:entity_id, :user_id, :title, :description, :video_url, :video_file, :thumbnail, 'pending', 0)";

        $params = [
            'entity_id' => $data['entity_id'],
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'video_file' => $data['video_file'] ?? null,
            'thumbnail' => $data['thumbnail'] ?? null,
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Increment view count
     *
     * @param string $type
     * @param int $videoId
     * @return bool
     */
    public function incrementViews($type, $videoId)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            return false;
        }

        return $this->db->query("UPDATE {$table} SET view_count = view_count + 1 WHERE id = :id", ['id' => $videoId]);
    }

    /**
     * Get table name for entity type
     *
     * @param string $type
     * @return string|null
     */
    private function getTableName($type)
    {
        $tables = [
            'destination' => 'destination_videos',
            'hotel' => 'hotel_videos',
            'restaurant' => 'restaurant_videos',
            'event' => 'event_videos',
        ];

        return $tables[$type] ?? null;
    }
}
