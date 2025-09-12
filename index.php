<?php
header('Content-Type: application/json');

echo json_encode([
    'message' => 'Welcome to Quiz API',
    'version' => '1.0.0',
    'endpoints' => [
        'health' => '/api/health',
        'quizzes' => '/api/v1/quizzes'
    ],
    'documentation' => 'Coming soon!'
]);
?>