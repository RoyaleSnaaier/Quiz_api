<?php
// Apply security headers and rate limiting
require_once '../include/security/security_headers.php';
require_once '../include/security/security_logger.php';

// Apply security measures
SecurityHeaders::apply();
SecurityHeaders::applyRateLimit();

// Start timing for performance logging
$startTime = microtime(true);

require_once '../include/class/response.php';
require_once '../include/class/base_controller.php';
require_once '../include/helpers/database_helper.php';
require_once '../include/helpers/validation_helper.php';
require_once '../include/security/secure_validation.php';
require_once '../include/class/quiz.php';

class QuizController extends BaseController {
    
    public function __construct() {
        parent::__construct('quizes', 'Quiz', '../include/class/quiz.php');
    }
    
    protected function getAll() {
        $filters = [];
        
        // Add category filter if provided
        if (isset($_GET['category'])) {
            $filters['category'] = $_GET['category'];
        }
        
        $quizzes = DatabaseHelper::findAll($this->table, $filters);
        
        if ($quizzes) {
            new Response('Success', $quizzes);
        } else {
            new Response('No quizzes found', null, 404);
        }
    }
    
    protected function getById() {
        $quiz = DatabaseHelper::findById($this->table, (int)$_GET['id']);
        
        if ($quiz) {
            new Response('Success', $quiz);
        } else {
            new Response('Quiz not found', null, 404);
        }
    }
      protected function create() {
        $jsonData = $this->getJsonInput();
        
        try {
            // Enhanced security validation and sanitization
            $validationRules = [
                'title' => [
                    'type' => 'string',
                    'required' => true,
                    'max_length' => 255,
                    'min_length' => 1
                ],
                'description' => [
                    'type' => 'string',
                    'required' => false,
                    'max_length' => 1000
                ],
                'category' => [
                    'type' => 'string',
                    'required' => false,
                    'max_length' => 100
                ],
                'tags' => [
                    'type' => 'string',
                    'required' => false,
                    'max_length' => 500
                ],
                'imageUrl' => [
                    'type' => 'url',
                    'required' => false
                ]
            ];
            
            $sanitizedData = SecureValidationHelper::validateAndSanitize((array)$jsonData, $validationRules);
            
            // Create quiz object for validation
            $quiz = new Quiz(
                id: -1,
                title: $sanitizedData['title'],
                description: $sanitizedData['description'] ?? '',
                category: $sanitizedData['category'] ?? null,
                tags: $sanitizedData['tags'] ?? null,
                imageUrl: $sanitizedData['imageUrl'] ?? null
            );
            
            // Prepare data for database insertion
            $dbData = [
                'title' => $quiz->getTitle(),
                'description' => $quiz->getDescription(),
                'category' => $quiz->getCategory(),
                'tags' => $quiz->getTags(),
                'image_url' => $quiz->getImageUrl()
            ];
            
            $newId = DatabaseHelper::insert($this->table, $dbData);
            $quiz->setId($newId);
            
            new Response('Quiz created successfully', $quiz->toArray(), 201);
              } catch (QuizException $e) {
            new Response('Validation error', $e->getMessage(), 400);
        } catch (InvalidArgumentException $e) {
            new Response('Validation error', $e->getMessage(), 400);
        } catch (Exception $e) {
            new Response('Error creating quiz', $e->getMessage(), 500);
        }
    }
    
    protected function update() {
        $id = (int)$_GET['id'];
        $jsonData = $this->getJsonInput();
        
        try {
            // Check if quiz exists
            if (!DatabaseHelper::exists($this->table, $id)) {
                new Response('Quiz not found', null, 404);
                return;
            }
            
            // Sanitize input data
            $sanitizedData = ValidationHelper::sanitizeArray((array)$jsonData, [
                'title' => ['type' => 'string', 'max_length' => 255],
                'description' => ['type' => 'string', 'max_length' => 1000],
                'category' => ['type' => 'string', 'max_length' => 100],
                'tags' => ['type' => 'string'],
                'imageUrl' => ['type' => 'url']
            ]);
            
            // Create quiz object for validation
            $quiz = new Quiz(
                id: $id,
                title: $sanitizedData['title'] ?? '',
                description: $sanitizedData['description'] ?? '',
                category: $sanitizedData['category'] ?? null,
                tags: $sanitizedData['tags'] ?? null,
                imageUrl: $sanitizedData['imageUrl'] ?? null
            );
            
            // Prepare data for database update
            $dbData = [];
            if (isset($sanitizedData['title'])) $dbData['title'] = $quiz->getTitle();
            if (isset($sanitizedData['description'])) $dbData['description'] = $quiz->getDescription();
            if (isset($sanitizedData['category'])) $dbData['category'] = $quiz->getCategory();
            if (isset($sanitizedData['tags'])) $dbData['tags'] = $quiz->getTags();
            if (isset($sanitizedData['imageUrl'])) $dbData['image_url'] = $quiz->getImageUrl();
            
            $updated = DatabaseHelper::update($this->table, $id, $dbData);
            
            if ($updated) {
                new Response('Quiz updated successfully', $quiz->toArray());
            } else {
                new Response('No changes made', null, 400);
            }        } catch (SecurityException $e) {
            new Response('Security violation', 'Request blocked for security reasons', 403);
        } catch (QuizException $e) {
            new Response('Validation error', $e->getMessage(), 400);
        } catch (InvalidArgumentException $e) {
            new Response('Validation error', $e->getMessage(), 400);
        } catch (Exception $e) {
            SecurityLogger::logDatabaseError($e->getMessage());
            new Response('Error updating quiz', 'Internal server error', 500);
        }
    }
    
    protected function delete() {
        $id = (int)$_GET['id'];
        
        $deleted = DatabaseHelper::delete($this->table, $id);
        
        if ($deleted) {
            new Response('Quiz deleted successfully', null);
        } else {
            new Response('Quiz not found', null, 404);
        }
    }
}

// Initialize and handle the request
$controller = new QuizController();
$controller->handleRequest();

// Log API usage for monitoring
$endTime = microtime(true);
$responseTime = $endTime - $startTime;
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$responseCode = http_response_code() ?: 200;

SecurityLogger::logAPIUsage($requestUri, $responseCode, $responseTime);
