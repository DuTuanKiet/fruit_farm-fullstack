<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../core/db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['status'=>'error','message'=>'Không xác định'];

try {
    $action = $_POST['action'] ?? '';

    switch($action){

        case 'add':
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if($name && $slug){
                $stmt = $conn->prepare("INSERT INTO categories(name, slug, created_at) VALUES (?, ?, NOW())");
                $stmt->bind_param("ss", $name, $slug);
                if($stmt->execute()){
                    $id = $stmt->insert_id;
                    $created_at = date('Y-m-d H:i:s');
                    $response = [
                        'status'=>'success',
                        'category'=>[
                            'id'=>$id,
                            'name'=>$name,
                            'slug'=>$slug,
                            'created_at'=>$created_at
                        ]
                    ];

                    // Reset AUTO_INCREMENT liên tục
                    $conn->query("ALTER TABLE categories AUTO_INCREMENT = ".($id+1));
                } else {
                    $response['message'] = 'Thêm thất bại: '.$stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Dữ liệu không hợp lệ';
            }
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $confirm = intval($_POST['confirm'] ?? 0);

            if($id && $name && $slug){
                // Kiểm tra sản phẩm
                $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=?");
                $stmt_check->bind_param("i", $id);
                $stmt_check->execute();
                $res = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if($res['total'] > 0 && !$confirm){
                    $response = [
                        'status'=>'warning',
                        'message'=>"Danh mục đang có {$res['total']} sản phẩm. Bạn có muốn cập nhật tên/slugs sản phẩm không?"
                    ];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // Cập nhật categories
                $stmt = $conn->prepare("UPDATE categories SET name=?, slug=? WHERE id=?");
                $stmt->bind_param("ssi", $name, $slug, $id);
                if($stmt->execute()){
                    // Cập nhật products nếu có
                    if($res['total'] > 0){
                        $stmt_prod = $conn->prepare("UPDATE products SET category_name=?, category_slug=? WHERE category_id=?");
                        $stmt_prod->bind_param("ssi", $name, $slug, $id);
                        $stmt_prod->execute();
                        $stmt_prod->close();
                    }
                    $response = ['status'=>'success'];
                } else {
                    $response['message'] = 'Cập nhật thất bại: '.$stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Dữ liệu không hợp lệ';
            }
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if($id){
                // Kiểm tra sản phẩm
                $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=?");
                $stmt_check->bind_param("i",$id);
                $stmt_check->execute();
                $res = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if($res['total'] > 0){
                    $response = ['status'=>'error','message'=>'Danh mục đang có sản phẩm, không thể xóa'];
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
                $stmt->bind_param("i",$id);
                if($stmt->execute()){
                    $response = ['status'=>'success'];

                    // Reset AUTO_INCREMENT liên tục
                    $max_id = $conn->query("SELECT IFNULL(MAX(id),0) AS max_id FROM categories")->fetch_assoc()['max_id'];
                    $conn->query("ALTER TABLE categories AUTO_INCREMENT = ".($max_id+1));

                } else {
                    $response['message'] = 'Xóa thất bại: '.$stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Dữ liệu không hợp lệ';
            }
            break;

        default:
            $response['message'] = 'Action không hợp lệ';
            break;
    }

} catch(Exception $e){
    $response['message'] = 'Lỗi server: '.$e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
