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

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// --- Xử lý phản hồi thanh toán (VNPAY / MoMo) ---
$payment_status = 'pending';
$payment_method = null;

// Kiểm tra phản hồi từ VNPAY
if (isset($_GET['vnp_ResponseCode'])) {
    $payment_method = 'vnpay';
    if ($_GET['vnp_ResponseCode'] == '00') {
        $payment_status = 'success';
    } else {
        $payment_status = 'failed';
    }
}

// Kiểm tra phản hồi từ MoMo
elseif (isset($_GET['resultCode'])) {
    $payment_method = 'momo';
    if ($_GET['resultCode'] == '0') {
        $payment_status = 'success';
    } else {
        $payment_status = 'failed';
    }
}

// Cập nhật trạng thái đơn hàng nếu có phản hồi thanh toán
if ($payment_method && $payment_status !== 'pending') {
    $stmt = $conn->prepare("UPDATE orders SET payment_method = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $payment_method, $payment_status, $order_id);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt hàng thành công - Fruit Farm</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/order_success.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="section-content">
    <div class="order-review-wrapper">
        <!-- Bảng thông tin đơn hàng -->
        <div class="cart-container">
            <div class="card-header">
                <?php if ($payment_method): ?>
  <div style="text-align:center; margin-bottom:15px;">
    <?php if ($payment_status === 'success'): ?>
      <p style="color:green; font-weight:600;">
        Thanh toán qua <strong><?= strtoupper($payment_method) ?></strong> thành công!
      </p>
    <?php else: ?>
      <p style="color:red; font-weight:600;">
        Thanh toán qua <strong><?= strtoupper($payment_method) ?></strong> thất bại hoặc bị hủy.
      </p>
    <?php endif; ?>
  </div>
<?php endif; ?>
            </div>
            <p style="text-align:center; margin-bottom:15px;">
                Cảm ơn bạn đã mua sắm tại <strong>Fruit Farm</strong>.
            </p>
            <p style="text-align:center; margin-bottom:20px;">
                Mã đơn hàng của bạn: <strong>#<?= $order_id ?></strong>
            </p>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th class="col-product">Sản phẩm</th>
                        <th class="col-quantity">Số lượng</th>
                        <th class="col-price">Giá</th>
                    </tr>
                </thead>
                <tbody>
<?php
$items = $conn->query("
    SELECT od.*, p.image_url 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = $order_id
");

$calculated_sub_total = 0;
$order_items = [];

while ($item = $items->fetch_assoc()) {
    $order_items[] = $item;
    $calculated_sub_total += $item['price'] * $item['quantity'];
}

foreach ($order_items as $item):
?>
<tr>
    <td class="cart-product-info">
        <img src="<?= BASE_URL . $item['image_url'] ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
        <span class="product-name"><?= $item['product_name'] ?></span>
    </td>
    <td class="text-center"><?= $item['quantity'] ?></td>
    <td class="text-right"><?= number_format($item['price'],0,",",".") ?>₫</td>
</tr>
<?php endforeach; ?>

                </tbody>
                <!-- Hàng tổng tiền -->
<tr class="total-row">
    <td colspan="2" style="text-align:right; font-weight:600; font-size:1rem;">
        Tổng tiền đơn hàng:
    </td>
    <td style="font-weight:700; color:#2c3333; font-size:1.1rem;">
        <?= number_format($calculated_sub_total, 0, ',', '.') ?>₫
    </td>
</tr>
            </table>

            <a href="index.php" class="btn checkout-btn" style="margin-top:20px;">⬅ Quay về trang chủ</a>
        </div>

        <!-- Bảng đánh giá -->
        <div class="cart-actions review-container">
            <h2 style="margin-bottom:20px;">Hãy để lại đánh giá</h2>
            <form action="submit_review.php" method="post" class="review-form">
                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? 0 ?>">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">

                <label for="name">Tên của bạn:</label>
                <input type="text" id="name" name="name" required>

                <label for="feedback">Nội dung đánh giá:</label>
                <textarea id="feedback" name="feedback" rows="5" required></textarea>

                <label for="rating">Đánh giá sao:</label>
                <select id="rating" name="rating">
                    <option value="5">⭐⭐⭐⭐⭐</option>
                    <option value="4">⭐⭐⭐⭐</option>
                    <option value="3">⭐⭐⭐</option>
                    <option value="2">⭐⭐</option>
                    <option value="1">⭐</option>
                </select>

                <button type="submit" class="btn checkout-btn" style="margin-top:15px;">Gửi đánh giá</button>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
