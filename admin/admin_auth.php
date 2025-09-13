<?php
// admin/admin_auth.php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa và có phải là admin không
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    // Nếu không phải, chuyển hướng về trang đăng nhập
    header("location: /fruitfarm/php/login.php");
}

// Gọi file kết nối CSDL để các trang khác có thể dùng
require_once '../php/db_connect.php';
?>