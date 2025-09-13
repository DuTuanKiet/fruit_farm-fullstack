<?php
// php/cart/get_cart.php
session_start();
require_once '../db_connect.php'; // Đảm bảo đường dẫn này đúng
header('Content-Type: application/json');

// Nếu giỏ hàng trống, trả về cấu trúc dữ liệu rỗng
if (empty($_SESSION['cart'])) {
    echo json_encode(['cartItems' => [], 'grandTotal' => 0]);
    exit;
}

$cart_items = [];
$grand_total = 0;

$product_ids = array_keys($_SESSION['cart']);

$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$types = str_repeat('i', count($product_ids));

$stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
if ($stmt === false) {
    // Xử lý lỗi nếu câu lệnh prepare thất bại
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn CSDL.']);
    exit;
}

$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['id'];
        $quantity = $_SESSION['cart'][$product_id];
        
        
        $subtotal = (float)$row['price'] * $quantity;
        
        $grand_total += $subtotal;

        $cart_items[] = [
            'product_id' => $product_id,
            'name' => $row['name'],
            'price' => (float)$row['price'],
            'image_url' => $row['image_url'], 
            'quantity' => $quantity,
            'subtotal' => $subtotal 
        ];
    }
}

echo json_encode([
    'cartItems' => $cart_items,
    'grandTotal' => $grand_total
]);

$stmt->close();
$conn->close();
?>