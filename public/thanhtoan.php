<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once('../backend/core/config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once('../backend/core/db_connect.php'); 

$userId = $_SESSION['user_id'];
$items_to_checkout = [];
$total_price = 0;
$mode = $_GET['mode'] ?? 'cart';
$discount_amount = 0;

// ✅ Lấy sản phẩm
if ($mode === 'buy_now' && isset($_SESSION['buy_now_item'])) {
    $buyNowItem = $_SESSION['buy_now_item'];
    if (isset($buyNowItem['product_id'], $buyNowItem['quantity'])) {
        $stmt = $conn->prepare("SELECT id as product_id, name, price, image_url, stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $buyNowItem['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            if ($product['stock'] > 0) {
                $product['quantity'] = min($buyNowItem['quantity'], $product['stock']);
                $product['note'] = '';
                $items_to_checkout[] = $product;
            }
        }
        $stmt->close();
    }
    unset($_SESSION['buy_now_item']);
} else {
    $sql = "SELECT c.quantity, c.note, p.id as product_id, p.name, p.price, p.image_url, p.stock
            FROM carts c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.stock > 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] > $row['stock']) $row['quantity'] = $row['stock'];
        $items_to_checkout[] = $row;
    }
    $stmt->close();
}

// ✅ Thông tin người dùng
$stmt_user = $conn->prepare("SELECT username, phone, address FROM users WHERE id = ?");
$stmt_user->bind_param("i", $userId);
$stmt_user->execute();
$user_info = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Fruit Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/thanhtoan.css?v=<?= time(); ?>">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="checkout-page">
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a> &gt; <span>Thanh toán</span>
    </div>

    <div class="checkout-container">
        <!-- 🧍‍♂️ Thông tin giao hàng -->
        <div class="checkout-form card">
            <h2>Thông tin giao hàng</h2>
            <form id="checkoutForm" action="<?= BASE_URL ?>backend/cart/process_order.php" method="POST">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode); ?>">

                <div class="form-group">
                    <label for="fullname">Họ và tên</label>
                    <div class="input-wrapper">
                        <i class="fa fa-user"></i>
                        <input type="text" name="fullname" placeholder="Họ tên"
                               value="<?= htmlspecialchars($user_info['username'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <div class="input-wrapper">
                        <i class="fa fa-phone"></i>
                        <input type="text" name="phone" placeholder="Số điện thoại"
                               value="<?= htmlspecialchars($user_info['phone'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ giao hàng</label>
                    <div class="input-wrapper">
                        <i class="fa fa-map-marker-alt"></i>
                        <input type="text" name="address" placeholder="Địa chỉ"
                               value="<?= htmlspecialchars($user_info['address'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group voucher-code-group">
                    <label for="voucher">Mã giảm giá (Nếu có)</label>
                    <div class="input-wrapper">
                    <i class="fa fa-tag"></i>
                        <input type="text" name="voucher_code" id="voucher_code" placeholder="Nhập mã giảm giá" value="">
                        <button type="button" id="applyVoucherBtn" class="btn-apply-voucher">Áp dụng</button>
                    </div>
                        <p id="voucher_message" class="voucher-message"></p>
                        <input type="hidden" name="discount_amount" id="discount_amount_input" value="0">
                </div>  

                <!-- 💳 Phương thức thanh toán -->
                <div class="form-group payment-methods">
                    <label>Phương thức thanh toán</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select" id="paymentSelect">
                            <div class="select-selected">
                                <i class="fa fa-truck"></i>
                                <span>Thanh toán khi nhận hàng (COD)</span>
                                <i class="fa fa-chevron-down arrow"></i>
                            </div>
                            <ul class="select-items">
                                <li data-value="cod" class="active">
                                    <i class="fa fa-truck"></i> Thanh toán khi nhận hàng (COD)
                                </li>
                                <li data-value="vnpay">
                                    <i class="fa fa-credit-card"></i> Thanh toán qua VNPAY
                                </li>
                                <li data-value="momo">
                                    <i class="fa fa-mobile-alt"></i> Thanh toán qua MoMo
                                </li>
                            </ul>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment-method-input" value="cod">
                </div>
            </form>
        </div>

        <!-- 📦 Tóm tắt đơn hàng -->
        <div class="checkout-summary card">
            <h2>Đơn hàng của bạn</h2>
            <?php if (!empty($items_to_checkout)): ?>
                <table class="order-table">
                    <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>SL</th>
                        <th>Thành tiền</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items_to_checkout as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $total_price += $subtotal;
                    ?>
                        <tr>
                            <td>
                                <div class="product-info-cell">
                                    <img src="<?= BASE_URL . htmlspecialchars($item['image_url']); ?>" alt="">
                                    <span><?= htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td><?= number_format($item['price']); ?>₫</td>
                            <td><?= $item['quantity']; ?></td>
                            <td><?= number_format($subtotal); ?>₫</td>
                        </tr>
                        <?php if (!empty($item['note'])): ?>
                            <tr class="product-note-row">
                                <td colspan="4" class="product-note-display">
                                    <strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($item['note'])) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3">Tạm tính</td>
                        <td id="subtotal_display"><?= number_format($total_price); ?>₫</td>
                    </tr>
                    <tr class="discount-row">
                        <td colspan="3">Giảm giá <span id="voucher_code_applied"></span></td>
                        <td id="discount_display">- <?= number_format($discount_amount); ?>₫</td>
                    </tr>
                    <tr>
                        <td colspan="3">Phí vận chuyển</td>
                        <td id="shipping_fee_display">20,000₫</td>
                    </tr>
                    <tr class="total-row">
                        <?php 
                            $shipping_fee = 20000;
                            $final_total = max(0, $total_price - $discount_amount + $shipping_fee);
                        ?>
                        <td colspan="3"><strong>Tổng cộng</strong></td>
                        <td id="final_total_display"><strong><?= number_format($final_total); ?>₫</strong></td>
                    </tr>
                    </tfoot>
                </table>

                <button type="submit" form="checkoutForm" class="btn-submit">
                    Xác nhận đặt hàng
                </button>
            <?php else: ?>
                <p>Giỏ hàng của bạn đang trống. <a href="sanpham.php">Tiếp tục mua sắm</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<div class="toast-container" id="toast-container"></div>
