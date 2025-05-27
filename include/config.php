<?php
// Configuration management
require_once __DIR__ . '/helpers/env_loader.php';

class Config {
    private static $config = null;
    
    public static function get(string $key, $default = null) {
        if (self::$config === null) {
            self::load();
        }
        
        return self::$config[$key] ?? $default;
    }
    
    private static function load() {
        // Load .env file
        EnvLoader::load(__DIR__ . '/../.env');self::$config = [
            'database' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? 3307,
                'name' => $_ENV['DB_NAME'] ?? 'quiz_database',
                'user' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? 'rootpassword',
                'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
            ],
            'api' => [
                'version' => '1.0.0',
                'name' => 'Quiz API',
                'base_url' => $_ENV['API_BASE_URL'] ?? 'http://localhost:8080',
                'enable_cors' => $_ENV['ENABLE_CORS'] ?? true,
                'debug' => $_ENV['DEBUG'] ?? true
            ],
            'validation' => [
                'max_title_length' => 255,
                'max_description_length' => 1000,
                'max_question_length' => 500,
                'max_answer_length' => 500,
                'max_url_length' => 500,
                'max_category_length' => 100,
                'default_time_limit' => 30,
                'max_time_limit' => 300
            ]
        ];
    }
    
    public static function getDbConfig(): array {
        return self::get('database');
    }
    
    public static function getApiConfig(): array {
        return self::get('api');
    }
    
    public static function getValidationConfig(): array {
        return self::get('validation');
    }
}
