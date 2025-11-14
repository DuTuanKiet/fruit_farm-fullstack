<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php'); 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện thao tác này.']);
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
    exit();
}

$action    = $_POST['action'];
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity  = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$note      = trim($_POST['note'] ?? '');

// 🧩 Log kiểm tra
error_log("[DEBUG NOTE] Action={$action}, ProductID={$productId}, Note='{$note}'", 0);

if ($productId <= 0 && $action !== 'update_note') {
    echo json_encode(['success' => false, 'message' => 'Thiếu hoặc sai product_id.']);
    exit();
}

// ===== HÀM TÍNH TỔNG GIỎ HÀNG =====
function calculate_cart_totals($conn, $userId) {
    $subtotalAmount = 0;
    $shipping = 20000;

    $stmt = $conn->prepare("
        SELECT c.quantity, p.price
        FROM carts c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $subtotalAmount += (float)$row['quantity'] * (float)$row['price'];
    }

    $grandTotal = $subtotalAmount + $shipping;
    return ['subtotalAmount' => $subtotalAmount, 'grandTotal' => $grandTotal];
}

// ===== XỬ LÝ CÁC HÀNH ĐỘNG =====
switch ($action) {

    case 'add':
        $stmt_check = $conn->prepare("SELECT quantity FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt_check->bind_param("ii", $userId, $productId);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $stmt_update = $conn->prepare("UPDATE carts SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt_update->bind_param("iii", $quantity, $userId, $productId);
            $stmt_update->execute();
        } else {
            $stmt_insert = $conn->prepare("INSERT INTO carts (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("iii", $userId, $productId, $quantity);
            $stmt_insert->execute();
        }

        echo json_encode(['success' => true, 'message' => 'Đã thêm sản phẩm vào giỏ hàng!']);
        break;

    case 'update':
        if ($quantity > 0) {
            $stmt_update = $conn->prepare("UPDATE carts SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt_update->bind_param("iii", $quantity, $userId, $productId);
            $stmt_update->execute();
        } else {
            $stmt_delete = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
            $stmt_delete->bind_param("ii", $userId, $productId);
            $stmt_delete->execute();
        }

        $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt_price->bind_param("i", $productId);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $product_price = $result_price->fetch_assoc()['price'] ?? 0;

        $new_subtotal = $product_price * $quantity;
        $totals = calculate_cart_totals($conn, $userId);

        echo json_encode(array_merge([
            'success' => true,
            'message' => 'Đã cập nhật giỏ hàng.',
            'subtotal' => $new_subtotal
        ], $totals));
        break;

    case 'remove':
        $stmt_delete = $conn->prepare("DELETE FROM carts WHERE user_id = ? AND product_id = ?");
        $stmt_delete->bind_param("ii", $userId, $productId);
        $stmt_delete->execute();

        $totals = calculate_cart_totals($conn, $userId);
        echo json_encode(array_merge([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
        ], $totals));
        break;

    case 'update_note':
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu product_id khi cập nhật ghi chú.']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE carts SET note = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("sii", $note, $userId, $productId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Đã lưu ghi chú sản phẩm vào giỏ hàng.']);
        } else {
            error_log("Lỗi DB khi cập nhật ghi chú: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Lỗi DB khi cập nhật ghi chú!']);
        }
        $stmt->close();
        break;
}

$conn->close();
?>
