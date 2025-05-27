<?php
// Database helper for common operations
require_once __DIR__ . '/../db.php';

class DatabaseHelper {
    
    public static function findById(string $table, int $id): ?array {
        $sql = "SELECT * FROM {$table} WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public static function findAll(string $table, array $filters = []): array {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            foreach ($filters as $column => $value) {
                $conditions[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = getPDO()->prepare($sql);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue(":{$param}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function insert(string $table, array $data): int {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = getPDO()->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        
        $lastId = getPDO()->lastInsertId();
        
        // Fallback if lastInsertId fails
        if (!$lastId || $lastId == 0) {
            $lastId = self::getLastInsertedId($table, $data);
        }
        
        return (int)$lastId;
    }
    
    public static function update(string $table, int $id, array $data): bool {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $setParts = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    public static function delete(string $table, int $id): bool {
        $sql = "DELETE FROM {$table} WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    private static function getLastInsertedId(string $table, array $data): int {
        // Build a query to find the record we just inserted
        $conditions = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'created_at' && $key !== 'updated_at') {
                $conditions[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
        }
        
        $sql = "SELECT id FROM {$table} WHERE " . implode(' AND ', $conditions) . " ORDER BY id DESC LIMIT 1";
        $stmt = getPDO()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['id'] : 0;
    }
    
    public static function exists(string $table, int $id): bool {
        $sql = "SELECT 1 FROM {$table} WHERE id = :id LIMIT 1";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    public static function executeQuery(string $sql, array $params = []): PDOStatement {
        $stmt = getPDO()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
