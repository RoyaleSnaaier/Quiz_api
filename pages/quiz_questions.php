<?php

require_once '../include/class/response.php';
require_once '../include/db.php';
require_once '../include/class/quiz_question.php';

if (isset($_GET['id'])) {

    // Update quiz question
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {   

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }

        // Debug: Log what we received
        error_log("PUT Request - Question ID from URL: " . $_GET['id']);
        error_log("PUT Request - JSON Data: " . print_r($jsonData, true));

        try {
            $quizQuestion = new QuizQuestion (
                id: $_GET['id'],
                quizId: $jsonData->quizId ?? -1,
                question: $jsonData->question ?? $jsonData->question_text ?? ''
            );

            $quizId = $quizQuestion->getQuizId();
            $questionText = $quizQuestion->getQuestion();
            $questionId = $quizQuestion->getId();

            // Debug: Log the SQL values
            error_log("Updating question - ID: $questionId, QuizID: $quizId, Question: $questionText");

            $sql = "UPDATE quiz_questions SET quiz_id = :quiz_id, question_text = :question_text, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindParam(':question_text', $questionText);
            $stmt->bindParam(':id', $questionId, PDO::PARAM_INT);
            $stmt->execute();

            // Debug: Log row count
            error_log("Rows affected: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                new Response('Quiz question updated successfully', $quizQuestion->toArray());
            } else {
                new Response('No quiz question found with the given ID', null, 404);
            }

        } catch(QuizQuestionException $exception) {
            new Response('Error creating quiz question object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $questionId = $_GET['id'];
            $sql = "DELETE FROM quiz_questions WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':id', $questionId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Quiz question deleted successfully', null);
            } else {
                new Response('No quiz question found with the given ID', null, 404);
            }
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get quiz question by id
        $questionId = $_GET['id'];
        $sql = "SELECT * FROM quiz_questions WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $questionId, PDO::PARAM_INT);
        $stmt->execute();
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($question) {
            new Response('Success', $question);
        } else {
            new Response('Quiz question not found', null, 404);
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        new Response('Method not allowed for this endpoint. Use POST without ID for creation.', null, 405);
    } else {
        new Response('Method not allowed', null, 405);
    }

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // Check if filtering by quiz_id
            if (isset($_GET['quiz_id'])) {
                $sql = "SELECT * FROM quiz_questions WHERE quiz_id = :quiz_id";
                $stmt = getPDO()->prepare($sql);
                $stmt->bindParam(':quiz_id', $_GET['quiz_id'], PDO::PARAM_INT);
            } else {
                $sql = "SELECT * FROM quiz_questions";
                $stmt = getPDO()->prepare($sql);
            }
            
            $stmt->execute();
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($questions) {
                new Response('Success', $questions);
            } else {
                new Response('No quiz questions found', null, 404);
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
            $quizQuestion = new QuizQuestion(
                id: -1, // New question, so no ID yet
                quizId: $jsonData->quizId ?? -1,
                question: $jsonData->question ?? ''
            );

            $quizId = $quizQuestion->getQuizId();
            $question = $quizQuestion->getQuestion();

            $sql = "INSERT INTO quiz_questions (quiz_id, question_text) VALUES (:quiz_id, :question_text)";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->bindParam(':question_text', $question);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Get the ID of the newly created quiz question
                $newQuestionId = getPDO()->lastInsertId();
                
                // If lastInsertId fails, get the ID from the database
                if (!$newQuestionId || $newQuestionId == 0) {
                    $idStmt = getPDO()->prepare("SELECT id FROM quiz_questions WHERE quiz_id = :quiz_id AND question_text = :question_text ORDER BY id DESC LIMIT 1");
                    $idStmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
                    $idStmt->bindParam(':question_text', $question);
                    $idStmt->execute();
                    $result = $idStmt->fetch(PDO::FETCH_ASSOC);
                    $newQuestionId = $result['id'] ?? 0;
                }
                
                $quizQuestion->setId(intval($newQuestionId));
                new Response('Quiz question created successfully', $quizQuestion->toArray());
            } else {
                new Response('Failed to create quiz question', null, 500);
            }

        } catch (QuizQuestionException $exception) {
            new Response('Error creating quiz question object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else {
        new Response('Method not allowed', null, 405);
    }
    
}

