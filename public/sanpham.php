<?php
session_start();
require_once(__DIR__ . '/../backend/core/config.php');
require_once(__DIR__ . '/../backend/core/db_connect.php'); // <<< BƯỚC 1: Đảm bảo kết nối DB ($conn)

// Lấy filter từ query string
$selectedCategory = $_GET['category'] ?? 'all';
$priceFilter = $_GET['price'] ?? 'all';

// =======================================================
// <<< BƯỚC 2: LẤY DANH MỤC TỪ DB CHO THANH LỌC >>>
// =======================================================
$categories_list = [];
$stmt_cat = $conn->prepare("SELECT name, slug FROM categories ORDER BY name ASC");
$stmt_cat->execute();
$result_cat = $stmt_cat->get_result();
while ($row = $result_cat->fetch_assoc()) {
    $categories_list[] = $row;
}
$stmt_cat->close();

// Query products + join categories
$sql = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
";

// Lọc theo category slug
if ($selectedCategory !== 'all') {
    $sql .= " AND c.slug = '" . $conn->real_escape_string($selectedCategory) . "' ";
}

// Lọc theo giá
switch ($priceFilter) {
    case '0-100':
        $sql .= " AND p.price <= 100000 ";
        break;
    case '100-300':
        $sql .= " AND p.price BETWEEN 100000 AND 300000 ";
        break;
    case '300-500':
        $sql .= " AND p.price BETWEEN 300000 AND 500000 ";
        break;
    case '500+':
        $sql .= " AND p.price >= 500000 ";
        break;
}

// Sắp xếp theo thời gian tạo
$sql .= " ORDER BY p.created_at DESC ";

$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error);
}

$products = $result->fetch_all(MYSQLI_ASSOC);
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
        <button class="category-btn <?= $selectedCategory === 'all' ? 'active' : '' ?>" data-category="all">
            <i class="fa-solid fa-list"></i> Tất cả
            <i class="fa-solid fa-chevron-down caret"></i>
        </button>
        <div class="dropdown-menu">
            <button data-category="all" <?= $selectedCategory === 'all' ? 'class="active"' : '' ?>>Tất cả sản phẩm</button>
            
            <?php foreach ($categories_list as $cat): ?>
                <button data-category="<?= htmlspecialchars($cat['slug']) ?>" 
                        <?= $selectedCategory === $cat['slug'] ? 'class="active"' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php 
    $i = 0;
    foreach ($categories_list as $cat): 
        if ($i++ >= 6) break; // Chỉ hiển thị tối đa 6 nút
    ?>
        <button class="category-btn <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>" data-category="<?= htmlspecialchars($cat['slug']) ?>">
            <?= htmlspecialchars($cat['name']) ?>
        </button>
    <?php endforeach; ?>
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
