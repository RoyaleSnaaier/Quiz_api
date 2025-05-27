<?php
header('Content-Type: application/json');

$api_info = [
    "name" => "Quiz API",
    "version" => "1.0.0",
    "description" => "Complete Quiz Management API with CRUD operations for quizzes, questions, and answers",
    "features" => [
        "Quiz management with categories and tags",
        "Questions with time limits and image support", 
        "Multiple choice answers with image support",
        "Complete quiz retrieval with all questions and answers",
        "Image support for quizzes, questions, and answers",
        "Per-question time limits",
        "Category-based quiz filtering"
    ],
    "endpoints" => [
        "quizzes" => [
            "description" => "Manage quizzes",
            "methods" => [
                "GET /quizzes" => "Get all quizzes (optional ?category= filter)",
                "GET /quizzes/{id}" => "Get specific quiz",
                "POST /quizzes" => "Create new quiz",
                "PUT /quizzes/{id}" => "Update quiz", 
                "DELETE /quizzes/{id}" => "Delete quiz"
            ],
            "fields" => [
                "title" => "string (required)",
                "description" => "string (required)",
                "category" => "string (optional)",
                "tags" => "string (optional, comma-separated)",
                "imageUrl" => "string (optional)"
            ]
        ],
        "quiz_questions" => [
            "description" => "Manage quiz questions",
            "methods" => [
                "GET /quiz_questions" => "Get all questions (optional ?quiz_id= filter)",
                "GET /quiz_questions/{id}" => "Get specific question",
                "POST /quiz_questions" => "Create new question",
                "PUT /quiz_questions/{id}" => "Update question",
                "DELETE /quiz_questions/{id}" => "Delete question"
            ],
            "fields" => [
                "quiz_id" => "integer (required)",
                "question_text" => "string (required)",
                "question_type" => "string (required: multiple_choice, true_false, text)",
                "time_limit" => "integer (seconds, optional, default 30)",
                "imageUrl" => "string (optional)"
            ]
        ],
        "answers" => [
            "description" => "Manage question answers",
            "methods" => [
                "GET /answers" => "Get all answers (optional ?question_id= filter)",
                "GET /answers/{id}" => "Get specific answer",
                "POST /answers" => "Create new answer",
                "PUT /answers/{id}" => "Update answer",
                "DELETE /answers/{id}" => "Delete answer"
            ],
            "fields" => [
                "question_id" => "integer (required)",
                "answer_text" => "string (required)",
                "is_correct" => "boolean (required)",
                "imageUrl" => "string (optional)"
            ]
        ],
        "quiz_complete" => [
            "description" => "Get complete quiz with all questions and answers",
            "methods" => [
                "GET /quiz_complete/{id}" => "Get quiz with all related data"
            ]
        ],
        "db_health" => [
            "description" => "Check database connection status",
            "methods" => [
                "GET /db_health" => "Returns database health status"
            ]
        ]
    ],
    "database_schema" => [
        "quizes" => [
            "id" => "Primary key",
            "title" => "Quiz title",
            "description" => "Quiz description", 
            "category" => "Quiz category",
            "tags" => "Comma-separated tags",
            "image_url" => "Quiz image URL",
            "created_at" => "Creation timestamp",
            "updated_at" => "Last update timestamp"
        ],
        "quiz_questions" => [
            "id" => "Primary key",
            "quiz_id" => "Foreign key to quizes.id",
            "question_text" => "The question text",
            "question_type" => "Type: multiple_choice, true_false, text",
            "time_limit" => "Time limit in seconds",
            "image_url" => "Question image URL",
            "created_at" => "Creation timestamp",
            "updated_at" => "Last update timestamp"
        ],
        "answers" => [
            "id" => "Primary key", 
            "question_id" => "Foreign key to quiz_questions.id",
            "answer_text" => "The answer text",
            "is_correct" => "Whether this is the correct answer",
            "image_url" => "Answer image URL",
            "created_at" => "Creation timestamp",
            "updated_at" => "Last update timestamp"
        ]
    ],    "example_usage" => [
        "Create Quiz" => [
            "method" => "POST",
            "url" => "/quizzes",
            "body" => [
                "title" => "Science Quiz",
                "description" => "Test your science knowledge",
                "category" => "Science",
                "tags" => "physics,chemistry,biology"
            ]
        ],
        "Create Question" => [
            "method" => "POST", 
            "url" => "/quiz_questions",
            "body" => [
                "quiz_id" => 1,
                "question_text" => "What is the chemical symbol for water?",
                "question_type" => "multiple_choice",
                "time_limit" => 45
            ]
        ],
        "Create Answer" => [
            "method" => "POST",
            "url" => "/answers", 
            "body" => [
                "quiz_id" => 1,
                "question_id" => 1,
                "answer_text" => "H2O",
                "is_correct" => true
            ]
        ],
        "Get Quiz with All Data" => [
            "method" => "GET",
            "url" => "/quiz_complete/1",
            "description" => "Returns quiz with all questions and answers"
        ],
        "Filter Quizzes by Category" => [
            "method" => "GET",
            "url" => "/quizzes?category=Science",
            "description" => "Returns only Science category quizzes"
        ]
    ],
    "sample_data" => [
        "total_quizzes" => 5,
        "total_questions" => 16,
        "total_answers" => 64,
        "categories" => ["General", "Science", "History", "Programming", "Geography"],
        "description" => "Sample data includes 5 complete quizzes with multiple choice questions covering various topics",
        "load_command" => "Run sample_data.sql to load test data into your database"
    ],
    "testing_endpoints" => [
        "List All Quizzes" => "GET http://localhost:8080/quizzes",
        "Get Specific Quiz" => "GET http://localhost:8080/quizzes/1",
        "Get Complete Quiz" => "GET http://localhost:8080/quiz_complete/1",
        "Filter by Category" => "GET http://localhost:8080/quizzes?category=Science",
        "Get Quiz Questions" => "GET http://localhost:8080/quiz_questions?quiz_id=1",
        "Get Question Answers" => "GET http://localhost:8080/answers?question_id=1",
        "Database Health" => "GET http://localhost:8080/db_health"
    ],
    "docker_commands" => [
        "Start API" => "docker-compose up -d",
        "Stop API" => "docker-compose down",
        "View logs" => "docker-compose logs -f",
        "Access phpMyAdmin" => "http://localhost:8080"
    ],    "base_url" => "http://localhost:8080",
    "database" => [
        "host" => "mysql",
        "port" => 3306,
        "database" => "quiz_db",
        "phpMyAdmin" => "http://localhost:8081"
    ]
];

echo json_encode($api_info, JSON_PRETTY_PRINT);
?>