<?php
session_start(); 
// Xóa tất cả dữ liệu trong session
$_SESSION = [];

// Xóa cookie session nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Xóa sạch dữ liệu có trong session
session_destroy();
header("Location: /fruitfarm/index.php");
exit();
?>