<?php
// Lấy dữ liệu giỏ hàng từ session, nếu chưa có thì tạo mảng rỗng
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
?>

<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giỏ hàng - Fruit Farm</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/giohang.css" />

  </head>
  <body>
    <?php include 'header.php'; ?>

    <main class="section-content">
      <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        &gt;
        <span>Giỏ hàng của bạn</span>
      </div>

      <div class="cart-container">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Sản phẩm</th>
              <th>Thông tin sản phẩm</th>
              <th>Đơn giá</th>
              <th>Số lượng</th>
              <th>Thành tiền</th>
              <th>Xóa</th>
            </tr>
          </thead>
         <tbody id="cart-body">
            <?php
            // Kiểm tra xem giỏ hàng có rỗng không
            if (!empty($cart)) :
                $grandTotal = 0; // Biến để tính tổng tiền
                // Lặp qua từng sản phẩm trong giỏ hàng
                foreach ($cart as $productId => $item) :
                    // Tính thành tiền cho mỗi sản phẩm
                    $subtotal = $item['price'] * $item['quantity'];
                    // Cộng dồn vào tổng tiền
                    $grandTotal += $subtotal;
            ?>
            <tr>
              <form action="update_cart.php" method="POST">
                <td>
                    <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-product-img" />
                </td>
                <td>
                    <?php echo htmlspecialchars($item['name']); ?>
                </td>
                <td><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                <td>
                    <div class="quantity-control">
                        <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" min="1" class="quantity-input" />
                        
                        <input type="hidden" name="id" value="<?php echo $productId; ?>" />

                        <button type="submit" class="btn-update">Cập nhật</button>
                    </div>
                </td>
                <td class="total-price"><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</td>
                <td>
                    <a href="remove_from_cart.php?id=<?php echo $productId; ?>" class="remove-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');"><i class="fa fa-trash"></i></a>
                </td>
              </form>
            </tr>
            <?php
                endforeach;
            else :
                // Nếu giỏ hàng trống, hiển thị thông báo
            ?>
            <tr>
                <td colspan="6" style="text-align: center;">🛒 Giỏ hàng của bạn đang trống!</td>
            </tr>
            <?php
            endif;
            ?>
        </tbody>
        </table>

        <div class="cart-actions">
          <div class="cart-notes">
            <textarea placeholder="Ghi chú đơn hàng"></textarea>
          </div>
          <div class="cart-summary">
            <p>
              Tổng tiền:
              <span id="grand-total">
                  <?php
                  // Nếu giỏ hàng không trống thì hiển thị tổng tiền, ngược lại hiển thị 0
                  echo isset($grandTotal) ? number_format($grandTotal, 0, ',', '.') . '₫' : '0₫';
                  ?>
              </span>
            </p>
            <div class="cart-buttons">
              <a href="sanpham.php" class="btn continue-btn">
                Tiếp tục mua hàng
              </a>
              <a href="thanhtoan.php" class="btn checkout-btn">
                Tiến hành đặt hàng
              </a>
            </div>
          </div>
        </div>
      </div>

      <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
      </button>
      
      <!-- Footer section -->
      <footer class="footer-section">
        <div class="section-content">
          <p class="copyright-text">@ Fruit Farm</p>

          <div class="social-link-list">
            <a href="https://www.facebook.com/" class="social-link">
              <i class="fa-brands fa-facebook"></i>
            </a>

            <a href="#" class="social-link">
              <i class="fa-brands fa-instagram"></i>
            </a>

            <a href="#" class="social-link">
              <i class="fa-brands fa-youtube"></i>
            </a>
          </div>

          <p class="policy-text">
            <a href="#" class="policy-link">Privacy policy</a>
            <span class="separator">*</span>
            <a href="#" class="policy-link">Refund policy</a>
          </p>
        </div>
      </footer>
    </main>
    
    <script src="js/script.js"></script>
  </body>
</html>