<?php
session_start();
require_once(__DIR__ . '/../core/config.php'); 
header('Content-Type: application/json');

// Nhận dữ liệu JSON từ FE
$data = json_decode(file_get_contents("php://input"));
$product_id = isset($data->product_id) ? intval($data->product_id) : 0;
$quantity   = isset($data->quantity) ? intval($data->quantity) : 1;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để mua sản phẩm.',
        'redirect' => BASE_URL . 'login.php'
    ]);
    exit;
}

// Kiểm tra dữ liệu sản phẩm hợp lệ
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ.']);
    exit;
}

// Lưu thông tin sản phẩm "Mua ngay" vào session
$_SESSION['buy_now_item'] = [
    'product_id' => $product_id,
    'quantity'   => $quantity
];

// ✅ Trả JSON cho FE điều hướng tới trang thanh toán
echo json_encode([
    'success'  => true,
    'message'  => 'Sản phẩm đã được chọn để mua ngay.',
    'redirect' => BASE_URL . 'thanhtoan.php?mode=buy_now'
]);
?>
