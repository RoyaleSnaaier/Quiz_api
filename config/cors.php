<?php
// CORS Configuration
function setCorsHeaders() {
    $allowed_origins = explode(',', $_ENV['CORS_ORIGIN'] ?? '*');
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array('*', $allowed_origins) || in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: " . ($origin ?: '*'));
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

setCorsHeaders();
?>