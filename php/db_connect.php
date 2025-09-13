<?php
// Kết nối CSDL
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "fruit_farm";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

// Kiểm tra kết nối
if ($conn->connect_error) {
  // Dừng chương trình và báo lỗi nếu kết nối thất bại
  die("Connection failed: " . $conn->connect_error);
}

// Thiết lập charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");
?>