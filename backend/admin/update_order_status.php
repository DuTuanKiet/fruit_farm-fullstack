<?php
// backend/admin/update_order_status.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db_connect.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// 1. Kiểm tra Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    $response['message'] = 'Không có quyền truy cập.';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

// 2. Lấy dữ liệu từ AJAX
$data = json_decode(file_get_contents("php://input"), true);
$order_id = (int)($data['order_id'] ?? 0);
$order_code = $data['order_code'] ?? null;
$new_status = $data['new_status'] ?? '';

// 3. Trạng thái hợp lệ
$valid_statuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    $response['message'] = 'Trạng thái không hợp lệ.';
    echo json_encode($response);
    exit;
}

// 4. Nếu có order_code thì tìm ID
if ($order_code) {
    $stmt = $conn->prepare("SELECT id FROM orders WHERE order_code = ?");
    $stmt->bind_param("s", $order_code);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $response['message'] = 'Không tìm thấy đơn hàng với mã: ' . $order_code;
        echo json_encode($response);
        exit;
    }
    $order_id = (int)$result['id'];
}

// Kiểm tra lại order_id
if ($order_id <= 0) {
    $response['message'] = 'order_id không hợp lệ.';
    echo json_encode($response);
    exit;
}

// 5. Update vào DB
try {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Cập nhật trạng thái đơn hàng thành công.";
    } else {
        $response['message'] = 'Lỗi CSDL: ' . $conn->error;
    }
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Lỗi hệ thống: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>
