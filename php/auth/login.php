<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../db_connect.php'; 

header('Content-Type: application/json');

// Lấy dữ liệu an toàn
$data = json_decode(file_get_contents("php://input"));
$username = $data->username ?? '';
$password = $data->password ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền tên đăng nhập và mật khẩu.']);
    exit;
}

// Tìm người dùng bằng username hoặc email
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // password_verify để kiểm tra mật khẩu
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công, lưu thông tin vào session
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; 

        if ($user['role'] === 'admin') { 
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng nhập Admin thành công! Đang chuyển hướng...', 
                'redirect' => '/fruitfarm/admin/admin_dashboard.php' // Trỏ đến trang admin
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng nhập thành công!', 
                'redirect' => 'index.php' // Trở về trang chủ
            ]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu không chính xác.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tài khoản không tồn tại.']);
}

$stmt->close();
$conn->close();
?>