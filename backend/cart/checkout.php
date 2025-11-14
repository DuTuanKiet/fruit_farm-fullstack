<?php
session_start();
require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');
header('Content-Type: application/json');

// --- Kiểm tra đăng nhập và giỏ hàng ---
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thanh toán.']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng của bạn đang trống.']);
    exit;
}

// --- Lấy thông tin sản phẩm từ DB ---
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$types = str_repeat('i', count($product_ids));

$stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$product_ids);
$stmt->execute();
$result = $stmt->get_result();
$products_info = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- Tính tổng tiền và số lượng ---
$total_amount = 0;
$total_quantity = 0;
$total_items = count($_SESSION['cart']);

foreach ($products_info as $product) {
    $product_id = $product['id'];
    $quantity = $_SESSION['cart'][$product_id];
    $total_amount += $product['price'] * $quantity;
    $total_quantity += $quantity;
}

// --- Tạo đơn hàng trong bảng orders ---
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, fullname, customer_name, customer_address, customer_phone, total_amount, status, order_date) VALUES (?, '', '', '', '', ?, 'pending', NOW())");
$order_stmt->bind_param("id", $_SESSION['user_id'], $total_amount);

if (!$order_stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Không thể tạo đơn hàng.']);
    $conn->close();
    exit;
}

$order_id = $order_stmt->insert_id;
$order_stmt->close();

// --- Thêm chi tiết đơn hàng vào order_detail ---
$item_stmt = $conn->prepare("INSERT INTO order_detail (order_id, product_id, product_name, quantity, note, price) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($products_info as $product) {
    $pid = $product['id'];
    $pname = $product['name'];
    $qty = $_SESSION['cart'][$pid];
    $price = $product['price'];
    $note = null;

    $item_stmt->bind_param("iisssd", $order_id, $pid, $pname, $qty, $note, $price);
    $item_stmt->execute();
}
$item_stmt->close();

// --- Xóa giỏ hàng ---
unset($_SESSION['cart']);

// --- Trả phản hồi ---
echo json_encode([
    'status' => 'success',
    'message' => 'Đặt hàng thành công!',
    'order_id' => $order_id,
    'total_items' => $total_items,
    'total_quantity' => $total_quantity,
    'total_amount' => $total_amount
]);

$conn->close();
?>
