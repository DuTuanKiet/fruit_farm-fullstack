<?php
require_once __DIR__ . '/../../backend/core/config.php';
require_once __DIR__ . '/../../backend/core/db_connect.php';

function safe($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }

/**
 * Hàm tạo URL phân trang (Đã Sửa cho trang Products)
 * Chỉ cần page number và sử dụng tham số 'p'.
 */
function create_page_url($page_num) {
    $url = "?page=products"; // Đảm bảo chuyển về trang products
    $url .= "&p=" . $page_num; // Sử dụng tham số 'p'
    return $url;
}
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
        $details = $_POST['details'] ?? ''; 
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $category_id = intval($_POST['category_id'] ?? 0); 
        
        // Lấy category name và slug (Cần SELECT từ DB nếu chỉ có ID)
        $category_name = null;
        $category_slug = null;
        if ($category_id > 0) {
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

        // FIX LỖI BIND_PARAM: Ép kiểu category_id, name, slug thành NULL hoặc string
        if ($category_id <= 0) $category_id = null;
        else $category_id = (string)$category_id; // Ép kiểu để bind an toàn
        $category_name = $category_name ?? null;
        $category_slug = $category_slug ?? null;
        // END FIX

        $image_path_for_db = '';
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === 0) {
            $upload_dir = __DIR__ . '/../../public/assets/images/'; 
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
        
        // CẬP NHẬT SQL STATEMENT
        if ($name && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO products 
                (name, price, cost_price, stock, description, details, is_featured, image_url, category_id, category_name, category_slug, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            // Chuỗi bind param ĐÃ SỬA: sddississss
            // name(s), price(d), cost_price(d), stock(i), description(s), details(s), is_featured(i), image_url(s), category_id(s), category_name(s), category_slug(s)
            $stmt->bind_param(
                "sddississss", 
                $name, $price, $cost_price, $stock, 
                $description, $details, $is_featured, 
                $image_path_for_db, 
                $category_id, $category_name, $category_slug
            );
            
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
        
        // <<< CẬP NHẬT 1: Lấy thêm DETAILS và CATEGORY SLUG >>>
        $details = $_POST['details'] ?? ''; 
        $category_slug = $_POST['category_slug'] ?? 'tatca';
        // LƯU Ý: Nếu form của bạn gửi category_slug, bạn phải truy vấn DB để lấy ID và Name
        
        // Lấy category name, id (Cần SELECT từ DB nếu chỉ có SLUG)
        $category_id = null;
        $category_name = null;

        if (!empty($category_slug)) {
            $stmt_cat = $conn->prepare("SELECT id, name FROM categories WHERE slug=? LIMIT 1");
            $stmt_cat->bind_param("s", $category_slug);
            $stmt_cat->execute();
            $cat_res = $stmt_cat->get_result()->fetch_assoc();
            $stmt_cat->close();
            if ($cat_res) {
                $category_id = (int)$cat_res['id'];
                $category_name = $cat_res['name'];
            }
        }
        
        // Xử lý NULL cho category fields
        $category_id_bind = $category_id > 0 ? $category_id : null;
        $category_name_bind = $category_name ?? null;
        $category_slug_bind = $category_slug ?? null;
        // <<< KẾT THÚC CẬP NHẬT 1 >>>

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

        // <<< CẬP NHẬT 2: Sửa câu lệnh UPDATE để bao gồm tất cả các trường >>>
        $stmt = $conn->prepare("UPDATE products 
            SET name=?, price=?, cost_price=?, stock=?, image_url=?, 
                description=?, details=?, is_featured=?, 
                category_id=?, category_name=?, category_slug=? 
            WHERE id=?");
            
        // Chuỗi định dạng: sddisssisssi
        // s:name, d:price, d:cost_price, i:stock, s:image_url, s:description, s:details, i:is_featured, s:category_id, s:category_name, s:category_slug, i:id
        $stmt->bind_param(
            "sddisssisssi", 
            $name, $price, $cost_price, $stock, 
            $image_path_for_db, $description, $details, $is_featured, 
            $category_id_bind, $category_name_bind, $category_slug_bind, 
            $id
        );
        // <<< KẾT THÚC CẬP NHẬT 2 >>>
        
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
$limit = 6;
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$offset = ($page - 1) * $limit;

// Cần định nghĩa 2 biến này để khối HTML phân trang không bị lỗi
$status_filter = 'all';
$search_term = ''; 

$totalRes = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalRow = $totalRes->fetch_assoc();
$total = (int)$totalRow['total'];
$totalPages = max(1, ceil($total / $limit)); // <-- Biến tổng số trang đúng

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

    <div class="pagination-wrap">
        <?php if ($totalPages > 1): ?>
            <nav class="pagination-nav">
                <?php
                $window = 2; // Hiển thị 2 nút trang bên trái và 2 nút trang bên phải trang hiện tại

                // --- Nút Previous ---
                $prev_page = max(1, $page - 1);
                $prev_url = create_page_url($prev_page); // Đã sửa
                $prev_class = ($page == 1) ? ' disabled' : '';
                echo '<a href="'.$prev_url.'" class="page-link prev-next'.$prev_class.'" title="Trang trước"><i class="fas fa-angle-left"></i></a>';

                // --- Nút trang 1 ---
                $url_1 = create_page_url(1); // Đã sửa
                echo '<a href="'.$url_1.'" class="page-link ' . (1 == $page ? 'active' : '') . '">1</a>';

                // --- Dấu '...' đầu tiên ---
                // Hiển thị '...' nếu trang hiện tại cách trang 1 hơn 2 bước
                if ($page - $window > 2) {
                    echo '<span class="page-link ellipsis">...</span>';
                }

                // --- Các nút ở giữa (Window) ---
                $start_i = max(2, $page - $window);
                $end_i = min($totalPages - 1, $page + $window); // Đã sửa $totalPages
                
                for ($i = $start_i; $i <= $end_i; $i++) {
                    $url_i = create_page_url($i); // Đã sửa
                    echo '<a href="'.$url_i.'" class="page-link ' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
                }

                // --- Dấu '...' cuối cùng ---
                // Hiển thị '...' nếu trang hiện tại cách trang cuối hơn 2 bước
                if ($page + $window < $totalPages - 1) { // Đã sửa $totalPages
                    echo '<span class="page-link ellipsis">...</span>';
                }
                
                // --- Nút trang cuối cùng ---
                if ($totalPages > 1) { // Đã sửa $totalPages
                    $url_last = create_page_url($totalPages); // Đã sửa
                    echo '<a href="'.$url_last.'" class="page-link ' . ($totalPages == $page ? 'active' : '') . '">' . $totalPages . '</a>';
                }

                // --- Nút Next ---
                $next_page = min($totalPages, $page + 1); // Đã sửa $totalPages
                $next_url = create_page_url($next_page); // Đã sửa
                $next_class = ($page == $totalPages) ? ' disabled' : ''; // Đã sửa $totalPages
                echo '<a href="'.$next_url.'" class="page-link prev-next'.$next_class.'" title="Trang sau"><i class="fas fa-angle-right"></i></a>';
                ?>
            </nav>
        <?php endif; ?>
    </div>

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