<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectItems = document.querySelectorAll(".select-items li");
    const selected = document.querySelector(".select-selected span");
    const paymentInput = document.getElementById("payment-method-input");
    const checkoutForm = document.getElementById("checkoutForm");
    const confirmButton = document.querySelector(".btn-submit");

    selectItems.forEach(item => {
        item.addEventListener("click", () => {
            selectItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            selected.textContent = item.textContent.trim();
            paymentInput.value = item.dataset.value;
        });
    });

    confirmButton.addEventListener("click", function(e) {
        e.preventDefault();

        const method = paymentInput.value;
        const fullname = checkoutForm.querySelector("[name='fullname']").value.trim();
        const phone = checkoutForm.querySelector("[name='phone']").value.trim();
        const address = checkoutForm.querySelector("[name='address']").value.trim();
        const totalText = document.querySelector(".total-row td:last-child").textContent.replace(/[₫,]/g, "");
        const totalAmount = parseInt(totalText) || 0;

        if (!fullname || !phone || !address) {
            alert("Vui lòng nhập đầy đủ thông tin giao hàng!");
            return;
        }

        if (method === "cod") {
            checkoutForm.submit();
        } else if (method === "vnpay") {
            window.location.href = `${BASE_URL}public/vnpay.php?fullname=${encodeURIComponent(fullname)}&amount=${totalAmount}&phone=${encodeURIComponent(phone)}&address=${encodeURIComponent(address)}`;
        } else if (method === "momo") {
            window.location.href = `${BASE_URL}public/momo.php?fullname=${encodeURIComponent(fullname)}&amount=${totalAmount}&phone=${encodeURIComponent(phone)}&address=${encodeURIComponent(address)}`;
        }
    });
});
</script>
</body>
</html>
