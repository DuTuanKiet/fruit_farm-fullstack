<?php
// Bỏ toàn bộ phần truy vấn database và xuất JSON, chỉ giữ lại phần HTML và thiết lập ban đầu

// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Vẫn cần các file config để sử dụng BASE_URL trong HTML
require_once(__DIR__ . '/../backend/core/config.php');
// Không cần db_connect.php vì không truy vấn DB ở đây nữa.

// --- Thiết lập mặc định (cần để sử dụng trong HTML count) ---
$products = []; // Gán rỗng vì data sẽ được tải bằng JS
$priceFilter = $_GET['price'] ?? 'all';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
// Bỏ qua các biến phân trang và truy vấn database

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sản phẩm - Fruit Farm</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/sanpham.css">
  <style>
    .out-of-stock { 
      opacity: 0.5; 
      pointer-events: none; 
      cursor: not-allowed; 
    }
    .btn-disabled { 
      background: #ccc; 
      color: #666; 
      border: 1px solid #999; 
      cursor: not-allowed; 
    }
    .product-stock { 
      font-size: 14px; 
      color: #555; 
      margin-bottom: 5px; 
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
  <section class="product-section">
    <!-- Thanh danh mục sản phẩm -->
<div class="category-bar">
  <div class="category-dropdown">
    <button class="category-btn active" data-category="all">
      <i class="fa-solid fa-list"></i> Tất cả
      <i class="fa-solid fa-chevron-down caret"></i>
    </button>
    <div class="dropdown-menu">
      <button data-category="all">Tất cả sản phẩm</button> <button data-category="trai-tuoi">Trái cây tươi</button>
      <button data-category="saykho">Trái cây sấy khô</button>
      <button data-category="nhapkhau">Trái cây nhập khẩu</button>
      <button data-category="dac-san">Đặc sản vùng miền</button>
      <button data-category="hienmua">Theo mùa hiện tại</button>
    </div>
  </div>

  <button class="category-btn" data-category="nhietdoi">🥭 Nhiệt đới</button>
  <button class="category-btn" data-category="camquyt">🍊 Có múi</button>
  <button class="category-btn" data-category="nhapkhau">🍎 Nhập khẩu</button>
  <button class="category-btn" data-category="dac-san">🌾 Đặc sản Việt</button>
  <button class="category-btn" data-category="saykho">🍌 Sấy khô</button>
  <button class="category-btn" data-category="berry">🍓 Dâu & Berry</button>
</div>

    <h2 class="section-title">Sản phẩm chúng tôi</h2>
    <!-- thanh công cụ lọc giá -->
<div class="product-controls">
  <div class="product-count">Hiển thị <?= count($products) ?> sản phẩm</div>
  <div class="filter-options">
    <label for="price-filter">Lọc theo giá:</label>
    <div class="select-wrapper">
      <select name="price-filter" id="price-filter">
        <option value="all">Tất cả</option>
        <option value="0-100">Dưới 100.000đ</option>
        <option value="100-300">100.000đ - 300.000đ</option>
        <option value="300-500">300.000đ - 500.000đ</option>
        <option value="500+">Trên 500.000đ</option>
      </select>
    </div>
  </div>
</div>

    <div class="product-list" id="product-list">
      <?php foreach ($products as $product):
          $is_out_of_stock = $product['stock'] <= 0;
      ?>
      <div class="product-card-container <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
          <a href="chitietsp.php?id=<?= $product['id'] ?>" class="product-image-link">
              <div class="product-image-wrapper">
                  <img src="<?= BASE_URL . htmlspecialchars($product['image_url'] ?: 'public/assets/images/no-image.png') ?>" 
                       alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
              </div>
          </a>
          <div class="product-info">
              <h3 class="product-name">
                  <a href="chitietsp.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a>
              </h3>
              <p class="product-price"><?= number_format($product['price'], 0, ',', '.') ?>₫</p>
              <p class="product-stock">Tồn kho: <?= $product['stock'] ?></p>
              <div class="product-actions">
                  <?php if ($is_out_of_stock): ?>
                      <button class="add-to-cart-btn btn-disabled" disabled>
                          <i class="fa fa-shopping-cart"></i> Hết hàng
                      </button>
                      <button class="buy-now-btn btn-disabled" disabled>Mua ngay</button>
                  <?php else: ?>
                      <button class="add-to-cart-btn btn-sm requires-login" 
                              data-id="<?= $product['id']; ?>" 
                              data-stock="<?= $product['stock']; ?>">
                          <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                      </button>
                      <button class="buy-now-btn btn-sm requires-login" 
                              data-id="<?= $product['id']; ?>" 
                              data-stock="<?= $product['stock']; ?>">
                          Mua Ngay
                      </button>
                  <?php endif; ?>
              </div>
          </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
      <div class="pagination"></div>
    </div>
  </section>

  <button id="backToTop" class="back-to-top">
    <i class="fas fa-arrow-up"></i>
  </button>
</main>

<div class="toast-container" id="toast-container"></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
