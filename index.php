<?php
header('Content-Type: application/json');

echo json_encode([
    'message' => 'Welcome to Quiz API',
    'version' => '1.0.0',
    'base_url' => 'http://localhost',
    'endpoints' => [
        'health' => [
            'url' => '/api/health',
            'method' => 'GET',
            'description' => 'Health check endpoint'
        ],
        'quizzes' => [
            'list_all' => [
                'url' => '/api/endpoints/quizzes.php',
                'method' => 'GET',
                'description' => 'Get all quizzes'
            ]
        ],
        'questions' => [
            'get_by_quiz' => [
                'url' => '/api/endpoints/quizzes/{quizId}/questions',
                'method' => 'GET',
                'description' => 'Get all questions for a specific quiz'
            ],
            'get_single' => [
                'url' => '/api/questions/{id}',
                'method' => 'GET',
                'description' => 'Get a specific question by ID'
            ],
            'create' => [
                'url' => '/api/quizzes/{quizId}/questions',
                'method' => 'POST',
                'description' => 'Create a new question for a quiz',
                'body' => ['question_text' => 'string']
            ],
            'update' => [
                'url' => '/api/questions/{id}',
                'method' => 'PUT',
                'description' => 'Update an existing question',
                'body' => ['question_text' => 'string']
            ],
            'delete' => [
                'url' => '/api/questions/{id}',
                'method' => 'DELETE',
                'description' => 'Delete a question'
            ]
        ]
    ],
    'notes' => [
        'All endpoints return JSON responses',
        'Questions endpoint uses REST-style routing via /api/endpoints/questions.php',
        'Replace {quizId} and {id} with actual numeric values',
        'POST/PUT requests require JSON body with Content-Type: application/json'
    ]
]);
?>