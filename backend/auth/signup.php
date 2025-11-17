<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra kết nối DB
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Lấy dữ liệu JSON từ AJAX hoặc fallback $_POST
$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input);

$username = trim($data->username ?? '');
$email = trim($data->email ?? '');
$password = trim($data->password ?? '');
$confirm_password = trim($data->confirm_password ?? '');

// Debug
file_put_contents(__DIR__.'/debug_signup.txt', print_r($data, true)."\n", FILE_APPEND);

// Kiểm tra trống
if (!$username || !$email || !$password || !$confirm_password) {
    echo json_encode(['success'=>false,'message'=>'Vui lòng điền đầy đủ các trường bắt buộc.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Xác nhận mật khẩu không khớp.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== Kiểm tra username/email tồn tại =====
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt_check->bind_param("ss", $username, $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    echo json_encode([
        'success' => false,
        'message' => 'Tên đăng nhập hoặc email đã tồn tại.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt_check->close();

// ===== Thêm user mới =====
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'user';
$status = 'active';
$is_active = 1;

$stmt_insert = $conn->prepare("
    INSERT INTO users (username, email, password, role, status, is_active)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt_insert->bind_param("sssssi", $username, $email, $hashed_password, $role, $status, $is_active);

if ($stmt_insert->execute()) {
    $stmt_insert->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'message' => 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.'
    ], JSON_UNESCAPED_UNICODE);
} else {
    $error = $stmt_insert->error;  
    $stmt_insert->close();
    $conn->close();
    echo json_encode([
        'success' => false,
        'message' => 'Không đăng ký được: ' . $error
    ], JSON_UNESCAPED_UNICODE);
}

?>
