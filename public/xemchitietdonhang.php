<?php
session_start();
require_once __DIR__ . '/../backend/core/config.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>Bạn cần đăng nhập để xem chi tiết đơn hàng.</p>";
    exit;
}

if (!isset($_GET['order_id'])) {
    echo "<p>Không tìm thấy mã đơn hàng.</p>";
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$sql_order = "
    SELECT id, order_date, total_amount, status, 
           customer_name, customer_phone, customer_address 
    FROM orders 
    WHERE id = ? AND user_id = ?
";
$stmt = $conn->prepare($sql_order);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<p>Đơn hàng không tồn tại hoặc không thuộc tài khoản của bạn.</p>";
    exit;
}

// Lấy danh sách sản phẩm trong đơn
$sql_details = "
    SELECT od.*, p.name AS product_name, p.image_url AS product_image, od.note 
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
";
$stmt2 = $conn->prepare($sql_details);
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$result = $stmt2->get_result();

$calculated_sub_total = 0;
$order_items = [];
while ($item = $result->fetch_assoc()) {
    $calculated_sub_total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    $order_items[] = $item;
}

$shipping_fee = ($order['total_amount'] ?? 0) - $calculated_sub_total;
$status_class = 'status-' . strtolower($order['status'] ?? 'pending');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $order_id ?> - Fruit Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/chitietdonhang.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="section-content order-detail-container" style="padding-top: 100px;">
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>public/index.php">Trang chủ</a> &gt;
        <a href="<?= BASE_URL ?>public/xemdonhang.php">Đơn hàng của tôi</a> &gt;
        <span>Chi tiết đơn hàng #<?= $order['id'] ?></span>
    </div>

    <div class="order-detail-header">
        <h2>Chi tiết đơn hàng #<?= $order['id'] ?></h2>
        <?php
            $status_vn = match ($order['status'] ?? 'pending') {
                'pending' => 'Đang chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'shipping' => 'Đang giao hàng',
                'completed' => 'Đã hoàn thành',
                'cancelled' => 'Đã hủy',
                default => 'Không rõ'
            };
            ?>
        <div class="order-status-tag status <?= $status_class ?>">
         <?= $status_vn ?>
        </div>
    </div>

    <div class="summary-and-shipping-grid">
        <div class="order-card summary-card">
            <h3><i class="fa fa-info-circle"></i> Thông tin tóm tắt</h3>
            <div class="summary-row">
                <span>Ngày đặt hàng:</span>
                <strong><?= date("d/m/Y H:i", strtotime($order['order_date'] ?? 'now')) ?></strong>
            </div>
            <div class="summary-row">
                <span>Phương thức thanh toán:</span>
                <strong>COD</strong>
            </div>
            <div class="summary-row">
                <span>Tạm tính:</span>
                <strong><?= number_format($calculated_sub_total, 0, ',', '.') ?>₫</strong>
            </div>
            <div class="summary-row">
                <span>Phí vận chuyển:</span>
                <strong><?= number_format($shipping_fee, 0, ',', '.') ?>₫</strong>
            </div>
            <div class="summary-row total-row">
                <span>Tổng thanh toán:</span>
                <strong class="total-amount-display">
                    <?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>₫
                </strong>
            </div>
        </div>

        <div class="order-card shipping-card">
            <h3><i class="fa fa-map-marker-alt"></i> Địa chỉ giao hàng</h3>
            <p><b>Người nhận:</b> <?= htmlspecialchars($order['customer_name'] ?? 'Khách hàng') ?></p>
            <p><b>Điện thoại:</b> <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
            <p><b>Địa chỉ:</b> <?= htmlspecialchars($order['customer_address'] ?? 'Chưa cung cấp') ?></p>
        </div>
    </div>

    <div class="order-card items-card">
        <h3><i class="fa fa-box-open"></i> Sản phẩm trong đơn</h3>
        <div class="table-responsive">
            <table>
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
    <?php $count = 1; foreach ($order_items as $item): ?>
    <tr>
        <td><?= $count++ ?></td>
        <td class="product-info-cell">
            <img src="<?= BASE_URL ?>public/assets/images/<?= htmlspecialchars(basename($item['product_image'])) ?>" class="product-img" alt="Ảnh sản phẩm">
            <span class="product-name"><?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm') ?></span>
        </td>
        <td><?= number_format($item['price'] ?? 0, 0, ',', '.') ?>₫</td>
        <td><?= $item['quantity'] ?? 1 ?></td>
        <td class="subtotal-cell"><?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') ?>₫</td>
    </tr>

    <?php if (!empty($item['note'])): ?>
    <tr class="note-row">
        <td></td>
        <td colspan="4">
            <div class="product-note">
                <i class="fa fa-sticky-note"></i> 
                <strong>Ghi chú:</strong> <?= htmlspecialchars($item['note']) ?>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
</tbody>
<!-- Hàng tổng tiền -->
<tr class="total-row">
    <td colspan="4" style="text-align:right; font-weight:600; font-size:1rem;">
        Tổng tiền đơn hàng:
    </td>
    <td colspan="2" style="font-weight:700; color:#2c3333; font-size:1.1rem;">
        <?= number_format($calculated_sub_total, 0, ',', '.') ?>₫
    </td>
</tr>
            </table>
        </div>
    </div>

    <div class="back-link">
        <a href="xemdonhang.php" class="btn-back"><i class="fa fa-arrow-left"></i> Quay lại danh sách đơn hàng</a>
    </div>
</main>
<div class="toast-container" id="toast-container"></div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
