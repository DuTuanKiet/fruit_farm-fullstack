<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thanh toán - Fruit Farm</title>
    <!-- Link font awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght, FILL, GRAD@48,400,0,0"
    />
    <!-- Link Swiper's CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link rel="stylesheet" href="/fruitfarm/css/style.css" />
    <link rel="stylesheet" href="/fruitfarm/css/thanhtoan.css" />
  </head>
  <body>
<?php 
include 'header.php'; 
require_once 'php/db_connect.php';

// Xác định chế độ thanh toán
$mode = $_GET['mode'] ?? 'cart'; // Mặc định là từ giỏ hàng
$items_to_checkout = [];
$total_price = 0;

// --- Lấy danh sách sản phẩm cần thanh toán ---
if ($mode === 'buy_now' && isset($_SESSION['buy_now_item'])) {
    // TRƯỜNG HỢP 1: MUA NGAY
    $item = $_SESSION['buy_now_item'];
    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $item['product_id']);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        $product['quantity'] = $item['quantity'];
        $items_to_checkout[] = $product;
    }
} else {
    // TRƯỜNG HỢP 2: THANH TOÁN TỪ GIỎ HÀNG
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));

        $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$product_ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($product = $result->fetch_assoc()) {
            $product['quantity'] = $_SESSION['cart'][$product['id']];
            $items_to_checkout[] = $product;
        }
    }
}
?>
<main class="section-content checkout-page">
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a> &gt; <span>Thanh toán</span>
    </div>

    <div class="checkout-container">
        <div class="checkout-form card">
    <h2>Thông tin giao hàng</h2>
    <form id="checkoutForm" action="process_order.php" method="POST">
        <div class="form-group">
            <label for="fullname">Họ và tên</label>
            <div class="input-wrapper">
                <i class="fa fa-user"></i>
                <input type="text" id="fullname" name="fullname" placeholder="Nhập họ tên đầy đủ của bạn" required>
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại</label>
            <div class="input-wrapper">
                <i class="fa fa-phone"></i>
                <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại nhận hàng" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email (Không bắt buộc)</label>
            <div class="input-wrapper">
                <i class="fa fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="Để nhận thông tin đơn hàng">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ giao hàng</label>
            <div class="input-wrapper">
                <i class="fa fa-map-marker-alt"></i>
                <textarea id="address" name="address" rows="3" placeholder="Nhập địa chỉ chi tiết (số nhà, tên đường, phường/xã,...)" required></textarea>
            </div>
        </div>
        
        <div class="form-group">
            <label for="payment_method">Phương thức thanh toán</label>
            <div class="input-wrapper">
                <i class="fa fa-credit-card"></i>
                <select id="payment_method" name="payment_method">
                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    <option value="momo">Ví điện tử Momo</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Ghi chú (Không bắt buộc)</label>
            <div class="input-wrapper">
                 <i class="fa fa-pencil-alt"></i>
                <textarea id="notes" name="notes" rows="2" placeholder="Thêm ghi chú cho người giao hàng..."></textarea>
            </div>
        </div>
    </form>
</div>

        <div class="checkout-summary card">
            <h2>Đơn hàng của bạn</h2>
            <?php if (!empty($items_to_checkout)): ?>
                <ul class="order-list">
                    <?php foreach ($items_to_checkout as $item): ?>
                        <li>
                            <?php echo htmlspecialchars($item['name']); ?> 
                            x <strong><?php echo $item['quantity']; ?></strong>
                            <span><?php echo number_format($item['price'] * $item['quantity']); ?>₫</span>
                        </li>
                        <?php $total_price += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="order-meta">
                    <p>Phí vận chuyển: <span>20,000₫</span></p>
                    <p class="total">
                        Tổng cộng:
                        <span><?php echo number_format($total_price + 20000); ?>₫</span>
                    </p>
                </div>
                <button class="btn-submit">Xác nhận đặt hàng</button>
            <?php else: ?>
                <p>Không có sản phẩm nào để thanh toán.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>
    
  </body>
</html>
