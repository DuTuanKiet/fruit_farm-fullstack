<?php
// Bật session nếu chưa bật
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config chung
require_once(__DIR__ . '/../core/config.php');

// 🔒 Kiểm tra quyền admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "backend/auth/login.php");
    exit();
}
?>
