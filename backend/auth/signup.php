<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../core/config.php');
header('Content-Type: application/json');

// Đọc dữ liệu JSON từ request
$data = json_decode(file_get_contents("php://input"));

// Kiểm tra dữ liệu JSON hợp lệ
if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data received.']);
    exit;
}

$username = trim($data->username ?? '');
$email = trim($data->email ?? null);
$password = trim($data->password ?? '');

// Kiểm tra input
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

// ✅ Kiểm tra username đã tồn tại chưa
$stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_check->bind_param("s", $username);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already exists.']);
    $stmt_check->close();
    $conn->close();
    exit;
}
$stmt_check->close();

// ✅ Mã hóa mật khẩu an toàn
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// ✅ Thêm người dùng mới
$stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt_insert->bind_param("sss", $username, $email, $hashed_password);

if ($stmt_insert->execute()) {
    session_start();
    $user_id = $conn->insert_id;

    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = 'user'; // vai trò mặc định

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! You are now logged in.',
        'redirect' => BASE_URL . 'index.php' // ✅ redirect động theo cấu hình
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt_insert->error]);
}

$stmt_insert->close();
$conn->close();
?>
