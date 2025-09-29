<?php
header('Content-Type: application/json');

echo json_encode([
    'message' => 'Welcome to Quiz API',
    'version' => '1.0.1',
    'base_url' => 'http://localhost',
    'endpoints' => [
        'health' => [
            'url' => '/api/health',
            'method' => 'GET',
            'description' => 'Health check endpoint'
        ],
        'quizzes' => [
            'list_all' => [
                'url' => '/api/quizzes',
                'method' => 'GET',
                'description' => 'Get all quizzes with question counts',
                'response' => 'Array of quizzes with question_count field'
            ],
            'get_single' => [
                'url' => '/api/quizzes/{id}',
                'method' => 'GET',
                'description' => 'Get specific quiz with all its questions',
                'response' => 'Quiz object with questions array'
            ],
            'create' => [
                'url' => '/api/quizzes',
                'method' => 'POST',
                'description' => 'Create a new quiz',
                'body' => [
                    'name' => 'string (required)',
                    'description' => 'string (optional)'
                ]
            ],
            'update' => [
                'url' => '/api/quizzes/{id}',
                'method' => 'PUT',
                'description' => 'Update an existing quiz',
                'body' => [
                    'name' => 'string (required)',
                    'description' => 'string (optional)'
                ]
            ],
            'delete' => [
                'url' => '/api/quizzes/{id}',
                'method' => 'DELETE',
                'description' => 'Delete a quiz (also deletes all associated questions)'
            ]
        ],
        'questions' => [
            'get_by_quiz' => [
                'url' => '/api/quizzes/{quizId}/questions',
                'method' => 'GET',
                'description' => 'Get all questions for a specific quiz',
                'response' => 'Array of questions with question type'
            ],
            'get_single' => [
                'url' => '/api/questions/{id}',
                'method' => 'GET',
                'description' => 'Get a specific question by ID',
                'response' => 'Question object with type information'
            ],
            'create' => [
                'url' => '/api/quizzes/{quizId}/questions',
                'method' => 'POST',
                'description' => 'Create a new question for a quiz',
                'body' => [
                    'question' => 'string (required) - The question text'
                ]
            ],
            'update' => [
                'url' => '/api/questions/{id}',
                'method' => 'PUT',
                'description' => 'Update an existing question',
                'body' => [
                    'question' => 'string (required) - The updated question text'
                ]
            ],
            'delete' => [
                'url' => '/api/questions/{id}',
                'method' => 'DELETE',
                'description' => 'Delete a question'
            ]
        ]
    ],
    'usage_examples' => [
        'Create Quiz' => [
            'method' => 'POST',
            'url' => '/api/quizzes',
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode([
                'name' => 'My Quiz',
                'description' => 'A sample quiz about general knowledge'
            ])
        ],
        'Add Question' => [
            'method' => 'POST', 
            'url' => '/api/quizzes/1/questions',
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode([
                'question' => 'What is the capital of France?'
            ])
        ],
        'Get Quiz with Questions' => [
            'method' => 'GET',
            'url' => '/api/quizzes/1',
            'description' => 'Returns quiz details with all questions included'
        ]
    ],
    'notes' => [
        'All endpoints return JSON responses',
        'Both quizzes and questions support full CRUD operations',
        'Replace {quizId} and {id} with actual numeric values',
        'POST/PUT requests require JSON body with Content-Type: application/json',
        'Error responses include appropriate HTTP status codes',
        'Deleting a quiz automatically deletes all associated questions',
        'Questions include type information when available from question_type table'
    ],
    'error_codes' => [
        '400' => 'Bad Request - Invalid input data',
        '404' => 'Not Found - Resource does not exist',
        '500' => 'Internal Server Error - Database or server error'
    ]
]);
?>