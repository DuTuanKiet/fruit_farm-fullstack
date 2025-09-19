<?php
require_once 'php/db_connect.php'; // Kết nối CSDL

// --- PHẦN 1: LOGIC LẤY DỮ LIỆU ---

// Lấy ID từ URL một cách an toàn
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$related_products = [];

if ($product_id > 0) {
    // 1. Lấy thông tin sản phẩm chính
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    $stmt->close();

    // 2. Lấy 4 sản phẩm liên quan (mới nhất, trừ sản phẩm hiện tại)
    if ($product) {
        $stmt_related = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE id != ? ORDER BY id DESC LIMIT 4");
        $stmt_related->bind_param("i", $product_id);
        $stmt_related->execute();
        $related_result = $stmt_related->get_result();
        $related_products = $related_result->fetch_all(MYSQLI_ASSOC);
        $stmt_related->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $product ? htmlspecialchars($product['name']) : 'Không tìm thấy sản phẩm'; ?> - Fruit Farm</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/chitietsp.css"> 
</head>
<body>

<?php include 'header.php'; // Gọi header chung ?>

<main class="product-detail-page">
    <?php if ($product): // Nếu tìm thấy sản phẩm, hiển thị thông tin ?>
    
    <section class="product-hero">
        <div class="product-image-gallery">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <nav class="breadcrumb">
                <a href="index.php">Trang chủ</a> <i class="fa fa-angle-right"></i>
                <a href="sanpham.php">Sản phẩm</a> <i class="fa fa-angle-right"></i>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">
                <span class="current-price"><?php echo number_format($product['price']); ?>₫</span>
                <span class="old-price"><?php echo number_format($product['price'] * 1.2); ?>₫</span> 
            </div>
            <p class="product-short-desc"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div class="product-actions">
    <div class="quantity-selector">
        <button class="quantity-btn minus">-</button>
        <input type="number" class="quantity-input" value="1" min="1" />
        <button class="quantity-btn plus">+</button>
    </div>
    <button class="btn add-to-cart-btn" data-id="<?php echo $product['id']; ?>">
        <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
    </button>
</div>
<button class="btn buy-now-btn" data-id="<?php echo $product['id']; ?>">Mua ngay</button>
            
            <div class="service-info-box">
              <div class="service-item"><i class="fa fa-truck-fast"></i> Giao hàng nội thành 2-4 giờ</div>
              <div class="service-item"><i class="fa fa-box-open"></i> Kiểm tra hàng trước khi thanh toán</div>
            </div>
        </div>
    </section>

    <?php if (!empty($related_products)): ?>
    <section class="related-products">
        <h2 class="section-title">Sản phẩm liên quan</h2>
        <div class="related-products-list">
            <?php foreach ($related_products as $related_item): ?>
            <a href="chitietsp.php?id=<?php echo $related_item['id']; ?>" class="product-card">
                <div class="card-image">
                    <img src="<?php echo htmlspecialchars($related_item['image_url']); ?>" alt="<?php echo htmlspecialchars($related_item['name']); ?>">
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?php echo htmlspecialchars($related_item['name']); ?></h3>
                    <p class="card-price"><?php echo number_format($related_item['price']); ?>₫</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php else: // Nếu KHÔNG tìm thấy sản phẩm, hiển thị thông báo lỗi ?>
    <section class="product-not-found">
        <i class="far fa-frown"></i>
        <h2>Rất tiếc, không tìm thấy sản phẩm bạn yêu cầu.</h2>
        <p>Sản phẩm có thể đã bị xóa hoặc URL không đúng. Vui lòng thử lại.</p>
        <a href="index.php" class="btn">Quay về Trang chủ</a>
    </section>
    <?php endif; ?>
</main>

<?php include 'footer.php'; // Gọi footer chung ?>

<script>
// Thêm một chút Javascript để nút tăng giảm số lượng hoạt động
document.addEventListener('DOMContentLoaded', function() {
    const quantitySelector = document.querySelector('.quantity-selector');
    if (quantitySelector) {
        const input = quantitySelector.querySelector('.quantity-input');
        const minusBtn = quantitySelector.querySelector('.minus');
        const plusBtn = quantitySelector.querySelector('.plus');

        minusBtn.addEventListener('click', function() {
            let currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', function() {
            let currentValue = parseInt(input.value);
            input.value = currentValue + 1;
        });
    }
});
</script>

</body>
</html>