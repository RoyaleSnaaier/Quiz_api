<?php
// Simple .env file loader
class EnvLoader {
    public static function load(string $filePath): void {
        if (!file_exists($filePath)) {
            return; // Silently fail if .env doesn't exist
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.+)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Set environment variable if not already set
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}
?>
