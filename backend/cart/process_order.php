<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');

function generateOrderCode($userId) {
    return 'DH-' . strtoupper(substr(md5(uniqid($userId, true)), 0, 10));
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Phương thức không hợp lệ');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Lấy dữ liệu form
$userId   = $_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$mode     = $_POST['mode'] ?? 'cart';
$product_notes = $_POST['notes'] ?? [];

// Kiểm tra thông tin giao hàng
if (!$fullname || !$phone || !$address) {
    $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin giao hàng.";
    file_put_contents(__DIR__ . '/debug_log.txt', "Redirect: " . ($_SESSION['error_message'] ?? 'no message') . PHP_EOL, FILE_APPEND);

    header('Location: ' . BASE_URL . 'public/thanhtoan.php');
    exit();
}

// --- 1️⃣ Lấy sản phẩm cần thanh toán ---
$items_to_process = [];
if ($mode === 'buy_now' && isset($_SESSION['buy_now_item'])) {
    $item = $_SESSION['buy_now_item'];
    $stmt = $conn->prepare("SELECT id, name, price, stock, image_url FROM products WHERE id=? AND stock>0");
    $stmt->bind_param("i", $item['product_id']);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($product) {
        $product['quantity'] = min($item['quantity'], $product['stock']);
        $items_to_process[] = $product;
    }
} else {
    $stmt = $conn->prepare("
        SELECT p.id, p.name, p.price, p.stock, c.quantity, c.note
        FROM carts c
        JOIN products p ON c.product_id=p.id
        WHERE c.user_id=? AND p.stock>0
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = min($row['quantity'], $row['stock']);
        $items_to_process[] = $row;
    }
    $stmt->close();
}

// Kiểm tra có sản phẩm không
if (empty($items_to_process)) {
    $_SESSION['error_message'] = "Không có sản phẩm nào để xử lý.";
    header('Location: ' . BASE_URL . 'public/thanhtoan.php');
    exit();
}

// --- 2️⃣ Tính tổng tiền ---
$server_total_price = 0;
foreach ($items_to_process as $item) {
    $server_total_price += $item['price'] * $item['quantity'];
}
$shipping_fee = 20000;
$final_total = $server_total_price + $shipping_fee;

// --- 3️⃣ Transaction an toàn ---
$conn->begin_transaction();
try {
    // Kiểm tra tồn kho (lock row)
    $ids = array_column($items_to_process, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt_check = $conn->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
    $stmt_check->bind_param($types, ...$ids);
    $stmt_check->execute();
    $stock_rows = $stmt_check->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_check->close();

    $stock_map = [];
    foreach ($stock_rows as $row) $stock_map[$row['id']] = $row['stock'];

    foreach ($items_to_process as $item) {
        if (!isset($stock_map[$item['id']]) || $stock_map[$item['id']] < $item['quantity']) {
            throw new Exception("Sản phẩm '{$item['name']}' không đủ tồn kho.");
        }
    }

    // Tạo đơn hàng
    $order_code = generateOrderCode($userId);
    $status = 'pending';
    $payment_method = 'cod';

    $stmt_order = $conn->prepare("
    INSERT INTO orders (user_id, order_code, customer_name, customer_phone, customer_address, total_amount, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_order->bind_param(
    "issssds",
    $userId,
    $order_code,
    $fullname,
    $phone,
    $address,
    $final_total,
    $status
    );
    $stmt_order->execute();
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // Thêm chi tiết đơn hàng và trừ tồn kho
    $stmt_details = $conn->prepare("
        INSERT INTO order_details (order_id, product_id, quantity, price, note)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt_update_stock = $conn->prepare("UPDATE products SET stock=stock-? WHERE id=?");

    foreach ($items_to_process as $item) {
        $note = $product_notes[$item['id']] ?? trim($item['note'] ?? '');
        $note = $note === '' ? null : $note;

        $stmt_details->bind_param("iiids", $order_id, $item['id'], $item['quantity'], $item['price'], $note);
        $stmt_details->execute();

        $stmt_update_stock->bind_param("ii", $item['quantity'], $item['id']);
        $stmt_update_stock->execute();
    }

    $stmt_details->close();
    $stmt_update_stock->close();

    // Xóa giỏ hàng nếu mode là cart
    if ($mode === 'cart') {
        $stmt_delete = $conn->prepare("DELETE FROM carts WHERE user_id=?");
        $stmt_delete->bind_param("i", $userId);
        $stmt_delete->execute();
        $stmt_delete->close();
    }

    $conn->commit();

    // Xóa session buy_now_item nếu có
    if ($mode === 'buy_now') unset($_SESSION['buy_now_item']);
    $_SESSION['cart'] = [];
    // --- Redirect khi đặt hàng thành công ---
    header('Location: ' . BASE_URL . 'public/order_success.php?order_id=' . $order_id);
    exit();


} catch (Exception $e) {
    $conn->rollback();
    // --- Redirect khi lỗi ---
    $_SESSION['error_message'] = "Xảy ra lỗi khi đặt hàng: " . $e->getMessage();
    header('Location: ' . BASE_URL . 'public/thanhtoan.php');
    exit();
}

$conn->close();
