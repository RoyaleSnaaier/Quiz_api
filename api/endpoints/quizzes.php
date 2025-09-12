<?php
    require_once("../../config/db.php");
    header('Content-Type: application/json');

    $sql = "SELECT * FROM quizzes";
    $stmt = $pdo->query($sql);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($quizzes);
?>