<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giỏ hàng - Fruit Farm</title>
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
    <!-- CSS -->
    <link rel="stylesheet" href="/fruitfarm/css/style.css" />
    <link rel="stylesheet" href="/fruitfarm/css/giohang.css" />
  </head>
  <body>
    <?php include 'header.php'; ?>

    <main class="section-content">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        &gt;
        <span>Giỏ hàng của bạn</span>
      </div>

      <!-- Cart Table -->
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
            <tr>
              <td>
                <img
                  src="images/5.jpg"
                  alt="Dâu Mỹ Montery"
                  class="cart-product-img"
                />
              </td>
              <td>
                Dâu Mỹ Montery - hộp 500gr
                <br />
                <span style="color: red">(Hộp 500gr)</span>
              </td>
              <td>180,000₫</td>
              <td>
                <div class="quantity-control">
                  <button class="decrease">-</button>
                  <input type="number" value="1" min="1" />
                  <button class="increase">+</button>
                </div>
              </td>
              <td class="total-price">180,000₫</td>
              <td>
                <button class="remove-btn"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Cart Actions -->
        <div class="cart-actions">
          <div class="cart-notes">
            <textarea placeholder="Ghi chú đơn hàng"></textarea>
          </div>
          <div class="cart-summary">
            <p>
              Tổng tiền:
              <span id="grand-total">180,000₫</span>
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

      <!-- Back to Top Button -->
      <button id="backToTop" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
      </button>
    </main>
    <!--Link Swiper script-->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="/fruitfarm/js/script.js"></script>
  </body>
</html>
