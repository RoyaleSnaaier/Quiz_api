<?php

// ======================================
// DEPRECATED ENDPOINT WARNING
// ======================================
// This endpoint is deprecated and will be removed in a future version.
// Please migrate to the new clean architecture endpoints:
// - Use /quizzes instead of this legacy endpoint
// - Or use /quizzes-clean for explicit clean architecture access
// ======================================

// Send deprecation warning header
header('X-API-Deprecation-Warning: This endpoint is deprecated. Use /quizzes or /quizzes-clean instead.');
header('X-API-Migration-Guide: https://api-docs.example.com/migration-guide');

require_once '../include/class/response.php';
require_once '../include/db.php';
require_once '../include/class/quiz.php';

if (isset($_GET['id'])) {

    // Update quiz
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {   

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }        try {
            $quiz = new Quiz (
                id: $_GET['id'],
                title: $jsonData->title ?? '',
                description: $jsonData->description ?? '',
                category: $jsonData->category ?? null,
                tags: $jsonData->tags ?? null,
                imageUrl: $jsonData->imageUrl ?? null
            );            $sql = "UPDATE quizes SET title = :title, description = :description, category = :category, tags = :tags, image_url = :image_url, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            
            $title = $quiz->getTitle();
            $description = $quiz->getDescription();
            $category = $quiz->getCategory();
            $tags = $quiz->getTags();
            $imageUrl = $quiz->getImageUrl();
            $id = $quiz->getId();
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':tags', $tags);
            $stmt->bindParam(':image_url', $imageUrl);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
        new Response('Method not allowed for this endpoint. Use POST without ID for creation.', null, 405);
    } else {
        new Response('Method not allowed', null, 405);
    }

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            // Add filtering by category
            if (isset($_GET['category'])) {
                $sql = "SELECT * FROM quizes WHERE category = :category";
                $stmt = getPDO()->prepare($sql);
                $stmt->bindParam(':category', $_GET['category']);
            } else {
                $sql = "SELECT * FROM quizes";
                $stmt = getPDO()->prepare($sql);
            }
            
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
                id: -1,
                title: $jsonData->title ?? '',
                description: $jsonData->description ?? '',
                category: $jsonData->category ?? null,
                tags: $jsonData->tags ?? null,
                imageUrl: $jsonData->imageUrl ?? null
            );

            $title = $quiz->getTitle();
            $description = $quiz->getDescription();
            $category = $quiz->getCategory();
            $tags = $quiz->getTags();
            $imageUrl = $quiz->getImageUrl();

            $sql = "INSERT INTO quizes (title, description, category, tags, image_url) VALUES (:title, :description, :category, :tags, :image_url)";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':tags', $tags);
            $stmt->bindParam(':image_url', $imageUrl);            $stmt->execute();

            // Get the ID of the newly created quiz
            $newQuizId = getPDO()->lastInsertId();
            
            // If lastInsertId fails, query the database for the newest quiz
            if (!$newQuizId || $newQuizId == 0) {
                $idStmt = getPDO()->prepare("SELECT id FROM quizes WHERE title = :title AND description = :description ORDER BY id DESC LIMIT 1");
                $idStmt->bindParam(':title', $title);
                $idStmt->bindParam(':description', $description);
                $idStmt->execute();
                $result = $idStmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $newQuizId = $result['id'];
                }
            }
            
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


