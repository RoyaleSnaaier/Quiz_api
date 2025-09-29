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
        // GET /api/quizzes - Get all quizzes
        if ($method === 'GET' && (
            (count($segments) == 2 && $segments[1] === 'quizzes') ||
            (count($segments) == 3 && $segments[2] === 'quizzes')
        )) {
            $sql = "SELECT q.*, COUNT(qs.id) as question_count 
                    FROM quizzes q 
                    LEFT JOIN questions qs ON q.id = qs.quiz_id 
                    GROUP BY q.id 
                    ORDER BY q.created_at DESC";
            $stmt = $pdo->query($sql);
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($quizzes);
            
        // GET /api/quizzes/{id} - Get specific quiz
        } elseif ($method === 'GET' && count($segments) >= 3) {
            // Find quiz ID from segments
            $quizId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'quizzes' && isset($segments[$i + 1])) {
                    $quizId = intval($segments[$i + 1]);
                    break;
                }
            }
            
            if ($quizId <= 0) {
                throw new Exception('Invalid quiz ID');
            }
            
            $sql = "SELECT q.*, COUNT(qs.id) as question_count 
                    FROM quizzes q 
                    LEFT JOIN questions qs ON q.id = qs.quiz_id 
                    WHERE q.id = ? 
                    GROUP BY q.id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId]);
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quiz) {
                http_response_code(404);
                echo json_encode(['error' => 'Quiz not found']);
                exit;
            }
            
            // Get questions for this quiz
            $questionSql = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id";
            $questionStmt = $pdo->prepare($questionSql);
            $questionStmt->execute([$quizId]);
            $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $quiz['questions'] = $questions;
            echo json_encode($quiz);
            
        // POST /api/quizzes - Create new quiz
        } elseif ($method === 'POST' && (
            (count($segments) == 2 && $segments[1] === 'quizzes') ||
            (count($segments) == 3 && $segments[2] === 'quizzes')
        )) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload');
            }
            
            if (!isset($data['name']) || empty($data['name'])) {
                throw new Exception('Quiz name is required');
            }
            
            if (!isset($data['description'])) {
                $data['description'] = '';
            }
            
            $sql = "INSERT INTO quizzes (name, description, created_at) VALUES (?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['description']]);
            
            $quizId = $pdo->lastInsertId();
            echo json_encode([
                'message' => 'Quiz created successfully',
                'quiz_id' => $quizId
            ]);
            
        // PUT /api/quizzes/{id} - Update quiz
        } elseif ($method === 'PUT' && count($segments) >= 3) {
            // Find quiz ID from segments
            $quizId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'quizzes' && isset($segments[$i + 1])) {
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
            
            if (!isset($data['name']) || empty($data['name'])) {
                throw new Exception('Quiz name is required');
            }
            
            if (!isset($data['description'])) {
                $data['description'] = '';
            }
            
            $sql = "UPDATE quizzes SET name = ?, description = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['name'], $data['description'], $quizId]);
            
            echo json_encode(['message' => 'Quiz updated successfully']);
            
        // DELETE /api/quizzes/{id} - Delete quiz
        } elseif ($method === 'DELETE' && count($segments) >= 3) {
            // Find quiz ID from segments
            $quizId = 0;
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'quizzes' && isset($segments[$i + 1])) {
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
            
            // Delete quiz (questions will be deleted automatically due to CASCADE)
            $sql = "DELETE FROM quizzes WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId]);
            
            echo json_encode(['message' => 'Quiz deleted successfully']);
            
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
