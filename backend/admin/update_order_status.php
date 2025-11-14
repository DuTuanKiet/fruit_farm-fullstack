<?php
// backend/admin/update_order_status.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db_connect.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// 1. Kiểm tra Admin (đảm bảo chỉ Admin mới có thể gọi API này)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    $response['message'] = 'Không có quyền truy cập.';
    http_response_code(403);
    echo json_encode($response);
    exit;
}

// 2. Lấy dữ liệu từ AJAX
$data = json_decode(file_get_contents("php://input"), true);
$order_id = (int)($data['order_id'] ?? 0);
$new_status = $data['new_status'] ?? '';

// 3. Chuẩn hóa trạng thái hợp lệ
$valid_statuses = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];
if ($order_id <= 0 || !in_array($new_status, $valid_statuses)) {
    $response['message'] = 'Dữ liệu không hợp lệ.';
    echo json_encode($response);
    exit;
}

// 4. Cập nhật vào Database
try {
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Cập nhật trạng thái đơn hàng #' . $order_id . ' thành công.';
    } else {
        $response['message'] = 'Lỗi cập nhật CSDL: ' . $conn->error;
    }
    $stmt->close();
} catch (\Throwable $th) {
    $response['message'] = 'Lỗi hệ thống: ' . $th->getMessage();
}

$conn->close();
echo json_encode($response);
?>