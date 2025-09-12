<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Load configuration with absolute paths
    require_once '/var/www/html/config/database.php';
    require_once '/var/www/html/config/cors.php';

    // Simple routing
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);

    // Handle both direct access and rewrite access
    if (strpos($path, '/api/index.php') !== false) {
        // Direct access to index.php, use query string
        $path = $_GET['path'] ?? '/';
    } else {
        // Rewrite access, remove /api prefix
        $path = str_replace('/api', '', $path);
    }

    $method = $_SERVER['REQUEST_METHOD'];

    // Debug logging
    error_log("Request URI: " . $request_uri);
    error_log("Path after processing: " . $path);
    error_log("Method: " . $method);

    // Basic routing
    switch ($path) {
        case '/health':
            echo json_encode([
                'status' => 'OK',
                'message' => 'Quiz API is running',
                'timestamp' => date('c'),
                'environment' => $_ENV['ENVIRONMENT'] ?? 'development'
            ]);
            break;
            
        case '/v1/quizzes':
            if ($method === 'GET') {
                echo json_encode([
                    'message' => 'PHP Quiz API endpoint - Coming soon!',
                    'data' => []
                ]);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'error' => 'Not Found',
                'message' => 'The requested resource was not found'
            ]);
            break;
    }
?>