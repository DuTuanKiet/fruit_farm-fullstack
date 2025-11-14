<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

$name = $_POST['name'] ?? '';
$price = floatval($_POST['price'] ?? 0);
$cost_price = floatval($_POST['cost_price'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$description = $_POST['description'] ?? '';
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$category_slug = $_POST['category_slug'] ?? 'tatca';

// ✅ Xử lý upload ảnh
$image_path_for_db = null;
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
    $uploadDir = __DIR__ . '/../../public/assets/images/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . basename($_FILES['product_image']['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
        $image_path_for_db = 'public/assets/images/' . $filename;
    } else {
        die('Không thể lưu file ảnh.');
    }
}

// Chỉ dùng lệnh INSERT cho thêm mới
$stmt = $conn->prepare("INSERT INTO products 
(name, price, cost_price, stock, description, is_featured, image_url, category_slug, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param(
    "sddisiss",
    $name,
    $price,
    $cost_price,
    $stock,
    $description,
    $is_featured,
    $image_path_for_db,
    $category_slug
);

if ($stmt->execute()) {
    echo "✅ Sản phẩm đã được thêm thành công!";
} else {
    echo "❌ Lỗi khi thêm sản phẩm: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
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
        <!-- Cột trái -->
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
            <input type="number" step="0.01" name="cost_price" id="cost_price" value="<?= $product['cost_price'] ?? 0 ?>" required>
          </div>
          <div class="form-group">
            <label for="stock">Tồn kho:</label>
            <input type="number" name="stock" id="stock" value="<?= $product['stock'] ?? 0 ?>" required>
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
            <label for="category_slug">Phân loại (Danh mục) <span style="color:red">*</span></label>
            <select id="category_slug" name="category_slug" required>
              <option value="">-- Chọn danh mục --</option>
              <option value="trai-tuoi">Trái cây tươi</option>
              <option value="saykho">Trái cây sấy khô</option>
              <option value="nhapkhau">Trái cây nhập khẩu</option>
              <option value="dac-san">Đặc sản vùng miền</option>
              <option value="hienmua">Theo mùa hiện tại</option>
              <option value="nhietdoi">Nhiệt đới</option>
              <option value="camquyt">Có múi</option>
              <option value="berry">Dâu & Berry</option>
              <option value="tatca">Khác</option>
            </select>
          </div>

        <!-- Cột phải -->
        <div>
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
  // Preview ảnh sản phẩm trước khi upload
  const input = document.getElementById('product_image');
  const preview = document.getElementById('previewImg');
  if (input) {
    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (file) {
        const url = URL.createObjectURL(file);
        preview.src = url;
      } else {
        preview.src = '<?= BASE_URL ?>public/assets/images/no-image.png';
      }
    });
  }

  // Optional: xác nhận trước khi gửi
  const form = document.getElementById('addProductForm');
  form.addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const price = document.getElementById('price').value.trim();
    if (!name || !price) {
      e.preventDefault();
      alert('Vui lòng nhập đầy đủ tên và giá sản phẩm!');
      return false;
    }
  });
</script>
