<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

// Lấy danh mục từ database
$cateQuery = $conn->query("SELECT id, name, slug FROM categories ORDER BY name ASC"); 

$name = $_POST['name'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$cost_price = floatval($_POST['cost_price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$details = $_POST['details'] ?? ''; 
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$category_id = intval($_POST['category_id'] ?? 0);

// Lấy category name và slug (CẦN SELECT MỚI HOẶC TỪ $cateQuery)
$category_name = null;
$category_slug = null;

if ($category_id > 0) {
    // Truy vấn để lấy tên và slug của danh mục
    $stmt_cat = $conn->prepare("SELECT name, slug FROM categories WHERE id=? LIMIT 1");
    $stmt_cat->bind_param("i", $category_id);
    $stmt_cat->execute();
    $cat_res = $stmt_cat->get_result()->fetch_assoc();
    $stmt_cat->close();
    
    if ($cat_res) {
        $category_name = $cat_res['name'];
        $category_slug = $cat_res['slug'];
    }
}

if ($category_id <= 0 || !$category_name) {
    $category_id = null; 
    $category_name = null;
    $category_slug = null;
}

// Xử lý upload ảnh
$image_path_for_db = null;
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
    $uploadDir = __DIR__ . '/../../public/assets/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext; 
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
        $image_path_for_db = 'assets/images/' . $filename;
    } else {
        die('Không thể lưu file ảnh.');
    }
}

// Chèn sản phẩm
$stmt = $conn->prepare("INSERT INTO products 
(name, price, cost_price, stock, description, details, is_featured, image_url, category_id, category_name, category_slug, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

// *** ĐÃ SỬA: Dùng các biến đã được gán NULL/Giá trị ***
$stmt->bind_param(
    "sddississss",
    $name,
    $price,
    $cost_price,
    $stock,
    $description,
    $details, 
    $is_featured,
    $image_path_for_db,
    $category_id,       
    $category_name,    
    $category_slug     
);

// Xử lý kết quả
if ($stmt->execute()) {
    // TODO: Thêm logic thông báo thành công (ví dụ: echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='?page=products';</script>";)
} else {
    // TODO: Thêm logic thông báo lỗi (ví dụ: echo "Lỗi SQL: " . $stmt->error;)
}
$stmt->close();
$conn->close();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="toast-container" id="toast-container"></div>
<div class="content-wrapper">
    <div class="main-header">
        <h2><i class="fa fa-plus-circle"></i> Thêm sản phẩm mới</h2>
        <a href="?page=products" class="btn btn-small" style="background:#e9ecef;color:#333;">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="form-container">
        <form action="?page=products" method="POST" enctype="multipart/form-data" id="addProductForm">
            <input type="hidden" name="action" value="save_new">

            <div class="grid-2">
                <div>
                    <div class="form-group">
                        <label for="name">Tên sản phẩm <span style="color:red">*</span></label>
                        <input type="text" id="name" name="name" placeholder="Nhập tên sản phẩm..." required>
                    </div>

                    <div class="form-group">
                        <label for="price">Giá bán (₫) <span style="color:red">*</span></label>
                        <input type="number" id="price" name="price" min="0" step="0.01" placeholder="Ví dụ: 25000" required>
                    </div>

                    <div class="form-group">
                        <label for="cost_price">Giá vốn:</label>
                        <input type="number" step="0.01" name="cost_price" id="cost_price" value="0" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Tồn kho:</label>
                        <input type="number" name="stock" id="stock" value="0" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Mô tả ngắn</label>
                        <textarea id="description" name="description" rows="3" placeholder="Mô tả ngắn gọn sản phẩm..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="details">Chi tiết sản phẩm</label>
                        <textarea id="details" name="details" rows="6" placeholder="Thông tin chi tiết, giới thiệu, công dụng..."></textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" value="1">
                        <label for="is_featured">Ghim ở trang chủ (sản phẩm nổi bật)</label>
                    </div>
                </div>

                <div>

                    <div class="form-group">
                        <label for="category_id">Phân loại (Danh mục) <span style="color:red">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Chọn danh mục --</option>

                            <?php while ($row = $cateQuery->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['name']) ?>
                                </option>
                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product_image">Ảnh sản phẩm <span style="color:red">*</span></label>
                        <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/gif, image/webp" required>
                    </div>

                    <div class="img-preview">
                        <img src="<?= file_exists(__DIR__.'/../../public/assets/images/no-image.png')
                            ? BASE_URL.'public/assets/images/no-image.png'
                            : 'https://via.placeholder.com/400x400?text=No+Image' ?>" 
                            alt="Xem trước ảnh sản phẩm" id="previewImg">
                    </div>

                    <div class="form-actions" style="margin-top:20px;">
                        <button type="submit" class="btn btn-save">
                            <i class="fa fa-save"></i> Lưu sản phẩm
                        </button>
                        <a href="?page=products" class="btn btn-small" style="background:#dee2e6;color:#333;">
                            <i class="fa fa-times"></i> Hủy bỏ
                        </a>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Preview ảnh
    const input = document.getElementById('product_image');
    const preview = document.getElementById('previewImg');

    if (input) {
        input.addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) preview.src = URL.createObjectURL(file);
            else preview.src = '<?= BASE_URL ?>public/assets/images/no-image.png';
        });
    }

    // Validate cơ bản
    const form = document.getElementById('addProductForm');
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const price = document.getElementById('price').value.trim();

        if (!name || !price) {
            e.preventDefault();
            alert('Vui lòng nhập đầy đủ tên và giá sản phẩm!');
        }
    });
</script>