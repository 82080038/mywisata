<?php
/**
 * MyWisata Application - Database Class
 * 
 * Singleton PDO database connection with prepared statements support.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Database {
    private static $instances = [];
    private $pdo;
    private $config;
    private $connectionName;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct($connectionName = 'default') {
        $this->connectionName = $connectionName;
        $this->config = require APP_ROOT . '/app/config/database.php';
        $this->connect();
    }
    
    /**
     * Get singleton instance
     * 
     * @param string $connectionName Connection name (default, address)
     * @return Database
     */
    public static function getInstance($connectionName = 'default') {
        if (!isset(self::$instances[$connectionName])) {
            self::$instances[$connectionName] = new self($connectionName);
        }
        return self::$instances[$connectionName];
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $config = $this->config[$this->connectionName] ?? $this->config['default'];
            
            $dsn = sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Set collation
            $this->pdo->exec("SET NAMES " . $config['charset'] . " COLLATE " . $config['collation']);
            
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }
    
    /**
     * Get PDO instance
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute query with prepared statement
     * 
     * @param string $sql SQL query with named parameters
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError($e->getMessage() . ' | SQL: ' . $sql . ' | Params: ' . json_encode($params));
            throw $e;
        }
    }
    
    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Log error to file
     * 
     * @param string $message Error message
     */
    private function logError($message) {
        $logFile = defined('ERROR_LOG_FILE') ? ERROR_LOG_FILE : APP_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Database Error: {$message}\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
