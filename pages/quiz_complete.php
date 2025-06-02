<?php
// Get complete quiz with questions and answers
require_once '../include/class/response.php';
require_once '../include/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $quizId = $_GET['id'];
        
        // Get quiz details
        $quizStmt = getPDO()->prepare("SELECT * FROM quizes WHERE id = :id");
        $quizStmt->bindParam(':id', $quizId, PDO::PARAM_INT);
        $quizStmt->execute();
        $quiz = $quizStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$quiz) {
            new Response('Quiz not found', null, 404);
        }
          // Get questions with answers
        $questionsStmt = getPDO()->prepare("
            SELECT q.id, q.quiz_id, q.question_text, q.question_type, q.time_limit, q.image_url, q.created_at, q.updated_at,
                   a.id as answer_id, 
                   a.answer_text, 
                   a.is_correct,
                   a.image_url as answer_image_url,
                   a.created_at as answer_created_at,
                   a.updated_at as answer_updated_at
            FROM quiz_questions q 
            LEFT JOIN answers a ON q.id = a.question_id 
            WHERE q.quiz_id = :quiz_id 
            ORDER BY q.id, a.id
        ");
        $questionsStmt->bindParam(':quiz_id', $quizId, PDO::PARAM_INT);
        $questionsStmt->execute();
        $results = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group answers by question
        $questions = [];
        foreach ($results as $row) {
            if (!isset($questions[$row['id']])) {
                $questions[$row['id']] = [
                    'id' => $row['id'],
                    'quiz_id' => $row['quiz_id'],
                    'question_text' => $row['question_text'],
                    'question_type' => $row['question_type'],
                    'time_limit' => $row['time_limit'],
                    'image_url' => $row['image_url'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'answers' => []
                ];
            }
            
            if ($row['answer_id']) {
                $questions[$row['id']]['answers'][] = [
                    'id' => $row['answer_id'],
                    'quiz_id' => $row['quiz_id'],
                    'question_id' => $row['id'],
                    'answer_text' => $row['answer_text'],
                    'is_correct' => (bool)$row['is_correct'],
                    'image_url' => $row['answer_image_url'],
                    'created_at' => $row['answer_created_at'],
                    'updated_at' => $row['answer_updated_at']
                ];
            }
        }
        
        $quiz['questions'] = array_values($questions);
        
        new Response('Success', $quiz);
        
    } catch (PDOException $e) {
        new Response('Error', $e->getMessage(), 500);
    }
} else {
    new Response('Method not allowed', null, 405);
}
