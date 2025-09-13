<?php
require_once 'db_connect.php';
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
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'Username already exists.']);
  $stmt->close();
  $conn->close();
  exit;
}
$stmt->close();

// Mã hóa mật khẩu 
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Chèn người dùng mới vào CSDL
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Registration successful!']);
} else {
  // Trả về lỗi của CSDL để dễ gỡ rối
  echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>