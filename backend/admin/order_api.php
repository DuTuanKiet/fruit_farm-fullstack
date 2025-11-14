<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// Giả định các file cần thiết
require_once __DIR__ . '/../../backend/core/config.php'; 
require_once __DIR__ . '/../../backend/core/db_connect.php'; 
// Đảm bảo có file kiểm tra quyền Admin
require_once __DIR__ . '/admin_auth.php'; 

$response = ['success' => false, 'message' => 'Lỗi không xác định.'];
$data = json_decode(file_get_contents("php://input"));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $data) {
    $action = $data->action ?? '';
    $order_id = $data->order_id ?? null;

    if ($action === 'confirm_order' && $order_id && is_numeric($order_id)) {
        $new_status = 'completed'; 

        try {
            // Cập nhật trạng thái đơn hàng
            $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ? AND status != 'completed'");
            $stmt->bind_param("si", $new_status, $order_id);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $response = [
                    'success' => true,
                    'message' => "Đơn hàng #{$order_id} đã được xác nhận thành công!",
                    'new_status_text' => 'Đã hoàn thành',
                    'new_badge_class' => 'badge-completed' 
                ];
            } elseif ($stmt->affected_rows === 0) {
                 $response = ['success' => false, 'message' => "Đơn hàng #{$order_id} đã được xác nhận trước đó hoặc không tồn tại."];
            } else {
                $response = ['success' => false, 'message' => "Lỗi cập nhật CSDL: " . $stmt->error];
            }
            $stmt->close();
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => "Lỗi hệ thống: " . $e->getMessage()];
        }
    } else {
        $response = ['success' => false, 'message' => 'Hành động hoặc ID không hợp lệ.'];
    }
}

$conn->close();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>