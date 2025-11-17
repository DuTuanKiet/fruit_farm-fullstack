<?php
require_once(__DIR__ . '/../backend/core/config.php');
require_once(__DIR__ . '/../backend/core/db_connect.php'); 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    die("Không tìm thấy đơn hàng.");
}

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Đơn hàng không tồn tại.");
}

// Chặn người khác xem đơn hàng không thuộc họ
if ($order['user_id'] != ($_SESSION['user_id'] ?? 0)) {
    die("Bạn không có quyền xem đơn hàng này.");
}

// ----------- XỬ LÝ THANH TOÁN CALLBACK -----------
$payment_status = 'pending';
$payment_method = null;

// VNPAY
if (isset($_GET['vnp_ResponseCode'])) {
    $payment_method = 'vnpay';
    $payment_status = ($_GET['vnp_ResponseCode'] == '00') ? 'success' : 'failed';
}
// MOMO
elseif (isset($_GET['resultCode'])) {
    $payment_method = 'momo';
    $payment_status = ($_GET['resultCode'] == '0') ? 'success' : 'failed';
}

// Nếu có phản hồi thì cập nhật DB
if ($payment_method && $payment_status !== 'pending') {
    $stmt = $conn->prepare("UPDATE orders SET payment_method = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $payment_method, $payment_status, $order_id);
    $stmt->execute();
}

// Mapping trạng thái sang tiếng Việt
$status_labels = [
    'pending' => 'Chờ xác nhận',
    'success' => 'Đã thanh toán',
    'failed'  => 'Thanh toán thất bại',
];
$order_status_label = $status_labels[$order['payment_status']] ?? 'Chưa xác định';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công - Fruit Farm</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/order_success.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Alert thông báo */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error   { background: #f8d7da; color: #721c24; }

        /* Thông tin khách hàng */
        .customer-info {
            background: #f4f6f8;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 15px;
            line-height: 1.6;
        }
        .customer-info p { margin-bottom: 8px; }

        /* Table tổng hợp */
        .cart-table th, .cart-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .cart-product-info { display: flex; align-items: center; gap: 15px; }
        .cart-product-info img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; }

        /* Form đánh giá */
        .review-form input,
        .review-form textarea,
        .review-form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="section-content">
    <div class="order-review-wrapper">

        <div class="cart-container">
            <div class="card-header">
                <h2>Chi tiết đơn hàng</h2>
            </div>

            <?php if ($payment_method): ?>
                <div class="alert <?= $payment_status === 'success' ? 'alert-success' : 'alert-error' ?>">
                    Thanh toán qua <strong><?= strtoupper($payment_method) ?></strong> 
                    <?= $payment_status === 'success' ? 'thành công!' : 'thất bại hoặc bị hủy.' ?>
                </div>
            <?php endif; ?>

            <p style="text-align:center; margin-bottom:10px;">Cảm ơn bạn đã mua sắm tại <strong>Fruit Farm</strong>.</p>
            <p style="text-align:center; margin-bottom:10px;">
                Mã đơn hàng: <strong><?= htmlspecialchars($order['order_code']) ?></strong>
            </p>
            <p style="text-align:center; margin-bottom:20px;">
                <strong>Trạng thái đặt hàng:</strong> <?= $order_status_label ?>
            </p>

            <div class="customer-info">
                <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                <p><strong>SĐT:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
            </div>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th class="text-center">SL</th>
                        <th class="text-right">Giá</th>
                    </tr>
                </thead>
                <tbody>

<?php
$stmtItems = $conn->prepare("
    SELECT od.*, p.image_url 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
");
$stmtItems->bind_param("i", $order_id);
$stmtItems->execute();
$items = $stmtItems->get_result();

while ($item = $items->fetch_assoc()):
?>
<tr>
    <td class="cart-product-info">
        <img src="<?= BASE_URL . $item['image_url'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
        <span><?= htmlspecialchars($item['product_name']) ?></span>
    </td>
    <td class="text-center"><?= $item['quantity'] ?></td>
    <td class="text-right"><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
</tr>
<?php endwhile; ?>

<tr class="total-row">
    <td colspan="2" style="text-align:right; font-weight:600;">Tổng thanh toán:</td>
    <td style="font-weight:700;"><?= number_format($order['total_amount'], 0, ',', '.') ?>₫</td>
</tr>
            </table>

            <a href="index.php" class="btn checkout-btn" style="margin-top:20px;">
                ⬅ Quay về trang chủ
            </a>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="review-container">
            <h2>Hãy để lại đánh giá</h2>
            <form action="submit_review.php" method="post" class="review-form">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">

                <label>Tên của bạn:</label>
                <input type="text" name="name" required>

                <label>Nội dung đánh giá:</label>
                <textarea name="feedback" rows="5" required></textarea>

                <label>Đánh giá sao:</label>
                <select name="rating">
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>

                <button class="btn checkout-btn">Gửi đánh giá</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
