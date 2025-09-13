<?php 
include 'header.php'; // Gọi header chung
require_once 'php/db_connect.php'; // Kết nối CSDL

// Lấy ID từ URL một cách an toàn
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id > 0) {
    // Chuẩn bị câu lệnh để lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        // Hiển thị thông tin sản phẩm
        ?>

<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chi tiết sản phẩm - Fruit Farm</title>
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
    <link rel="stylesheet" href="/fruitfarm/css/chitietsp.css"> 
  </head>
  <body>
    
        <main class="section-content">
    <section class="product-hero">
        <div class="product-hero__image">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-hero__info">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p class="product-sku">Mã sản phẩm: FF-<?php echo $product['id']; ?></p>
            <p class="price">
                <?php echo number_format($product['price']); ?>₫
                <span class="old"><?php echo number_format($product['price'] * 1.2); ?>₫</span> 
            </p>
            <p class="short-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div class="actions">
                <div class="quantity-box">
                    <input type="number" value="1" min="1" />
                </div>
                <a href="#" class="btn add-cart" data-id="<?php echo $product['id']; ?>">🛒 Thêm vào giỏ</a>
               <button class="btn buy-now-btn" data-id="<?php echo $product['id']; ?>">Mua ngay</button>
            </div>
            
             <div class="service-info">
              <div class="service-item">
                <i class="fa fa-truck"></i>
                Giao Hàng Nội Thành 2-4 Giờ
              </div>
              <div class="service-item">
                <i class="fa fa-box-open"></i>
                Kiểm Tra Nhận Hàng
              </div>
            </div>
        </div>
    </section>

    </main>
        <?php
    } else {
        echo "<p>Không tìm thấy sản phẩm.</p>";
    }
} else {
    echo "<p>ID sản phẩm không hợp lệ.</p>";
}

include 'footer.php'; // Gọi footer chung
?>
    
</html>
