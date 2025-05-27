<?php

require_once '../include/class/response.php';
require_once '../include/db.php';
require_once '../include/class/quiz.php';

if (isset($_GET['id'])) {

    // Update quiz
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {   

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }
        try {
            $quiz = new Quiz (
                id: $jsonData->id ?? -1,
                title: $jsonData->title ?? '',
                description: $jsonData->description ?? ''
            );

            $sql = "UPDATE quizes SET title = :title, description = :description, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':title', $quiz->getTitle());
            $stmt->bindParam(':description', $quiz->getDescription());
            $stmt->bindParam(':id', $quiz->getId(), PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Quiz updated successfully', $quiz->toArray());
            } else {
                new Response('No quiz found with the given ID', null, 404);
            }

        } catch(QuizException $exception) {
            new Response('Error creating quiz object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $quizId = $_GET['id'];
            $sql = "DELETE FROM quizes WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':id', $quizId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Quiz deleted successfully', null);
            } else {
                new Response('No quiz found with the given ID', null, 404);
            }
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get quiz by id
        $quizId = $_GET['id'];
        $sql = "SELECT * FROM quizes WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $quizId, PDO::PARAM_INT);
        $stmt->execute();
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($quiz) {
            new Response('Success', $quiz);
        } else {
            new Response('Quiz not found', null, 404);
        }    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // This endpoint shouldn't be used for creating with a specific ID
        new Response('Method not allowed for this endpoint. Use POST without ID for creation.', null, 405);
    } else {
        new Response('Method not allowed', null, 405);
    }

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $sql = "SELECT * FROM quizes";
            $stmt = getPDO()->prepare($sql);
            $stmt->execute();
            $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($quizzes) {
                new Response('Success', $quizzes);
            } else {
                new Response('No quizzes found', null, 404);
            }

        } catch (PDOException $e) {
            new Response('Error', $e->getMessage(), 500);
        }    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }

        try {
            $quiz = new Quiz(
                id: -1, // New quiz, so no ID yet
                title: $jsonData->title ?? '',
                description: $jsonData->description ?? ''
            );

            $title = $quiz->getTitle();
            $description = $quiz->getDescription();

            $sql = "INSERT INTO quizes (title, description) VALUES (:title, :description)";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->execute();

            // Get the ID of the newly created quiz
            $newQuizId = getPDO()->lastInsertId();
            $quiz->setId(intval($newQuizId));

            if ($stmt->rowCount() > 0) {
                new Response('Quiz created successfully', $quiz->toArray());
            } else {
                new Response('Failed to create quiz', null, 500);
            }

        } catch (QuizException $exception) {
            new Response('Error creating quiz object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else {
        new Response('Method not allowed', null, 405);
    }
    
}


