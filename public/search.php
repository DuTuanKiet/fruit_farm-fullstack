<?php
require_once __DIR__ . '/../backend/core/config.php';
require_once __DIR__ . '/../backend/core/db_connect.php';

// Lấy từ khóa tìm kiếm từ URL, ví dụ: search.php?q=xoai
$searchQuery = $_GET['q'] ?? '';
$searchQuery = trim($searchQuery); 

$searchResults = [];
if (!empty($searchQuery)) {
    // Sử dụng truy vấn FULLTEXT SEARCH
    // MATCH(...) AGAINST(...) nhanh hơn nhiều so với LIKE '%...%'
    $sql = "SELECT id, name, price, image_url, description 
            FROM products 
            WHERE MATCH(name, description) AGAINST(? IN BOOLEAN MODE)";

    $stmt = $conn->prepare($sql);

    // Thêm các toán tử để tìm kiếm tốt hơn, ví dụ: "+xoai +xanh"
    $searchTerm = '+' . str_replace(' ', ' +', $searchQuery);

    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $searchResults = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kết quả tìm kiếm cho "<?= htmlspecialchars($searchQuery); ?>"</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/sanpham.css"> </head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
      <section class="product-section">
        <h2 class="section-title">
            <?php if (!empty($searchQuery)): ?>
                Kết quả tìm kiếm cho "<?= htmlspecialchars($searchQuery); ?>"
            <?php else: ?>
                Vui lòng nhập từ khóa để tìm kiếm
            <?php endif; ?>
        </h2>

        <div class="product-list">
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $product): ?>
                    <div class="product-card-container">
                        <a href="chitietsp.php?id=<?= $product['id']; ?>" class="product-image-link">
                            <div class="product-image-wrapper">
                                <img src="<?= BASE_URL ?><?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="product-image"/>
                            </div>
                        </a>
                        <div class="product-info">
                            <h3 class="product-name">
                                <a href="chitietsp.php?id=<?= $product['id']; ?>"><?= htmlspecialchars($product['name']); ?></a>
                            </h3>
                            <p class="product-price"><?= number_format($product['price']); ?>₫</p>
                            <div class="product-actions">
                                <button class="add-to-cart-btn btn-sm requires-login" data-id="<?= $product['id']; ?>"><i class="fa fa-shopping-cart"></i> Thêm vào giỏ</button>
                                <button class="buy-now-btn btn-sm requires-login" data-id="<?= $product['id']; ?>">Mua Ngay</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!empty($searchQuery)): ?>
                <p class="no-results">Không tìm thấy sản phẩm nào phù hợp với từ khóa của bạn.</p>
            <?php endif; ?>
        </div>
      </section>
    </main>
    <div class="toast-container" id="toast-container"></div>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>