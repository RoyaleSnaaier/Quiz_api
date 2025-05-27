<?php
// Improved database connection
require_once __DIR__ . '/config.php';

class Database {
    private static $pdo = null;
    
    public static function getInstance(): PDO {
        if (self::$pdo === null) {
            $config = Config::getDbConfig();
            
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true
            ];
            
            try {
                self::$pdo = new PDO($dsn, $config['user'], $config['password'], $options);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        
        return self::$pdo;
    }
}

// Maintain backward compatibility
function getPDO(): PDO {
    return Database::getInstance();
}
