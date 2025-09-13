<?php
// Restart lại trang FE sẽ gọi trang này giản là kiểm tra xem 
// $_SESSION['username'] có tồn tại không và trả về tên người dùng
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['username'])) {
    echo json_encode(['username' => $_SESSION['username']]);
} else {
    echo json_encode(['username' => null]);
}
?>