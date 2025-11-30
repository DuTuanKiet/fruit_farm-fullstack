<?php
session_start();
require_once(__DIR__ . '/../core/config.php'); 
require_once(__DIR__ . '/../core/db_connect.php');

$user_id = $_SESSION['user_id'] ?? 0;
if ($user_id <= 0) {
    die("Bạn phải đăng nhập.");
}

// Fix lỗi: tránh undefined index
$mode = $_GET['mode'] ?? '';

if (isset($_SESSION['buy_now_item']) && $mode === 'buy_now') {

    $item = $_SESSION['buy_now_item'];
    $product_id = intval($item['product_id']);
    $quantity   = intval($item['quantity']);

    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) die("Sản phẩm không tồn tại.");

    // Fix logic tồn kho
    if ($product['stock'] <= 0) die("Sản phẩm đã hết hàng.");
    $quantity = min($quantity, $product['stock']);

    $total_amount = $product['price'] * $quantity;

    // Tạo đơn hàng mới
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, customer_name, customer_address, customer_phone, total_amount, status) 
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    $customer_name    = $_SESSION['username'] ?? 'Khách hàng';
    $customer_address = ''; // giữ nguyên logic của bạn
    $customer_phone   = ''; // giữ nguyên
    $stmt->bind_param("isssd", $user_id, $customer_name, $customer_address, $customer_phone, $total_amount);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Sinh order_code
    $order_code = 'ORD' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("UPDATE orders SET order_code = ? WHERE id = ?");
    $stmt->bind_param("si", $order_code, $order_id);
    $stmt->execute();

    // Thêm chi tiết đơn hàng
    $stmt = $conn->prepare("
        INSERT INTO order_details (order_id, product_id, product_name, quantity, price) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisid", $order_id, $product_id, $product['name'], $quantity, $product['price']);
    $stmt->execute();

    // Xóa session mua ngay
    unset($_SESSION['buy_now_item']);

    // Redirect sang order_success
    header("Location: order_success.php?order_id=$order_id");
    exit;
}
?>
