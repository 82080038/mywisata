<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Base Repository Class
 * 
 * Implements Repository Pattern for data access layer
 * Provides common CRUD operations and query building
 * 
 * @package App\Core
 */
abstract class Repository {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $modelClass;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     * 
     * @param int $id Record ID
     * @param array $columns Columns to select
     * @return array|false Record data or false if not found
     */
    public function find($id, $columns = ['*']) {
        try {
            $columnsStr = implode(', ', $columns);
            $stmt = $this->db->prepare(
                "SELECT {$columnsStr} FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1"
            );
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Repository find error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find record by column value
     * 
     * @param string $column Column name
     * @param mixed $value Column value
     * @param array $columns Columns to select
     * @return array|false Record data or false if not found
     */
    public function findBy($column, $value, $columns = ['*']) {
        try {
            $columnsStr = implode(', ', $columns);
            $stmt = $this->db->prepare(
                "SELECT {$columnsStr} FROM {$this->table} WHERE {$column} = :value LIMIT 1"
            );
            $stmt->execute(['value' => $value]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Repository findBy error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find all records
     * 
     * @param array $conditions WHERE conditions
     * @param array $columns Columns to select
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT
     * @param int $offset OFFSET
     * @return array Array of records
     */
    public function findAll($conditions = [], $columns = ['*'], $orderBy = null, $limit = null, $offset = null) {
        try {
            $columnsStr = implode(', ', $columns);
            $sql = "SELECT {$columnsStr} FROM {$this->table}";
            
            // Add WHERE conditions
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $column => $value) {
                    $whereClauses[] = "{$column} = :{$column}";
                }
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
            
            // Add ORDER BY
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            // Add LIMIT and OFFSET
            if ($limit) {
                $sql .= " LIMIT {$limit}";
                if ($offset) {
                    $sql .= " OFFSET {$offset}";
                }
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Repository findAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Create new record
     * 
     * @param array $data Record data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data) {
        try {
            $columns = array_keys($data);
            $placeholders = array_map(function($col) { return ":{$col}"; }, $columns);
            
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                    VALUES (" . implode(', ', $placeholders) . ")";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Repository create error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update record
     * 
     * @param int $id Record ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $setClauses = [];
            foreach (array_keys($data) as $column) {
                $setClauses[] = "{$column} = :{$column}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
                   " WHERE {$this->primaryKey} = :id";
            
            $data['id'] = $id;
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Repository update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete record
     * 
     * @param int $id Record ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id"
            );
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Repository delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count records
     * 
     * @param array $conditions WHERE conditions
     * @return int Number of records
     */
    public function count($conditions = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $column => $value) {
                    $whereClauses[] = "{$column} = :{$column}";
                }
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['count'];
        } catch (PDOException $e) {
            error_log("Repository count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Check if record exists
     * 
     * @param int $id Record ID
     * @return bool Existence status
     */
    public function exists($id) {
        return $this->find($id, [$this->primaryKey]) !== false;
    }
    
    /**
     * Execute custom query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    protected function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Repository query error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->db->rollBack();
    }
}
