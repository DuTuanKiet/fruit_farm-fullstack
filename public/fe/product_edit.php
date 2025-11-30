<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header("Location: ?page=products"); exit; }

// Lấy dữ liệu sản phẩm từ DB
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) { header("Location: ?page=products"); exit; }

$link_back = '?page=products';

// === CẬP NHẬT 1: Lấy danh sách Categories từ DB ===
$categories = [];
$stmt_cat = $conn->prepare("SELECT id, name, slug FROM categories ORDER BY id ASC");
$stmt_cat->execute();
$result_cat = $stmt_cat->get_result();
while ($row = $result_cat->fetch_assoc()) {
    $categories[$row['slug']] = ['id' => $row['id'], 'name' => $row['name']];
}
$stmt_cat->close();
// ==================================================

// Xử lý khi người dùng bấm nút "Cập nhật"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = $_POST['name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $cost_price = floatval($_POST['cost_price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $description = $_POST['description'] ?? '';
    $details = $_POST['details'] ?? ''; // Đã lấy details
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $category_slug = $_POST['category_slug'] ?? 'tatca';

    // === CẬP NHẬT 2: Ánh xạ category_slug sang ID và Name (FIX LỖI CHÍNH) ===
    $category_id = null;
    $category_name = null;
    
    if (isset($categories[$category_slug])) {
        $category_id = $categories[$category_slug]['id'];
        $category_name = $categories[$category_slug]['name'];
    }
    // LƯU Ý: Vì DB có trường category_id là INT(11) có thể NULL, nhưng bind_param cần kiểu dữ liệu phù hợp
    // Nếu category_id là NULL, ta phải bind nó là NULL. Trong trường hợp này, ta giả định nó là INT.
    // Nếu category_id không có giá trị, đặt là NULL cho DB
    $category_id_bind = $category_id > 0 ? $category_id : null;
    $category_name_bind = $category_name ?? null;
    $category_slug_bind = $category_slug ?? null;
    // =========================================================================

    // Xử lý upload ảnh (nếu người dùng chọn ảnh mới)
    $image_path_for_db = $product['image_url']; // Giữ nguyên ảnh cũ nếu không đổi

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $uploadDir = realpath(__DIR__ . '/../../') . '/public/assets/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = uniqid() . '_' . basename($_FILES['product_image']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            $image_path_for_db = 'public/assets/images/' . $filename;

            // (Tùy chọn) Xóa ảnh cũ nếu có
            $oldPath = __DIR__ . '/../../' . $product['image_url'];
            if (file_exists($oldPath)) unlink($oldPath);
        }
    }

    // UPDATE sản phẩm
    $stmt = $conn->prepare("UPDATE products 
        SET name=?, price=?, cost_price=?, stock=?, image_url=?, 
            description=?, details=?, is_featured=?, 
            category_id=?, category_name=?, category_slug=? 
        WHERE id=?");

    // Chuỗi định dạng: "sddisssiissi" (đã bao gồm details (s) và category_id, name, slug)
    // s:name, d:price, d:cost_price, i:stock, s:image_url, s:description, s:details, i:is_featured, i:category_id, s:category_name, s:category_slug, i:id
    // LƯU Ý: mysqli::bind_param không hỗ trợ NULL cho kiểu i. Phải dùng kiểu 's' và truyền NULL
    $stmt->bind_param(
      "sddisssisssi", // Sửa lại thành 's' cho category_id để chấp nhận NULL (đã đổi category_id_bind)
        $name, $price, $cost_price, $stock,
        $image_path_for_db, $description, $details,
        $is_featured, $category_id_bind, $category_name_bind, $category_slug_bind, // Dùng biến đã xử lý NULL
        $id
    );

    if ($stmt->execute()) {
        echo "<script>alert('✅ Cập nhật sản phẩm thành công!'); window.location.href='$link_back';</script>";
    } else {
        echo "<p style='color:red'>❌ Lỗi khi cập nhật: " . htmlspecialchars($stmt->error) . "</p>";
    }
    $stmt->close();
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
<div class="toast-container" id="toast-container"></div>
<div class="content-wrapper">
  <div class="main-header">
    <div>
      <h2><i class="fa fa-pen"></i> Chỉnh sửa sản phẩm</h2>
      <div class="muted" style="margin-top:6px;font-size:13px;">ID: <?= safe($product['id']); ?> — <?= safe($product['name']); ?></div>
    </div>
    <div>
      <a href="<?= $link_back ?>" class="btn-small btn-cancel"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>
  </div>

  <div class="form-container">
    <form action="?page=products" method="POST" enctype="multipart/form-data" id="editProductForm">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" value="<?= intval($product['id']); ?>">
      <input type="hidden" name="current_image_url" value="<?= safe($product['image_url']); ?>">

      <div class="grid-2">
        <div>
          <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input id="name" name="name" type="text" value="<?= safe($product['name']); ?>" required>
          </div>

          <div class="form-group">
            <label for="price">Giá bán (VNĐ)</label>
            <input id="price" name="price" type="number" step="0.01" value="<?= floatval($product['price']); ?>" required>
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
            <textarea id="description" name="description" rows="4"><?= safe($product['description']); ?></textarea>
          </div>

          <div class="form-group">
            <label for="details">Chi tiết</label>
            <textarea id="details" name="details" rows="6"><?= safe($product['details']); ?></textarea>
          </div>

          <div class="form-group">
            <label for="category_slug">Phân loại (Danh mục)</label>
            <select id="category_slug" name="category_slug" required>
              <?php $current_slug = safe($product['category_slug'] ?? 'tatca'); ?>
              <?php foreach ($categories as $slug => $cat): ?>
                <option value="<?= safe($slug) ?>" <?= $current_slug == $slug ? 'selected' : '' ?>>
                  <?= safe($cat['name']) ?>
                </option>
              <?php endforeach; ?>
              <?php if (!isset($categories['tatca']) && $current_slug == 'tatca'): ?>
                <option value="tatca" selected>Khác</option>
              <?php endif; ?>
            </select>
          </div>

          <div class="form-group">
            <label><input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>> Ghim nổi bật</label>
          </div>
        </div>

        <div>
          <div class="form-group">
            <label for="product_image">Ảnh sản phẩm (nếu muốn đổi)</label>
            <input id="product_image" name="product_image" type="file" accept="image/*">
          </div>

          <div class="img-preview">
            <?php
              $img = $product['image_url'] ?: 'public/assets/images/no-image.png';
              if (strpos($img, 'http') === 0) $disp = $img;
              elseif (strpos($img, 'public/') === 0) $disp = BASE_URL . $img;
              else $disp = BASE_URL . 'public/assets/images/' . basename($img);
            ?>
            <img id="previewImg" src="<?= safe($disp); ?>" alt="Preview">
          </div>

          <div class="form-actions">
            <button type="submit" name="update_product" class="btn btn-save"><i class="fa fa-save"></i> Lưu thay đổi</button>
            <a href="<?= $link_back ?>" class="btn btn-cancel"><i class="fa fa-times"></i> Hủy</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  // preview ảnh cho edit
  (function(){
    const input = document.getElementById('product_image');
    const preview = document.getElementById('previewImg');
    if (!input) return;
    input.addEventListener('change', function(){
      const f = this.files[0];
      if (!f) return;
      preview.src = URL.createObjectURL(f);
    });
  })();
</script>