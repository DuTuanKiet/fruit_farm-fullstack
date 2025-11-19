<?php
require_once __DIR__ . '/../backend/core/config.php';
require_once __DIR__ . '/../backend/core/db_connect.php';

$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
$relatedProducts = [];

// ===============================
// 1. XỬ LÝ TÌM KIẾM
// ===============================
if ($searchQuery !== "") {

    // Kiểm tra FULLTEXT index trên name + description
    $checkFulltext = $conn->query("
        SHOW INDEX FROM products 
        WHERE Index_type='FULLTEXT' 
        AND Column_name IN ('name', 'description')
    ");

    if ($checkFulltext && $checkFulltext->num_rows > 0) {
        // FULLTEXT SEARCH
        $sql = "SELECT id, name, price, image_url, description, stock
                FROM products
                WHERE status='active' 
                AND MATCH(name, description) AGAINST(? IN BOOLEAN MODE)";
        $stmt = $conn->prepare($sql);
        $searchTerm = '+' . str_replace(' ', ' +', $searchQuery); // boolean mode
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Nếu không có kết quả, fallback LIKE
        if (empty($searchResults)) {
            $sql_like = "SELECT id, name, price, image_url, description, stock
                         FROM products
                         WHERE status='active' AND (name LIKE ? OR description LIKE ?)";
            $stmt = $conn->prepare($sql_like);
            $likeTerm = '%' . $searchQuery . '%';
            $stmt->bind_param("ss", $likeTerm, $likeTerm);
            $stmt->execute();
            $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        // Nếu không có FULLTEXT index, dùng LIKE trực tiếp
        $sql_like = "SELECT id, name, price, image_url, description, stock
                     FROM products
                     WHERE status='active' AND (name LIKE ? OR description LIKE ?)";
        $stmt = $conn->prepare($sql_like);
        $likeTerm = '%' . $searchQuery . '%';
        $stmt->bind_param("ss", $likeTerm, $likeTerm);
        $stmt->execute();
        $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// ===============================
// 2. LẤY SẢN PHẨM LIÊN QUAN (RANDOM 5, stock > 0)
// ===============================
$sql_related = "SELECT id, name, price, image_url, stock 
                FROM products 
                WHERE stock > 0 AND status='active'
                ORDER BY RAND() 
                LIMIT 5";
$relatedResult = $conn->query($sql_related);
if ($relatedResult) {
    $relatedProducts = $relatedResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kết quả tìm kiếm cho '<?= htmlspecialchars($searchQuery); ?>'</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"/>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/sanpham.css">

    <style>
        .related-section { margin-top: 40px; }
        .related-title { font-size: 20px; font-weight: 700; margin-bottom: 18px; }
        .no-results {
            font-size: 16px;
            color: #6b7280;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 14px 18px;
            text-align: center;
            width: 100%;
        }
        .btn-disabled {
            background: #ccc;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>

<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<main>
    <section class="product-section">
        <h2 class="section-title">
            <?php if ($searchQuery !== ""): ?>
                Kết quả tìm kiếm cho "<?= htmlspecialchars($searchQuery); ?>"
            <?php else: ?>
                Vui lòng nhập từ khóa để tìm kiếm
            <?php endif; ?>
        </h2>

        <div class="product-list">
            <?php if ($searchQuery !== ""): ?>
                <?php if (!empty($searchResults)): ?>
                    <?php foreach ($searchResults as $product):
                        $isOutOfStock = intval($product['stock']) <= 0;
                    ?>
                        <div class="product-card-container">
                            <a href="chitietsp.php?id=<?= $product['id']; ?>" class="product-image-link">
                                <div class="product-image-wrapper">
                                    <img src="<?= BASE_URL . htmlspecialchars($product['image_url']); ?>"
                                         alt="<?= htmlspecialchars($product['name']); ?>"
                                         class="product-image">
                                </div>
                            </a>

                            <div class="product-info">
                                <h3 class="product-name">
                                    <a href="chitietsp.php?id=<?= $product['id']; ?>">
                                        <?= htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>

                                <p class="product-price"><?= number_format($product['price']); ?>₫</p>

                                <div class="product-actions">
                                    <?php if ($isOutOfStock): ?>
                                        <button class="add-to-cart-btn btn-sm btn-disabled">
                                            <i class="fa fa-shopping-cart"></i> Hết hàng
                                        </button>
                                        <button class="buy-now-btn btn-sm btn-disabled">Mua Ngay</button>
                                    <?php else: ?>
                                        <button class="add-to-cart-btn btn-sm requires-login" data-id="<?= $product['id']; ?>">
                                            <i class="fa fa-shopping-cart"></i> Thêm vào giỏ
                                        </button>
                                        <button class="buy-now-btn btn-sm requires-login" data-id="<?= $product['id']; ?>">Mua Ngay</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-results">Không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- SẢN PHẨM LIÊN QUAN -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-section">
            <h2 class="related-title">Các sản phẩm khác</h2>

            <div class="product-list">
                <?php foreach ($relatedProducts as $product):
                    $isOutOfStock = intval($product['stock']) <= 0;
                ?>
                    <div class="product-card-container">
                        <a href="chitietsp.php?id=<?= $product['id']; ?>" class="product-image-link">
                            <div class="product-image-wrapper">
                                <img src="<?= BASE_URL . htmlspecialchars($product['image_url']); ?>"
                                     alt="<?= htmlspecialchars($product['name']); ?>"
                                     class="product-image">
                            </div>
                        </a>

                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="chitietsp.php?id=<?= $product['id']; ?>">
                                    <?= htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>

                            <p class="product-price"><?= number_format($product['price']); ?>₫</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </section>
</main>

<div class="toast-container" id="toast-container"></div>
<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
