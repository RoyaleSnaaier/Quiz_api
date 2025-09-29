<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    echo json_encode([
        'status' => 'OK',
        'message' => 'Quiz API is running',
        'timestamp' => date('c'),
        'environment' => $_ENV['ENVIRONMENT'] ?? 'development'
    ]);
?>