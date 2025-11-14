  <?php
  require_once __DIR__ . '/../backend/core/config.php';
  if (session_status() === PHP_SESSION_NONE) session_start();
  require_once __DIR__ . '/../backend/core/db_connect.php';

  // Kiểm tra user login
  if (!isset($_SESSION['user_id'])) {
      $cart = []; // Chưa login → giỏ trống
  } else {
      $user_id = $_SESSION['user_id'];
      
      // Lấy giỏ hàng từ DB
$stmt = $conn->prepare("
    SELECT c.product_id, c.quantity, c.note, p.name, p.price, p.image_url, p.stock
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[$row['product_id']] = [
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image_url' => $row['image_url'],
        'note' => (string)($row['note'] ?? ''),
        'stock' => $row['stock']
    ];
}
  }

  // Tính tổng tiền
$subtotal = 0;
foreach ($cart as $item) {
    if (intval($item['stock']) > 0) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}
  $shipping = 20000;
  $grandTotal = $subtotal + $shipping;
  ?>


  <!DOCTYPE html>
  <html lang="vi">
  <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Giỏ hàng - Fruit Farm</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
      <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/giohang.css?v=<?= time(); ?>" />
  </head>
  <body>
      <?php include __DIR__ . '/includes/header.php'; ?>

      <main class="section-content">
          <!-- Breadcrumb -->
          <div class="breadcrumb">
              <a href="<?= BASE_URL ?>public/index.php">Trang chủ</a> &gt;
              <span>Giỏ hàng của bạn</span>
          </div>

          <!-- CỘT TRÁI: GIỎ HÀNG -->
          <section class="cart-container">
              <div class="card-header">
                  <h2>Giỏ hàng của bạn</h2>
                  <a href="sanpham.php" class="continue-shopping-link">
                      Tiếp tục mua sắm <i class="fa fa-arrow-right"></i>
                  </a>
              </div>

              <table class="cart-table">
                  <thead>
                      <tr>
                          <td colspan="2">Sản phẩm</td>
                          <td class="col-note">Ghi chú</td>
                          <td class="col-price">Giá</td>
                          <td class="col-qty">Số lượng</td>
                          <td class="col-total">Tổng</td>
                          <td class="col-remove">Xóa</td>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (!empty($cart)): ?>
                          <?php foreach ($cart as $productId => $item): ?>
    <?php $isOutOfStock = intval($item['stock']) <= 0; ?>
    <tr class="<?= $isOutOfStock ? 'cart-item-disabled' : '' ?>">
        <td class="cart-image">
            <img src="<?= BASE_URL . htmlspecialchars($item['image_url']); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
        </td>

        <td class="cart-product-name">
            <?= htmlspecialchars($item['name']); ?>
            <?php if ($isOutOfStock): ?>
                <span class="out-of-stock-label">(Hết hàng)</span>
            <?php endif; ?>
        </td>

        <td class="cart-note">
            <?php if (!$isOutOfStock): ?>
                <textarea class="item-note-input" 
                          data-id="<?= htmlspecialchars($productId); ?>" 
                          placeholder="Ghi chú (255 ký tự)..." 
                          maxlength="255"><?= htmlspecialchars($item['note'] ?? ''); ?></textarea>
            <?php else: ?>
                <div class="quantity-disabled">Không khả dụng</div>
            <?php endif; ?>
        </td>

        <td class="cart-price"><?= number_format($item['price']); ?>₫</td>

        <td class="cart-quantity">
            <?php if (!$isOutOfStock): ?>
                <div class="quantity-control">
                    <button class="decrease-btn" data-id="<?= htmlspecialchars($productId); ?>">-</button>
                    <input type="number" value="<?= htmlspecialchars($item['quantity']); ?>" min="1" class="quantity-input" data-id="<?= htmlspecialchars($productId); ?>" />
                    <button class="increase-btn" data-id="<?= htmlspecialchars($productId); ?>">+</button>
                </div>
            <?php else: ?>
                <div class="quantity-disabled"><?= htmlspecialchars($item['quantity']); ?></div>
            <?php endif; ?>
        </td>

        <td class="cart-subtotal"><?= number_format($item['price'] * $item['quantity']); ?>₫</td>

        <td class="cart-remove">
            <button class="remove-btn" data-id="<?= htmlspecialchars($productId); ?>"><i class="fa fa-trash"></i></button>
        </td>
    </tr>
<?php endforeach; ?>
                      <?php else: ?>
                          <tr>
                              <td colspan="7" class="cart-empty-message">Giỏ hàng của bạn đang trống.</td>
                          </tr>
                      <?php endif; ?>
                  </tbody>
              </table>
          </section>

          <!-- CỘT PHẢI: GHI CHÚ & TÓM TẮT -->
          <aside class="cart-actions">
              <div class="cart-summary">
                  <h2>Tóm tắt đơn hàng</h2>
                  <div class="summary-row">
                      <span>Tạm tính</span>
                      <span id="subtotal-amount"><?= number_format($subtotal); ?>₫</span>
                  </div>
                  <div class="summary-row">
                      <span>Phí vận chuyển</span>
                      <span><?= number_format($shipping); ?>₫</span>
                  </div>
                  <div class="summary-total-row">
                      <span>Tổng cộng</span>
                      <span id="grand-total"><?= number_format($grandTotal); ?>₫</span>
                  </div>
                  <div class="cart-buttons">
                      <a href="<?= BASE_URL ?>public/thanhtoan.php" id="checkout-btn" class="btn checkout-btn requires-login">
                        Tiến hành thanh toán
                        </a>
                  </div>
              </div>
          </aside>
      </main>

      <div class="toast-container" id="toast-container"></div>
      <?php include __DIR__ . '/includes/footer.php'; ?>
  </body>
  </html>
