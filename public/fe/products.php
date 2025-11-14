<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

function safe($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

// === XỬ LÝ POST: thêm / update / delete ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // LƯU MỚI
    if ($action === 'save_new') {
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $cost_price = floatval($_POST['cost_price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $description = $_POST['description'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $image_path_for_db = '';
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === 0) {
            $upload_dir = __DIR__ . '/../public/assets/images/';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
            $orig = basename($_FILES['product_image']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed)) {
                $newfile = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-_\.]/','', $orig);
                $target = $upload_dir . $newfile;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
                    $image_path_for_db = 'public/assets/images/' . $newfile;
                }
            }
        }

        if ($name && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, stock, description, is_featured, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sddisis", $name, $price, $cost_price, $stock, $description, $is_featured, $image_path_for_db);
            if ($stmt->execute()) $_SESSION['message'] = "Thêm sản phẩm thành công.";
            else $_SESSION['error'] = "Lỗi thêm: " . $stmt->error;
            $stmt->close();
        } else {
            $_SESSION['error'] = "Vui lòng nhập tên và giá hợp lệ.";
        }
        header("Location: ?page=products");
        exit;
    }

    // CẬP NHẬT
    if ($action === 'update') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $cost_price = floatval($_POST['cost_price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $description = $_POST['description'] ?? '';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $current_image = $_POST['current_image_url'] ?? '';

        $image_path_for_db = $current_image;
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === 0) {
            $upload_dir = realpath(__DIR__ . '/../../') . '/public/assets/images/';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
            $orig = basename($_FILES['product_image']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed)) {
                $newfile = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-_\.]/','', $orig);
                $target = $upload_dir . $newfile;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
                    if (!empty($current_image) && file_exists(__DIR__ . '/../' . $current_image)) @unlink(__DIR__ . '/../' . $current_image);
                    $image_path_for_db = 'public/assets/images/' . $newfile;
                }
            }
        }

        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, cost_price=?, stock=?, image_url=?, description=?, is_featured=? WHERE id=?");
        $stmt->bind_param("sddissii", $name, $price, $cost_price, $stock, $image_path_for_db, $description, $is_featured, $id);
        if ($stmt->execute()) $_SESSION['message'] = "Cập nhật sản phẩm thành công.";
        else $_SESSION['error'] = "Lỗi cập nhật: " . $stmt->error;
        $stmt->close();
        header("Location: ?page=products");
        exit;
    }

    // XÓA
    if ($action === 'delete') {
        $id = intval($_POST['delete_id'] ?? 0);
        if ($id > 0) {
            $q = $conn->prepare("SELECT image_url FROM products WHERE id=?");
            $q->bind_param("i", $id);
            $q->execute();
            $r = $q->get_result()->fetch_assoc();
            $q->close();
            if (!empty($r['image_url']) && file_exists(__DIR__ . '/../' . $r['image_url'])) @unlink(__DIR__ . '/../' . $r['image_url']);

            $d = $conn->prepare("DELETE FROM products WHERE id=?");
            $d->bind_param("i", $id);
            if ($d->execute()) $_SESSION['message'] = "Đã xóa sản phẩm.";
            else $_SESSION['error'] = "Lỗi xóa: " . $d->error;
            $d->close();
        }
        header("Location: ?page=products");
        exit;
    }
}

// === HIỂN THỊ TRANG ===
$action = $_GET['action'] ?? 'list';
if ($action === 'add') {
    include __DIR__ . '/product_add.php';
    exit;
}
if ($action === 'edit' && isset($_GET['id'])) {
    include __DIR__ . '/product_edit.php';
    exit;
}

// LIST (có phân trang)
$limit = 12;
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

$totalRes = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalRow = $totalRes->fetch_assoc();
$total = (int)$totalRow['total'];
$totalPages = max(1, ceil($total / $limit));

