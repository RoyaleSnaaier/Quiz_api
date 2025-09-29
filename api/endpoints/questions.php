<?php
    require_once("../../config/db.php");
    header('Content-Type: application/json');

    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($uri, '/'));

    try {
        if ($method === 'GET'
            && isset($segments[1], $segments[2], $segments[3])
            && $segments[1] === 'quizzes'
            && $segments[3] === 'questions'
        ) {
            // GET /api/quizzes/{quizId}/questions
            $quizId = intval($segments[2]);
            $sql = "SELECT * FROM questions WHERE quiz_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId]);
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($questions);
        } elseif ($method === 'GET'
            && isset($segments[1], $segments[2])
            && $segments[1] === 'questions'
        ) {
            // GET /api/questions/{id}
            $questionId = intval($segments[2]);
            $sql = "SELECT * FROM questions WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$questionId]);
            $question = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($question);
        } elseif ($method === 'POST'
            && isset($segments[1], $segments[2], $segments[3])
            && $segments[1] === 'quizzes'
            && $segments[3] === 'questions'
        ) {
            // POST /api/quizzes/{quizId}/questions
            $quizId = intval($segments[2]);
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload');
            }
            // Insert new question (column name 'question' in database)
            $sql = "INSERT INTO questions (quiz_id, question) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$quizId, $data['question_text']]);
            echo json_encode(['message' => 'Question added successfully']);
        } elseif ($method === 'PUT'
            && isset($segments[1], $segments[2])
            && $segments[1] === 'questions'
        ) {
            // PUT /api/questions/{id}
            $questionId = intval($segments[2]);
            $data = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload');
            }
            // Update question text (column name 'question')
            $sql = "UPDATE questions SET question = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data['question_text'], $questionId]);
            echo json_encode(['message' => 'Question updated successfully']);
        } elseif ($method === 'DELETE'
            && isset($segments[1], $segments[2])
            && $segments[1] === 'questions'
        ) {
            // DELETE /api/questions/{id}
            $questionId = intval($segments[2]);
            $sql = "DELETE FROM questions WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$questionId]);
            echo json_encode(['message' => 'Question deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Endpoint not found']);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
?>