<?php
// backend/auth/logout.php
require_once(__DIR__ . '/../core/config.php');

session_start();

// 🔹 Xóa session
$_SESSION = [];

// 🔹 Xóa cookie session nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 🔹 Hủy session
session_destroy();

// 🔹 Chuyển hướng về public index
header("Location: " . BASE_URL . "public/index.php");
exit();

