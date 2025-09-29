<?php
    require_once("../../config/db.php");
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($uri, '/'));
    
    // Also check REDIRECT_URL for rewritten requests
    if (isset($_SERVER['REDIRECT_URL'])) {
        $redirectUri = parse_url($_SERVER['REDIRECT_URL'], PHP_URL_PATH);
        $redirectSegments = explode('/', trim($redirectUri, '/'));
        if (count($redirectSegments) > count($segments)) {
            $segments = $redirectSegments;
        }
    }

    try {
        // GET /api/quizzes/{quizId}/questions
        if ($method === 'GET' && in_array('questions', $segments) && in_array('quizzes', $segments)) {
            // Find quiz ID from segments
            $quizId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'quizzes' && isset($segments[$i + 1]) && $segments[$i + 1] !== 'questions') {
                    $quizId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($quizId <= 0) {
                throw new Exception('Invalid quiz ID');
            }
            
            $sql = "SELECT q.*, qt.type FROM questions q 
                    LEFT JOIN question_type qt ON q.id = qt.question_id 
                    WHERE q.quiz_id = ? 
                    ORDER BY q.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($questions);
            
        // GET /api/questions/{id}
        } elseif ($method === 'GET' && in_array('questions', $segments)) {
            // Find question ID from segments
            $questionId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'questions' && isset($segments[$i + 1])) {
                    $questionId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($questionId <= 0) {
                throw new Exception('Invalid question ID');
            }
            
            $sql = "SELECT q.*, qt.type FROM questions q 
                    LEFT JOIN question_type qt ON q.id = qt.question_id 
                    WHERE q.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$question) {
                http_response_code(404);
                echo json_encode(['error' => 'Question not found']);
                exit;
            }
            echo json_encode($question);
            
        // POST /api/quizzes/{quizId}/questions
        } elseif ($method === 'POST' && in_array('questions', $segments) && in_array('quizzes', $segments)) {
            // Find quiz ID from segments
            $quizId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'quizzes' && isset($segments[$i + 1]) && $segments[$i + 1] !== 'questions') {
                    $quizId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($quizId <= 0) {
                throw new Exception('Invalid quiz ID');
            }
            
            // Check if quiz exists
            $checkQuiz = $pdo->prepare("SELECT id FROM quizzes WHERE id = ?");
            $checkQuiz->execute([$quizId]);
            if (!$checkQuiz->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Quiz not found']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload');
            }
            
            if (!isset($data['question']) || empty($data['question'])) {
                throw new Exception('Question text is required');
            }
            
            $sql = "INSERT INTO questions (quiz_id, question, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId, $data['question']]);
            
            $questionId = $pdo->lastInsertId();
            echo json_encode([
                'message' => 'Question added successfully',
                'question_id' => $questionId
            ]);
            
        // PUT /api/questions/{id}
        } elseif ($method === 'PUT' && in_array('questions', $segments)) {
            // Find question ID from segments
            $questionId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'questions' && isset($segments[$i + 1])) {
                    $questionId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($questionId <= 0) {
                throw new Exception('Invalid question ID');
            }
            
            // Check if question exists
            $checkQuestion = $pdo->prepare("SELECT id FROM questions WHERE id = ?");
            $checkQuestion->execute([$questionId]);
            if (!$checkQuestion->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Question not found']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload');
            }
            
            if (!isset($data['question']) || empty($data['question'])) {
                throw new Exception('Question text is required');
            }
            
            $sql = "UPDATE questions SET question = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['question'], $questionId]);
            
            echo json_encode(['message' => 'Question updated successfully']);
            
        // DELETE /api/questions/{id}
        } elseif ($method === 'DELETE' && in_array('questions', $segments)) {
            // Find question ID from segments
            $questionId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'questions' && isset($segments[$i + 1])) {
                    $questionId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($questionId <= 0) {
                throw new Exception('Invalid question ID');
            }
            
            // Check if question exists
            $checkQuestion = $pdo->prepare("SELECT id FROM questions WHERE id = ?");
            $checkQuestion->execute([$questionId]);
            if (!$checkQuestion->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Question not found']);
                exit;
            }
            
            $sql = "DELETE FROM questions WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$questionId]);
            
            echo json_encode(['message' => 'Question deleted successfully']);
            
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found', 'path' => $uri, 'method' => $method, 'segments' => $segments]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
?>