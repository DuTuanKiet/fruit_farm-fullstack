<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../backend/core/config.php';
if (!$conn) die("Lỗi kết nối database: ".mysqli_connect_error());

$dbname = "fruit_farm";

// Header để Word mở trực tiếp
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=database_doc.doc");

// CSS để Word đọc tốt + set lề
echo '<html><head><meta charset="UTF-8"><style>
@page {
    margin-top: 2cm;
    margin-bottom: 2cm;
    margin-left: 3cm;
    margin-right: 2cm;
}
body { font-family: Arial, sans-serif; font-size: 12pt; }
table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
th, td { border: 1px solid #333; padding: 5px; text-align: left; vertical-align: top; }
th { background-color: #f2f2f2; }
h2 { font-family: Arial, sans-serif; }
</style></head><body>';

// Mảng diễn giải đầy đủ
$columnDescriptions = [
    'carts'=>['id'=>'Mã giỏ hàng','user_id'=>'ID người dùng','product_id'=>'ID sản phẩm','quantity'=>'Số lượng','note'=>'Ghi chú'],
    'categories'=>['id'=>'Mã danh mục','name'=>'Tên danh mục','slug'=>'Slug hiển thị URL','created_at'=>'Ngày tạo'],
    'feedback'=>['id'=>'Mã phản hồi','user_id'=>'ID người dùng','name'=>'Tên người gửi','email'=>'Email người gửi','subject'=>'Tiêu đề','message'=>'Nội dung phản hồi','created_at'=>'Ngày gửi','status'=>'Trạng thái phản hồi'],
    'orders'=>['id'=>'Mã đơn hàng','order_code'=>'Mã đơn','user_id'=>'ID người dùng','fullname'=>'Họ tên','customer_name'=>'Tên khách hàng','customer_address'=>'Địa chỉ','customer_phone'=>'Số điện thoại','total_amount'=>'Tổng tiền','status'=>'Trạng thái: pending, completed, cancelled','order_note'=>'Ghi chú đơn hàng','order_date'=>'Ngày đặt','updated_at'=>'Ngày cập nhật','payment_method'=>'Phương thức thanh toán','payment_status'=>'Trạng thái thanh toán'],
    'order_details'=>['id'=>'Mã chi tiết đơn','order_id'=>'ID đơn hàng','product_id'=>'ID sản phẩm','product_name'=>'Tên sản phẩm','quantity'=>'Số lượng','note'=>'Ghi chú','price'=>'Giá tại thời điểm mua'],
    'products'=>['id'=>'Mã sản phẩm','name'=>'Tên sản phẩm','description'=>'Mô tả ngắn','details'=>'Chi tiết sản phẩm','is_featured'=>'Sản phẩm nổi bật','price'=>'Giá bán','image_url'=>'Link ảnh','created_at'=>'Ngày tạo','cost_price'=>'Giá gốc','stock'=>'Số lượng tồn kho','category_id'=>'ID danh mục','status'=>'Trạng thái','views'=>'Số lượt xem'],
    'product_views'=>['id'=>'Mã lượt xem','product_id'=>'ID sản phẩm','user_id'=>'ID người dùng','viewed_at'=>'Thời gian xem'],
    'testimonials'=>['id'=>'Mã đánh giá','user_id'=>'ID người dùng','name'=>'Tên người dùng','user_image'=>'Ảnh người dùng','feedback'=>'Nội dung đánh giá','rating'=>'Điểm đánh giá','created_at'=>'Ngày tạo'],
    'users'=>['id'=>'Mã người dùng','username'=>'Tên đăng nhập','password'=>'Mật khẩu','role'=>'Vai trò','status'=>'Trạng thái tài khoản','is_active'=>'Đang hoạt động','email'=>'Email','created_at'=>'Ngày tạo','phone'=>'Số điện thoại','address'=>'Địa chỉ']
];

// Lấy danh sách bảng
$tablesResult = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$dbname'");
if (!$tablesResult) die("Lỗi lấy danh sách bảng: ".$conn->error);

while($tableRow = $tablesResult->fetch_assoc()){
    $tableName = $tableRow['TABLE_NAME'];
    echo "<h2>Bảng: $tableName</h2>";
    echo '<table>';
    echo '<tr><th>TT</th><th>Thuộc tính</th><th>Kiểu</th><th>Kích thước</th><th>Khóa</th><th>Duy nhất</th><th>Bắt buộc</th><th>Diễn giải</th></tr>';

    // Lấy cột
    $sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
            ORDER BY ORDINAL_POSITION";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss",$dbname,$tableName);
    $stmt->execute();
    $result = $stmt->get_result();

    // Lấy khóa ngoại
    $fkQuery = $conn->prepare("
        SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $fkQuery->bind_param("ss",$dbname,$tableName);
    $fkQuery->execute();
    $fkResult = $fkQuery->get_result();
    $foreignKeys = [];
    while($fkRow = $fkResult->fetch_assoc()){
        $foreignKeys[$fkRow['COLUMN_NAME']] = $fkRow['REFERENCED_TABLE_NAME']."(".$fkRow['REFERENCED_COLUMN_NAME'].")";
    }

    // Lấy cột UNIQUE
    $uniqueColumns = [];
    $uniqQuery = $conn->prepare("
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND NON_UNIQUE=0
    ");
    $uniqQuery->bind_param("ss",$dbname,$tableName);
    $uniqQuery->execute();
    $uniqResult = $uniqQuery->get_result();
    while($uniqRow = $uniqResult->fetch_assoc()){
        $uniqueColumns[] = $uniqRow['COLUMN_NAME'];
    }

    $tt = 1;
    while($row = $result->fetch_assoc()){
        // Kiểu và kích thước
        if(preg_match('/^([^\(]+)(\((.+)\))?$/',$row['COLUMN_TYPE'],$matches)){
            $type = trim($matches[1]);
            $size = isset($matches[3]) ? $matches[3] : '';
        } else {
            $type = $row['COLUMN_TYPE'];
            $size = '';
        }

        // Khóa
        $key = '';
        if($row['COLUMN_KEY']=='PRI') $key='Khóa chính';
        elseif(isset($foreignKeys[$row['COLUMN_NAME']])) $key='FK -> '.$foreignKeys[$row['COLUMN_NAME']];

        // Duy nhất
        $unique = in_array($row['COLUMN_NAME'],$uniqueColumns)?'X':'';

        // Bắt buộc
        $required = ($row['IS_NULLABLE']=='NO')?'X':'';

        // Diễn giải
        $desc = $columnDescriptions[$tableName][$row['COLUMN_NAME']] ?? $row['COLUMN_COMMENT'] ?? '';

        echo "<tr>
            <td>{$tt}</td>
            <td>{$row['COLUMN_NAME']}</td>
            <td>{$type}</td>
            <td>{$size}</td>
            <td>{$key}</td>
            <td>{$unique}</td>
            <td>{$required}</td>
            <td>{$desc}</td>
        </tr>";
        $tt++;
    }

    echo '</table>';
    $stmt->close();
    $fkQuery->close();
    $uniqQuery->close();
}

$conn->close();
echo '</body></html>';
