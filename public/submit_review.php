<?php
require_once(__DIR__ . '/../backend/core/config.php');
require_once(__DIR__ . '/../backend/core/db_connect.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $name = trim($_POST['name']);
    $feedback = trim($_POST['feedback']);
    $rating = intval($_POST['rating']);

    if ($name && $feedback) {
        $stmt = $conn->prepare("INSERT INTO testimonials (user_id, name, feedback, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $name, $feedback, $rating);
        $stmt->execute();
    }

    header("Location: " . BASE_URL . "public/index.php#testimonials");
    exit();
}
?>
