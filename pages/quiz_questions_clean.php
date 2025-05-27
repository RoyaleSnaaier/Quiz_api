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
require_once '../include/class/quiz_question.php';

class QuizQuestionController extends BaseController {
    
    public function __construct() {
        parent::__construct('quiz_questions', 'QuizQuestion', '../include/class/quiz_question.php');
    }
    
    protected function getAll() {
        $filters = [];
        
        // Add quiz_id filter if provided
        if (isset($_GET['quiz_id'])) {
            $filters['quiz_id'] = (int)$_GET['quiz_id'];
        }
        
        $questions = DatabaseHelper::findAll($this->table, $filters);
        
        if ($questions) {
            new Response('Success', $questions);
        } else {
            new Response('No questions found', null, 404);
        }
    }
    
    protected function getById() {
        $question = DatabaseHelper::findById($this->table, (int)$_GET['id']);
        
        if ($question) {
            new Response('Success', $question);
        } else {
            new Response('Question not found', null, 404);
        }
    }
    
    protected function create() {
        $jsonData = $this->getJsonInput();
        
        try {
            // Validate required fields
            $errors = ValidationHelper::validateRequired((array)$jsonData, ['quiz_id', 'question_text']);
            if (!empty($errors)) {
                new Response('Validation failed', $errors, 400);
                return;
            }
            
            // Validate field lengths
            $lengthErrors = ValidationHelper::validateMaxLength((array)$jsonData, [
                'question_text' => 500,
                'question_type' => 50,
                'image_url' => 500
            ]);
            if (!empty($lengthErrors)) {
                new Response('Validation failed', $lengthErrors, 400);
                return;
            }
            
            // Validate quiz_id exists
            $quiz = DatabaseHelper::findById('quizes', (int)$jsonData->quiz_id);
            if (!$quiz) {
                new Response('Quiz not found', null, 404);
                return;
            }
            
            // Sanitize input data
            $sanitizedData = [
                'quiz_id' => (int)$jsonData->quiz_id,
                'question_text' => ValidationHelper::sanitizeString($jsonData->question_text),
                'question_type' => ValidationHelper::sanitizeString($jsonData->question_type ?? 'multiple_choice'),
                'time_limit' => isset($jsonData->time_limit) ? (int)$jsonData->time_limit : 30,
                'image_url' => isset($jsonData->image_url) ? ValidationHelper::sanitizeUrl($jsonData->image_url) : null
            ];
              $questionId = DatabaseHelper::insert($this->table, $sanitizedData);
            
            if ($questionId) {
                $newQuestion = DatabaseHelper::findById($this->table, $questionId);
                new Response('Question created successfully', $newQuestion, 201);
            } else {
                new Response('Failed to create question', null, 500);
            }
            
        } catch (Exception $e) {
            error_log("Error creating question: " . $e->getMessage());
            new Response('Error creating question', $e->getMessage(), 500);
        }
    }
    
    protected function update() {
        $jsonData = $this->getJsonInput();
        $id = (int)$_GET['id'];
        
        try {
            // Check if question exists
            $existingQuestion = DatabaseHelper::findById($this->table, $id);
            if (!$existingQuestion) {
                new Response('Question not found', null, 404);
                return;
            }
            
            // Validate field lengths if provided
            $lengthErrors = ValidationHelper::validateMaxLength((array)$jsonData, [
                'question_text' => 500,
                'question_type' => 50,
                'image_url' => 500
            ]);
            if (!empty($lengthErrors)) {
                new Response('Validation failed', $lengthErrors, 400);
                return;
            }
            
            // If quiz_id is being updated, validate it exists
            if (isset($jsonData->quiz_id)) {
                $quiz = DatabaseHelper::findById('quizes', (int)$jsonData->quiz_id);
                if (!$quiz) {
                    new Response('Quiz not found', null, 404);
                    return;
                }
            }
            
            // Prepare update data
            $updateData = [];
            if (isset($jsonData->quiz_id)) {
                $updateData['quiz_id'] = (int)$jsonData->quiz_id;
            }
            if (isset($jsonData->question_text)) {
                $updateData['question_text'] = ValidationHelper::sanitizeString($jsonData->question_text);
            }
            if (isset($jsonData->question_type)) {
                $updateData['question_type'] = ValidationHelper::sanitizeString($jsonData->question_type);
            }
            if (isset($jsonData->time_limit)) {
                $updateData['time_limit'] = (int)$jsonData->time_limit;
            }
            if (isset($jsonData->image_url)) {
                $updateData['image_url'] = ValidationHelper::sanitizeUrl($jsonData->image_url);
            }
            
            if (empty($updateData)) {
                new Response('No valid fields to update', null, 400);
                return;
            }
            
            $updated = DatabaseHelper::update($this->table, $id, $updateData);
            
            if ($updated) {
                $updatedQuestion = DatabaseHelper::findById($this->table, $id);
                new Response('Question updated successfully', $updatedQuestion);
            } else {
                new Response('Question not found or no changes made', null, 404);
            }
            
        } catch (Exception $e) {
            error_log("Error updating question: " . $e->getMessage());
            new Response('Error updating question', $e->getMessage(), 500);
        }
    }
    
    protected function delete() {
        $id = (int)$_GET['id'];
        
        // Check if question has answers and warn/prevent deletion
        $answers = DatabaseHelper::findAll('answers', ['question_id' => $id]);
        if (!empty($answers)) {
            new Response('Cannot delete question with existing answers. Delete answers first.', null, 400);
            return;
        }
        
        $deleted = DatabaseHelper::delete($this->table, $id);
        
        if ($deleted) {
            new Response('Question deleted successfully', null);
        } else {
            new Response('Question not found', null, 404);
        }
    }
}

// Initialize and handle the request
$controller = new QuizQuestionController();
$controller->handleRequest();

// Log API usage for monitoring
$endTime = microtime(true);
$responseTime = $endTime - $startTime;
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$responseCode = http_response_code() ?: 200;

SecurityLogger::logAPIUsage($requestUri, $responseCode, $responseTime);
?>