$stmt = $conn->prepare("SELECT * FROM products ORDER BY id ASC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/products.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div class="toast-container" id="toast-container"></div>
<div class="content-wrapper">
  <div class="main-header">
    <h2> Quản lý sản phẩm</h2>
    <div class="muted" style="margin-top:6px;font-size:14px;">Tổng: <?= $total ?> sản phẩm</div>
    <a href="?page=products&action=add" class="btn-add"><i class="fa fa-plus"></i> Thêm sản phẩm</a>
  </div>

  <?php if (!empty($_SESSION['message'])): ?>
    <div class="alert alert-success"><?= safe($_SESSION['message']); unset($_SESSION['message']); ?></div>
  <?php elseif (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= safe($_SESSION['error']); unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <table class="styled-table">
    <thead>
  <tr>
    <th>#</th>
    <th>Ảnh</th>
    <th>Tên / Danh mục</th>
    <th>Giá bán</th>
    <th>Giá vốn</th>
    <th>Tồn kho</th>
    <th>Trạng thái</th>
    <th>Hành động</th>
  </tr>
</thead>

<tbody>
<?php $i = $offset + 1; while ($row = $result->fetch_assoc()): 
    $image_url = $row['image_url'] ?? '';
    if ($image_url) {
        if (strpos($image_url, 'http') === 0) $display = $image_url;
        elseif (strpos($image_url, 'public/') === 0) $display = BASE_URL . $image_url;
        else $display = BASE_URL . 'public/assets/images/' . basename($image_url);
    } else {
        $display = BASE_URL . 'public/assets/images/no-image.png';
    }
?>
<tr>
    <td><?= $i++; ?></td>
    <td><img src="<?= safe($display) ?>" alt="<?= safe($row['name']) ?>" class="tbl-img"></td>
    <td><strong><?= safe($row['name']) ?></strong></td>
    <td><?= number_format($row['price'] ?? 0,0,',','.') ?>₫</td>
    <td><?= number_format($row['cost_price'] ?? 0,0,',','.') ?>₫</td>
    <td><?= intval($row['stock'] ?? 0) ?></td>
    <td><?= $row['is_featured'] ? '<span class="badge badge-green">Có</span>' : '<span class="badge badge-gray">Không</span>' ?></td>
    <td>
      <a href="?page=products&action=edit&id=<?= $row['id'] ?>" class="btn-action"><i class="fa fa-pen"></i> Sửa</a>
      <form method="post" style="display:inline-block;margin:0;padding:0;" onsubmit="return confirm('Bạn chắc muốn xóa sản phẩm này?');">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
        <button type="submit" class="btn-action text-red" style="background:none;border:none;cursor:pointer;"><i class="fa fa-trash"></i> Xóa</button>
      </form>
    </td>
</tr>
<?php endwhile; ?>
</tbody>

  </table>

  <!-- Pagination -->
  <div class="pagination-wrap">
    <?php if ($page > 1): ?>
      <a class="page-link" href="?page=products&p=<?= $page - 1 ?>">&laquo; Trước</a>
    <?php endif; ?>

    <?php
      $start = max(1, $page - 3);
      $end = min($totalPages, $page + 3);
      if ($start > 1) echo '<a class="page-link" href="?page=products&p=1">1</a>...';
      for ($p=$start;$p<=$end;$p++){
        $cls = $p==$page ? 'active' : '';
        echo '<a class="page-link '.$cls.'" href="?page=products&p='.$p.'">'.$p.'</a>';
      }
      if ($end < $totalPages) echo '...<a class="page-link" href="?page=products&p='.$totalPages.'">'.$totalPages.'</a>';
    ?>

    <?php if ($page < $totalPages): ?>
      <a class="page-link" href="?page=products&p=<?= $page + 1 ?>">Sau &raquo;</a>
    <?php endif; ?>
  </div>
</div>

<!-- Quick view modal -->
<div id="quickView" class="quickview-overlay" style="display:none;">
  <div class="quickview-card">
    <button class="qv-close">&times;</button>
    <div class="qv-left"><img id="qv-img" src="" alt=""></div>
    <div class="qv-right">
      <h3 id="qv-name"></h3>
      <div id="qv-desc" class="muted"></div>
      <div id="qv-price" style="font-weight:700;color:var(--primary-color);margin-top:10px;"></div>
      <div class="qv-actions" style="margin-top:18px;">
        <a id="qv-edit" class="btn btn-small" href="#">Sửa</a>
        <form id="qv-delete-form" method="post" style="display:inline-block;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" id="qv-delete-id" name="delete_id" value="">
          <button type="submit" class="btn btn-small btn-cancel">Xóa</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Quick view JS
  document.addEventListener('click', function(e){
    const tr = e.target.closest('tr');
    if(!tr) return;
    if(e.target.closest('.tbl-img') || e.target.closest('.btn-action')) {
      document.getElementById('qv-img').src = tr.querySelector('img').src;
      document.getElementById('qv-name').textContent = tr.querySelector('strong').textContent;
      document.getElementById('qv-desc').textContent = tr.querySelector('td:nth-child(3)').textContent;
      document.getElementById('qv-delete-id').value = tr.querySelector('input[name="delete_id"]').value;
      document.getElementById('quickView').style.display = 'flex';
      const editLink = tr.querySelector('a[href*="edit"]')?.href;
      if(editLink) document.getElementById('qv-edit').href = editLink;
    }
  });
  document.querySelector('.qv-close').addEventListener('click', function(){
    document.getElementById('quickView').style.display = 'none';
  });
</script>
