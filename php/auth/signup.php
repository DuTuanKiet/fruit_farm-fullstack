<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../db_connect.php'; 

header('Content-Type: application/json');

// Lấy dữ liệu JSON từ request
$data = json_decode(file_get_contents("php://input"));

// Kiểm tra dữ liệu JSON có hợp lệ không
if ($data === null) {
  echo json_encode(['success' => false, 'message' => 'Invalid JSON data received.']);
  exit;
}

$username = $data->username ?? '';
$email = $data->email ?? null;
$password = $data->password ?? '';

// Kiểm tra input 
if (empty($username) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
  exit;
}

// Kiểm tra username đã tồn tại chưa
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

// Mã hóa mật khẩu 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Chèn người dùng mới vào CSDL
$stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt_insert->bind_param("sss", $username, $email, $hashed_password);

if ($stmt_insert->execute()) {
  // Nếu chèn thành công, BẮT ĐẦU tự động đăng nhập
  session_start();
  
  // Lấy ID của người dùng vừa được tạo
  $user_id = $conn->insert_id; 
  
  // Lưu thông tin vào session
  $_SESSION['loggedin'] = true;
  $_SESSION['user_id'] = $user_id;
  $_SESSION['username'] = $username;
  $_SESSION['role'] = 'user'; // Vai trò mặc định
  
  // TRẢ VỀ JSON MỘT LẦN DUY NHẤT
  echo json_encode(['success' => true, 'message' => 'Registration successful! You are now logged in.']);

} else {
  // Trả về lỗi của CSDL để dễ gỡ rối
  echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt_insert->error]);
}

$stmt_insert->close();
$conn->close();
?>