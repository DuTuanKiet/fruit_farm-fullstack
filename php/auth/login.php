<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// Lấy dữ liệu an toàn
$data = json_decode(file_get_contents("php://input"));
$username = $data->username ?? '';
$password = $data->password ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// Tìm người dùng bằng username hoặc email
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // password_verify để kiểm tra mật khẩu với chuỗi đã mã hóa ở CSDL
    if (password_verify($password, $user['password'])) {
        // Đăng nhập thành công, lưu thông tin vào session
         $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Lấy vai trò từ CSDL

        if ($user['role'] === 'admin') { 
            // Trả về JSON với đường dẫn chuyển hướng đến trang admin
            echo json_encode([
                'success' => true, 
                'message' => 'Admin login successful! Redirecting...', 
                'redirect' => '/fruitfarm/admin/admin_dashboard.php'
            ]);
        } else {
            // Trả về JSON với đường dẫn chuyển hướng đến trang chủ
            echo json_encode([
                'success' => true, 
                'message' => 'Login successful!', 
                'redirect' => 'index.php'
            ]);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>