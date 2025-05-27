<?php
require_once '../include/db.php';


try {
    $pdo = getPDO();
    echo '<p style="color:green;">Database connected successfully!</p>';
} catch (PDOException $e) {
    echo '<p style="color:red;">Database connection failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>