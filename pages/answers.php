<?php

// ======================================
// DEPRECATED ENDPOINT WARNING
// ======================================
// This endpoint is deprecated and will be removed in a future version.
// Please migrate to the new clean architecture endpoints:
// - Use /answers instead of this legacy endpoint
// - Or use /answers-clean for explicit clean architecture access
// ======================================

// Send deprecation warning header
header('X-API-Deprecation-Warning: This endpoint is deprecated. Use /answers or /answers-clean instead.');
header('X-API-Migration-Guide: https://api-docs.example.com/migration-guide');

require_once '../include/class/response.php';
require_once '../include/db.php';
require_once '../include/class/answer.php';

if (isset($_GET['id'])) {

    // Update answer
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {   

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }

        try {
            $answer = new Answer (
                id: $_GET['id'],
                quizId: $jsonData->quizId ?? -1,
                questionId: $jsonData->questionId ?? -1,
                answerText: $jsonData->answerText ?? '',
                isCorrect: $jsonData->isCorrect ?? false
            );

            $quizId = $answer->getQuizId();
            $questionId = $answer->getQuestionId();
            $answerText = $answer->getAnswerText();
            $isCorrect = $answer->getIsCorrect();
            $answerId = $answer->getId();

            $sql = "UPDATE answers SET quiz_id = :quiz_id, question_id = :question_id, answer_text = :answer_text, is_correct = :is_correct, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmt->bindParam(':answer_text', $answerText);
            $stmt->bindParam(':is_correct', $isCorrect, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $answerId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Answer updated successfully', $answer->toArray());
            } else {
                new Response('No answer found with the given ID', null, 404);
            }

        } catch(AnswerException $exception) {
            new Response('Error creating answer object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $answerId = $_GET['id'];
            $sql = "DELETE FROM answers WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':id', $answerId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Answer deleted successfully', null);
            } else {
                new Response('No answer found with the given ID', null, 404);
            }
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get answer by id
        $answerId = $_GET['id'];
        $sql = "SELECT * FROM answers WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $answerId, PDO::PARAM_INT);
        $stmt->execute();
        $answer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($answer) {
            new Response('Success', $answer);
        } else {
            new Response('Answer not found', null, 404);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        new Response('Method not allowed for this endpoint. Use POST without ID for creation.', null, 405);
    } else {
        new Response('Method not allowed', null, 405);
    }

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // Check if filtering by question_id or quiz_id
            if (isset($_GET['question_id'])) {
                $sql = "SELECT * FROM answers WHERE question_id = :question_id";
                $stmt = getPDO()->prepare($sql);
                $stmt->bindParam(':question_id', $_GET['question_id'], PDO::PARAM_INT);
            } else if (isset($_GET['quiz_id'])) {
                $sql = "SELECT * FROM answers WHERE quiz_id = :quiz_id";
                $stmt = getPDO()->prepare($sql);
                $stmt->bindParam(':quiz_id', $_GET['quiz_id'], PDO::PARAM_INT);
            } else {
                $sql = "SELECT * FROM answers";
                $stmt = getPDO()->prepare($sql);
            }
            
            $stmt->execute();
            $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($answers) {
                new Response('Success', $answers);
            } else {
                new Response('No answers found', null, 404);
            }

        } catch (PDOException $e) {
            new Response('Error', $e->getMessage(), 500);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }

        try {
            $answer = new Answer(
                id: -1, // New answer, so no ID yet
                quizId: $jsonData->quizId ?? -1,
                questionId: $jsonData->questionId ?? -1,
                answerText: $jsonData->answerText ?? '',
                isCorrect: $jsonData->isCorrect ?? false
            );

            $quizId = $answer->getQuizId();
            $questionId = $answer->getQuestionId();
            $answerText = $answer->getAnswerText();
            $isCorrect = $answer->getIsCorrect();

            $sql = "INSERT INTO answers (quiz_id, question_id, answer_text, is_correct) VALUES (:quiz_id, :question_id, :answer_text, :is_correct)";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
            $stmt->bindParam(':answer_text', $answerText);
            $stmt->bindParam(':is_correct', $isCorrect, PDO::PARAM_BOOL);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Get the ID of the newly created answer
                $newAnswerId = getPDO()->lastInsertId();
                
                // If lastInsertId fails, get the ID from the database
                if (!$newAnswerId || $newAnswerId == 0) {
                    $idStmt = getPDO()->prepare("SELECT id FROM answers WHERE quiz_id = :quiz_id AND question_id = :question_id AND answer_text = :answer_text ORDER BY id DESC LIMIT 1");
                    $idStmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
                    $idStmt->bindParam(':question_id', $questionId, PDO::PARAM_INT);
                    $idStmt->bindParam(':answer_text', $answerText);
                    $idStmt->execute();
                    $result = $idStmt->fetch(PDO::FETCH_ASSOC);
                    $newAnswerId = $result['id'] ?? 0;
                }
                
                $answer->setId(intval($newAnswerId));
                new Response('Answer created successfully', $answer->toArray());
            } else {
                new Response('Failed to create answer', null, 500);
            }

        } catch (AnswerException $exception) {
            new Response('Error creating answer object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else {
        new Response('Method not allowed', null, 405);
    }
    
}
