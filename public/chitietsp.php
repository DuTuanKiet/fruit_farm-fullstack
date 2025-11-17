<?php
require_once __DIR__ . '/../backend/core/config.php';
require_once __DIR__ . '/../backend/core/db_connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy id sản phẩm từ URL
if (!isset($_GET['id'])) die("Không tìm thấy sản phẩm");
$productId = intval($_GET['id']);

// Nếu người dùng đã đăng nhập thì lưu user_id, ngược lại để null
$userId = $_SESSION['user_id'] ?? null;

// Ghi nhận lượt xem vào bảng product_views
$stmt_view = $conn->prepare("INSERT INTO product_views (product_id, user_id) VALUES (?, ?)");
$stmt_view->bind_param("ii", $productId, $userId);
$stmt_view->execute();

// Lấy chi tiết sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) die("Sản phẩm không tồn tại");

$product = $result->fetch_assoc();
$stock = intval($product['stock'] ?? 0); // Lấy tồn kho
$is_out_of_stock = $stock <= 0;

// Lấy sản phẩm liên quan (ngẫu nhiên 4 sản phẩm khác)
$stmt_related = $conn->prepare("
    SELECT * FROM products 
    WHERE id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt_related->bind_param("i", $productId);
$stmt_related->execute();
$relatedProducts = $stmt_related->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($product['name']); ?> - Fruit Farm</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/chitietsp.css" /> 
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="product-detail-page">
    <?php if ($product): ?>
    <section class="product-hero <?= $is_out_of_stock ? 'out-of-stock' : '' ?>">
        <div class="product-image-gallery">
            <img src="<?= BASE_URL ?><?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <nav class="breadcrumb">
                <a href="<?= BASE_URL ?>public/index.php">Trang chủ</a> <i class="fa fa-angle-right"></i>
                <a href="<?= BASE_URL ?>public/sanpham.php">Sản phẩm</a> <i class="fa fa-angle-right"></i>
                <span><?= htmlspecialchars($product['name']); ?></span>
            </nav>
            <h1 class="product-title"><?= htmlspecialchars($product['name']); ?></h1>

            <?php if ($is_out_of_stock): ?>
                <div class="stock-notice">Sản phẩm đã hết hàng</div>
            <?php endif; ?>

            <div class="product-price">
                <span class="current-price"><?= number_format($product['price']); ?>₫</span>
                <span class="old-price"><?= number_format($product['price'] * 1.2); ?>₫</span> 
            </div>
            <p class="product-short-desc"><?= nl2br(htmlspecialchars($product['description'])); ?></p>
            
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn minus" <?= $is_out_of_stock ? 'disabled' : '' ?>>-</button>
                    <input type="number" class="quantity-input" value="1" min="1" <?= $is_out_of_stock ? 'disabled' : '' ?> />
                    <button class="quantity-btn plus" <?= $is_out_of_stock ? 'disabled' : '' ?>>+</button>
                </div>
                <button class="btn add-to-cart-btn <?= $is_out_of_stock ? 'btn-disabled' : '' ?>" 
                    data-id="<?= $product['id']; ?>" data-stock="<?= $stock ?>" <?= $is_out_of_stock ? 'disabled' : '' ?>>
                    <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                </button>
            </div>
                <button class="btn buy-now-btn <?= $is_out_of_stock ? 'btn-disabled' : '' ?>" 
                    data-id="<?= $product['id']; ?>" data-stock="<?= $stock ?>" <?= $is_out_of_stock ? 'disabled' : '' ?>>
                    Mua ngay
                </button>
        </div>
        <div class="service-info-box">
                <div class="service-item"><i class="fa fa-truck-fast"></i> Giao hàng nội thành 2–4h</div>
                <div class="service-item"><i class="fa fa-box-open"></i> Kiểm tra hàng trước khi thanh toán</div>
                <div class="service-item"><i class="fa fa-rotate-left"></i> Đổi trả miễn phí trong 2h nếu sản phẩm lỗi</div>
                <div class="service-item"><i class="fa fa-phone"></i> Hotline: 0819.767.357 (Zalo)</div>
                <div class="service-item"><i class="fa fa-receipt"></i> Xuất hóa đơn VAT theo yêu cầu</div>
        </div>
    </section>

    <?php if (!empty($product['details'])): ?>
    <section class="product-full-details">
        <div class="container">
            <h2 class="section-title">Chi Tiết Sản Phẩm & Giới Thiệu</h2>
            
            <div class="read-more-container">
                <div class="details-content" id="productDetailsContent">
                    <?= nl2br(htmlspecialchars($product['details'])) ?>
                </div>
                <div class="content-fade-mask"></div>
                <button class="read-more-btn" id="readMoreBtn">
                    Xem thêm <i class="fa fa-angle-down"></i>
                </button>
            </div>
            </div>
    </section>
<?php endif; ?>

    <?php if (!empty($relatedProducts)): ?>
    <section class="related-products">
        <h2 class="section-title">Các Sản Phẩm Liên Quan Khác</h2>
        <div class="related-products-list">
            <?php foreach ($relatedProducts as $related_item): ?>
            <a href="<?= BASE_URL ?>public/chitietsp.php?id=<?= $related_item['id']; ?>" class="product-card">
                <div class="card-image"><img src="<?= BASE_URL ?><?= htmlspecialchars($related_item['image_url']); ?>" alt="<?= htmlspecialchars($related_item['name']); ?>"></div>
                <div class="card-content">
                    <h3><?= htmlspecialchars($related_item['name']); ?></h3>
                    <p class="card-price"><?= number_format($related_item['price']); ?>₫</p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php else: ?>
    <section class="product-not-found">
        <i class="far fa-frown"></i>
        <h2>Rất tiếc, không tìm thấy sản phẩm bạn yêu cầu.</h2>
        <p>Sản phẩm có thể đã bị xóa hoặc URL không đúng. Vui lòng thử lại.</p>
        <a href="<?= BASE_URL ?>public/index.php" class="btn">Quay về Trang chủ</a>
    </section>
    <?php endif; ?>
</main>

<div class="toast-container" id="toast-container"></div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
