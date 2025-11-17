<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');

// Kiểm tra kết nối DB
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Đọc JSON 1 lần
$raw_input = file_get_contents("php://input");
file_put_contents(__DIR__ . "/debug.txt", $raw_input . "\n", FILE_APPEND);

$data = json_decode($raw_input);
$username = trim($data->username ?? '');
$password = trim($data->password ?? '');
$redirect_url = $data->redirect_url ?? (BASE_URL . 'index.php');

// Kiểm tra input
if ($username === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng điền tên đăng nhập và mật khẩu.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Truy vấn user bằng username hoặc email
$stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra user tồn tại và mật khẩu đúng
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        // Kiểm tra trạng thái tài khoản
        if ($user['status'] === 'disabled') {
            echo json_encode([
                'success' => false,
                'message' => 'Tài khoản này đang bị vô hiệu hóa. Vui lòng liên hệ admin.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Tạo session cho tài khoản hợp lệ
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Trả JSON để frontend chuyển hướng
        $redirect = ($user['role'] === 'admin') 
            ? BASE_URL . 'public/fe/admin_dashboard.php' 
            : $redirect_url;

        $msg = ($user['role'] === 'admin') 
            ? 'Đăng nhập Admin thành công! Đang chuyển hướng...' 
            : 'Đăng nhập thành công!';

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'redirect' => $redirect
        ], JSON_UNESCAPED_UNICODE);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mật khẩu không chính xác.'
        ], JSON_UNESCAPED_UNICODE);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Tài khoản không tồn tại.'
    ], JSON_UNESCAPED_UNICODE);
}

// Dọn tài nguyên
$stmt->close();
$conn->close();
?>
