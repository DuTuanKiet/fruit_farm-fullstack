<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once(__DIR__ . '/../core/config.php');

header('Content-Type: application/json; charset=utf-8');

// Lấy dữ liệu JSON từ frontend
$data = json_decode(file_get_contents("php://input"));
$username = trim($data->username ?? '');
$password = trim($data->password ?? '');
$redirect_url = $data->redirect_url ?? (BASE_URL . 'index.php');

// Kiểm tra đầu vào
if ($username === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng điền tên đăng nhập và mật khẩu.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 🔎 Truy vấn tìm user bằng username hoặc email
$stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Kiểm tra user tồn tại và mật khẩu đúng
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        // --- KIỂM TRA TRẠNG THÁI TÀI KHOẢN ---
        if ($user['status'] === 'disabled') {
            echo json_encode([
                'success' => false,
                'message' => 'Tài khoản này đang bị vô hiệu hóa. Vui lòng liên hệ admin.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // --- TẠO SESSION CHO TÀI KHOẢN HỢP LỆ ---
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // --- TRẢ VỀ JSON ĐỂ CHUYỂN HƯỚNG FRONTEND ---
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
