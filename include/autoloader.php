<?php
// Simple autoloader for the Quiz API
class QuizAPIAutoloader {
    private static $paths = [
        'include/class/',
        'include/helpers/',
        'include/'
    ];
    
    public static function register() {
        spl_autoload_register([self::class, 'load']);
    }
    
    public static function load($className) {
        $className = ltrim($className, '\\');
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        
        foreach (self::$paths as $path) {
            $fullPath = __DIR__ . '/' . $path . $fileName;
            
            if (file_exists($fullPath)) {
                require_once $fullPath;
                return;
            }
            
            // Try with snake_case conversion
            $snakeCaseFileName = self::camelToSnake($className) . '.php';
            $snakeCasePath = __DIR__ . '/' . $path . $snakeCaseFileName;
            
            if (file_exists($snakeCasePath)) {
                require_once $snakeCasePath;
                return;
            }
        }
    }
    
    private static function camelToSnake($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
