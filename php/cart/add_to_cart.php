<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$data = json_decode(file_get_contents("php://input"));

if ($data === null) {
    
    http_response_code(400); 
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
    exit; 
}

$product_id = isset($data->product_id) ? (int)$data->product_id : 0;
$quantity = isset($data->quantity) ? (int)$data->quantity : -1; // Mặc định là số âm để không lọt vào điều kiện bên dưới


if ($product_id > 0) {
    if ($quantity > 0) {
        // Thêm hoặc cập nhật sản phẩm
        $_SESSION['cart'][$product_id] = $quantity;
    } elseif ($quantity === 0) {
        // Xóa sản phẩm nếu số lượng là 0
        unset($_SESSION['cart'][$product_id]);
    }
    // Nếu quantity < 0 (dữ liệu không hợp lệ)
}

// Luôn trả về một phản hồi JSON hợp lệ
echo json_encode(['success' => true, 'message' => 'Giỏ hàng đã được cập nhật.']);

?>