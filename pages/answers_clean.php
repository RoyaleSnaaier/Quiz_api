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
require_once '../include/class/answer.php';

class AnswerController extends BaseController {
    
    public function __construct() {
        parent::__construct('answers', 'Answer', '../include/class/answer.php');
    }
    
    protected function getAll() {
        $filters = [];
        
        // Add question_id filter if provided
        if (isset($_GET['question_id'])) {
            $filters['question_id'] = (int)$_GET['question_id'];
        }
        
        // Add quiz_id filter if provided (join with quiz_questions table)
        if (isset($_GET['quiz_id'])) {
            // For quiz_id filter, we need a custom query since it's not directly in answers table
            $quizId = (int)$_GET['quiz_id'];
            $sql = "SELECT a.* FROM answers a 
                    JOIN quiz_questions q ON a.question_id = q.id 
                    WHERE q.quiz_id = :quiz_id";
            $stmt = DatabaseHelper::executeQuery($sql, ['quiz_id' => $quizId]);
            $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $answers = DatabaseHelper::findAll($this->table, $filters);
        }
        
        if ($answers) {
            new Response('Success', $answers);
        } else {
            new Response('No answers found', null, 404);
        }
    }
    
    protected function getById() {
        $answer = DatabaseHelper::findById($this->table, (int)$_GET['id']);
        
        if ($answer) {
            new Response('Success', $answer);
        } else {
            new Response('Answer not found', null, 404);
        }
    }
    
    protected function create() {
        $jsonData = $this->getJsonInput();
        
        try {
            // Validate required fields
            $errors = ValidationHelper::validateRequired((array)$jsonData, ['question_id', 'answer_text', 'is_correct']);
            if (!empty($errors)) {
                new Response('Validation failed', $errors, 400);
                return;
            }
            
            // Validate field lengths
            $lengthErrors = ValidationHelper::validateMaxLength((array)$jsonData, [
                'answer_text' => 500,
                'image_url' => 500
            ]);
            if (!empty($lengthErrors)) {
                new Response('Validation failed', $lengthErrors, 400);
                return;
            }
            
            // Validate question_id exists
            $question = DatabaseHelper::findById('quiz_questions', (int)$jsonData->question_id);
            if (!$question) {
                new Response('Question not found', null, 404);
                return;
            }
            
            // Validate is_correct is boolean
            if (!is_bool($jsonData->is_correct) && !in_array($jsonData->is_correct, [0, 1, '0', '1', 'true', 'false'])) {
                new Response('is_correct must be a boolean value', null, 400);
                return;
            }
              // Convert is_correct to boolean
            $isCorrect = in_array($jsonData->is_correct, [1, '1', 'true', true], true);
            
            // Sanitize input data - include quiz_id from the question
            $sanitizedData = [
                'quiz_id' => (int)$question['quiz_id'],
                'question_id' => (int)$jsonData->question_id,
                'answer_text' => ValidationHelper::sanitizeString($jsonData->answer_text),
                'is_correct' => $isCorrect ? 1 : 0,
                'image_url' => isset($jsonData->image_url) ? ValidationHelper::sanitizeUrl($jsonData->image_url) : null
            ];
              $answerId = DatabaseHelper::insert($this->table, $sanitizedData);
            
            if ($answerId) {
                $newAnswer = DatabaseHelper::findById($this->table, $answerId);
                new Response('Answer created successfully', $newAnswer, 201);
            } else {
                new Response('Failed to create answer', null, 500);
            }
            
        } catch (Exception $e) {
            error_log("Error creating answer: " . $e->getMessage());
            new Response('Error creating answer', $e->getMessage(), 500);
        }
    }
    
    protected function update() {
        $jsonData = $this->getJsonInput();
        $id = (int)$_GET['id'];
        
        try {
            // Check if answer exists
            $existingAnswer = DatabaseHelper::findById($this->table, $id);
            if (!$existingAnswer) {
                new Response('Answer not found', null, 404);
                return;
            }
            
            // Validate field lengths if provided
            $lengthErrors = ValidationHelper::validateMaxLength((array)$jsonData, [
                'answer_text' => 500,
                'image_url' => 500
            ]);
            if (!empty($lengthErrors)) {
                new Response('Validation failed', $lengthErrors, 400);
                return;
            }
            
            // If question_id is being updated, validate it exists
            if (isset($jsonData->question_id)) {
                $question = DatabaseHelper::findById('quiz_questions', (int)$jsonData->question_id);
                if (!$question) {
                    new Response('Question not found', null, 404);
                    return;
                }
            }
              // Prepare update data
            $updateData = [];
            if (isset($jsonData->question_id)) {
                $updateData['question_id'] = (int)$jsonData->question_id;
                // Also update quiz_id when question_id changes
                $updateData['quiz_id'] = (int)$question['quiz_id'];
            }
            if (isset($jsonData->answer_text)) {
                $updateData['answer_text'] = ValidationHelper::sanitizeString($jsonData->answer_text);
            }
            if (isset($jsonData->is_correct)) {
                // Validate and convert is_correct
                if (!is_bool($jsonData->is_correct) && !in_array($jsonData->is_correct, [0, 1, '0', '1', 'true', 'false'])) {
                    new Response('is_correct must be a boolean value', null, 400);
                    return;
                }
                $updateData['is_correct'] = in_array($jsonData->is_correct, [1, '1', 'true', true], true) ? 1 : 0;
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
                $updatedAnswer = DatabaseHelper::findById($this->table, $id);
                new Response('Answer updated successfully', $updatedAnswer);
            } else {
                new Response('Answer not found or no changes made', null, 404);
            }
            
        } catch (Exception $e) {
            error_log("Error updating answer: " . $e->getMessage());
            new Response('Error updating answer', $e->getMessage(), 500);
        }
    }
    
    protected function delete() {
        $id = (int)$_GET['id'];
        
        $deleted = DatabaseHelper::delete($this->table, $id);
        
        if ($deleted) {
            new Response('Answer deleted successfully', null);
        } else {
            new Response('Answer not found', null, 404);
        }
    }
}

// Initialize and handle the request
$controller = new AnswerController();
$controller->handleRequest();

// Log API usage for monitoring
$endTime = microtime(true);
$responseTime = $endTime - $startTime;
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$responseCode = http_response_code() ?: 200;

SecurityLogger::logAPIUsage($requestUri, $responseCode, $responseTime);
?>
