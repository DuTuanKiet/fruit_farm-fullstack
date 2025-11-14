<?php
// =======================================================
// FILE: /backend/cart/get_cart.php
// =======================================================

// Hiển thị lỗi khi chạy dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối cấu hình và DB
require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../core/db_connect.php');

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Báo cáo lỗi chi tiết từ MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// =======================================================
// Xác định file được gọi trực tiếp hay include
// =======================================================
$is_ajax = (basename($_SERVER['PHP_SELF']) === 'get_cart.php');
define('IS_AJAX_CALL', $is_ajax);

// =======================================================
// CÁC HÀM PHỤ (dùng cho xem đơn hàng / thống kê)
// =======================================================
function get_user_orders(mysqli $conn, int $user_id): array {
    $orders = [];
    $sql = "SELECT id AS order_id, user_id, total_amount, status, created_at AS order_date
            FROM orders 
            WHERE user_id = ?
            ORDER BY created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Lỗi truy vấn get_user_orders: " . $e->getMessage());
    }

    return $orders;
}

function get_order_detail(mysqli $conn, int $order_id, int $user_id): ?array {
    $sql = "SELECT 
                id AS order_id, created_at AS order_date, total_amount, status, 
                customer_name, customer_phone, customer_address
            FROM orders
            WHERE id = ? AND user_id = ?";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $detail = $result->fetch_assoc();
        $stmt->close();
        return $detail;
    } catch (Exception $e) {
        error_log("Lỗi truy vấn get_order_detail: " . $e->getMessage());
        return null;
    }
}

function get_order_items(mysqli $conn, int $order_id): array {
    $items = [];
    $sql = "SELECT 
                od.product_id, od.quantity, od.price, od.note, 
                p.name AS product_name, p.image_url
            FROM order_details od
            JOIN products p ON od.product_id = p.id
            WHERE od.order_id = ?";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("Lỗi truy vấn get_order_items: " . $e->getMessage());
    }

    return $items;
}

// =======================================================
// LẤY GIỎ HÀNG (chạy khi gọi trực tiếp bằng AJAX)
// =======================================================
if (IS_AJAX_CALL) {
    header('Content-Type: application/json; charset=utf-8');
    ini_set('display_errors', 0);

    $cartItems = [];
    $subtotalAmount = 0;
    $grandTotal = 0;

    // Nếu chưa đăng nhập → giỏ hàng trống
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'cartItems' => [],
            'subtotalAmount' => 0,
            'grandTotal' => 0
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userId = $_SESSION['user_id'];

    try {
        $sql = "SELECT 
                    c.product_id, c.quantity, c.note, 
                    p.name, p.price, p.image_url
                FROM carts c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Đảm bảo URL ảnh hợp lệ
            if (!empty($row['image_url']) && strpos($row['image_url'], BASE_URL) === false) {
                $row['image_url'] = BASE_URL . $row['image_url'];
            }

            $row['subtotal'] = (float)$row['quantity'] * (float)$row['price'];
            $cartItems[] = $row;
            $subtotalAmount += $row['subtotal'];
        }

        // Tổng tiền tạm tính + phí ship cố định
        $shippingFee = 20000;
        $grandTotal = $subtotalAmount + $shippingFee;

        echo json_encode([
            'cartItems' => $cartItems,
            'subtotalAmount' => $subtotalAmount,
            'grandTotal' => $grandTotal
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Lỗi AJAX Giỏ hàng: " . $e->getMessage());
        echo json_encode([
            'error' => 'Lỗi CSDL khi lấy giỏ hàng.',
            'cartItems' => [],
            'subtotalAmount' => 0,
            'grandTotal' => 0
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}
?>
