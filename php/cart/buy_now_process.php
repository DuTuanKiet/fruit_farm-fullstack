<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));
$product_id = (int)$data->product_id;
$quantity = 1; 

if ($product_id > 0) {
    // Lưu sản phẩm vào một session riêng cho việc "Mua Ngay"
    $_SESSION['buy_now_item'] = [
        'product_id' => $product_id,
        'quantity' => $quantity
    ];

    echo json_encode([
        'success' => true,
        'redirect' => '/fruitfarm/thanhtoan.php?mode=buy_now' // Thêm mode để trang thanh toán nhận biết
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
}
?>