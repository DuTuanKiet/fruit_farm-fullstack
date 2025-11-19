<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

// Lấy order_code từ URL
$order_code = $_GET['order_code'] ?? null;
if (!$order_code) {
    echo "<p>Không tìm thấy mã đơn hàng.</p>";
    exit;
}

// Lấy thông tin đơn hàng
$sql_order = "
    SELECT id, order_code, order_date, total_amount, status,
           customer_name, customer_phone, customer_address, user_id, order_note, payment_method, payment_status
    FROM orders
    WHERE order_code = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("s", $order_code);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<p>Đơn hàng không tồn tại.</p>";
    exit;
}

// Mã đơn hiển thị
$display_code = $order['order_code'];

// Lấy chi tiết sản phẩm
$calculated_sub_total = 0;
$order_items = [];
$sql_details = "
    SELECT od.*, p.name AS product_name, p.image_url AS product_image, od.note
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
";
$stmt2 = $conn->prepare($sql_details);
$stmt2->bind_param("i", $order['id']);
$stmt2->execute();
$result = $stmt2->get_result();
while ($item = $result->fetch_assoc()) {
    $calculated_sub_total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    $order_items[] = $item;
}
$stmt2->close();

$shipping_fee = ($order['total_amount'] ?? 0) - $calculated_sub_total;

// class trạng thái
$status_class = 'status-' . strtolower($order['status'] ?? 'pending');

// trạng thái tiếng Việt
$status_vn = match ($order['status'] ?? 'pending') {
    'pending' => 'Đang chờ xác nhận',
    'confirmed' => 'Đã xác nhận',
    'shipping' => 'Đang giao hàng',
    'completed' => 'Đã hoàn thành',
    'cancelled' => 'Đã hủy',
    default => 'Không rõ'
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Admin – Chi tiết đơn hàng #<?= htmlspecialchars($display_code) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/order_detail.css">
</head>
<body>

<div class="admin-container">
    <div class="page-header">
        <h1>Chi tiết đơn hàng #<?= htmlspecialchars($display_code) ?></h1>
        <span class="status-badge <?= $status_class ?>"><?= $status_vn ?></span>
    </div>

    <div class="grid-2">
        <div class="card">
            <h3><i class="fa fa-info-circle"></i> Thông tin đơn hàng</h3>
            <div class="row"><span>Mã đơn:</span><strong><?= htmlspecialchars($display_code) ?></strong></div>
            <div class="row"><span>Ngày tạo:</span><strong><?= date("d/m/Y H:i", strtotime($order['order_date'])) ?></strong></div>
            <div class="row"><span>Tạm tính:</span><strong><?= number_format($calculated_sub_total) ?>₫</strong></div>
            <div class="row"><span>Phí vận chuyển:</span><strong><?= number_format($shipping_fee) ?>₫</strong></div>
            <div class="row total"><span>Tổng:</span><strong><?= number_format($order['total_amount']) ?>₫</strong></div>
        </div>

        <div class="card">
            <h3><i class="fa fa-user"></i> Khách hàng</h3>
            <p><b>Họ tên:</b> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><b>SĐT:</b> <?= htmlspecialchars($order['customer_phone']) ?></p>
            <p><b>Địa chỉ:</b> <?= htmlspecialchars($order['customer_address']) ?></p>
        </div>
    </div>

    <div class="action-box">
        <a href="<?= BASE_URL ?>public/fe/admin_dashboard.php?page=orders" class="btn-back">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card full">
        <h3><i class="fa fa-box-open"></i> Sản phẩm</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>SL</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($order_items as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="product-cell">
                        <img src="<?= BASE_URL ?>public/assets/images/<?= basename($item['product_image']) ?>" alt="">
                        <span><?= htmlspecialchars($item['product_name']) ?></span>
                    </td>
                    <td><?= number_format($item['price'] ?? 0) ?>₫</td>
                    <td><?= $item['quantity'] ?? 1 ?></td>
                    <td><?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?>₫</td>
                </tr>
                <?php if(!empty($item['note'])): ?>
                <tr class="note-row">
                    <td></td>
                    <td colspan="4"><i class="fa fa-note-sticky"></i> <b>Ghi chú:</b> <?= htmlspecialchars($item['note']) ?></td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
