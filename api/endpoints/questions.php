<?php
    require_once("../../config/db.php");
    header('Content-Type: application/json');

    $sql = "SELECT * FROM questions";
    $stmt = $pdo->query($sql);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($questions);
?>