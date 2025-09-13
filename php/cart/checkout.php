<?php
session_start();
require_once '../db_connect.php';
header('Content-Type: application/json');

// Kiểm tra xem người dùng đã đăng nhập và có hàng trong giỏ chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thanh toán.']);
    exit;
}
if (empty($_SESSION['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng của bạn đang trống.']);
    exit;
}

// Lấy thông tin sản phẩm để tính tổng tiền
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$types = str_repeat('i', count($product_ids));

$stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();
$products_info = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$total_price = 0;
$total_quantity = 0;
$total_items = count($_SESSION['cart']);

foreach ($products_info as $product) {
    $product_id = $product['id'];
    $quantity = $_SESSION['cart'][$product_id];
    $total_price += $product['price'] * $quantity;
    $total_quantity += $quantity;
}

// Ở đây chúng ta chỉ xóa giỏ hàng và trả về thông báo
unset($_SESSION['cart']);

echo json_encode([
    'status' => 'success',
    'total_items' => $total_items,
    'total_quantity' => $total_quantity,
    'total_price' => $total_price
]);
?>