<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['status'=>'error','message'=>'Không xác định'];
$action = $_POST['action'] ?? '';

$id = intval($_POST['id'] ?? 0);
$message = trim($_POST['message'] ?? '');

try {
    if($action === 'mark_read'){
        if($id){
            $stmt = $conn->prepare("UPDATE feedback SET status='read' WHERE id=?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()){
                $response = ['status'=>'success'];
            } else {
                $response['message'] = 'Không thể đánh dấu đã đọc: '.$stmt->error;
            }
        } else {
            $response['message'] = 'ID không hợp lệ';
        }

    } elseif($action === 'delete'){
        if($id){
            $stmt = $conn->prepare("DELETE FROM feedback WHERE id=?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()){
                $response = ['status'=>'success'];
            } else {
                $response['message'] = 'Xóa thất bại: '.$stmt->error;
            }
        } else {
            $response['message'] = 'ID không hợp lệ';
        }

    } elseif($action === 'reply'){
        if($id && $message){
            // Cập nhật phản hồi trực tiếp trong bảng feedback
            $stmt = $conn->prepare("UPDATE feedback SET reply_message=?, status='replied' WHERE id=?");
            $stmt->bind_param("si", $message, $id);
            if($stmt->execute()){
                $response = ['status'=>'success'];
            } else {
                $response['message'] = 'Gửi trả lời thất bại: '.$stmt->error;
            }
        } else {
            $response['message'] = 'Dữ liệu không hợp lệ';
        }

    } else {
        $response['message'] = 'Action không hợp lệ';
    }
} catch(Exception $e){
    $response['message'] = 'Lỗi server: '.$e->getMessage();
}

// Trả JSON duy nhất, không có output thừa
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
