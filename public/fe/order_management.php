<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../backend/admin/admin_auth.php';
require_once __DIR__ . '/../../backend/core/db_connect.php'; 
require_once __DIR__ . '/../../backend/admin/order_module.php';

/**
 * Sinh order_code tự động: U{user_id}-YYYYMMDD-XXX
 */
function generateOrderCode($conn, $user_id) {
    $date = date('Ymd');
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE DATE(order_date) = CURDATE()");
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $seq = (int)$row['cnt'] + 1;
    $stmt->close();
    return sprintf("U%d-%s-%03d", $user_id, $date, $seq);
}

/**
 * Hàm tạo URL phân trang, giữ nguyên các filter.
 */
function create_page_url($page_num, $status_filter, $search_term) {
    $url = "?page=orders";
    if ($status_filter !== 'all') $url .= "&status=" . urlencode($status_filter);
    if (!empty($search_term)) $url .= "&search=" . urlencode($search_term);
    $url .= "&page_num=" . $page_num;
    return $url;
}

// Lấy thống kê đơn hàng
$total_stats = get_order_stats($conn);
$stats_keys = ['all_orders','pending_orders','confirmed_orders','shipping_orders','completed_orders','cancelled_orders','total_revenue'];
foreach ($stats_keys as $key) {
    if (!isset($total_stats[$key])) $total_stats[$key] = 0;
}

// Lấy filter và search
$status_filter = $_GET['status'] ?? 'all';
$search_term   = trim($_GET['search'] ?? '');
$limit  = 6;
$page   = max(1, intval($_GET['page_num'] ?? 1));
$offset = ($page - 1) * $limit;

// Build WHERE clause (search theo customer_name + order_code)
$where = [];
$params = [];
$types = "";
if ($status_filter !== 'all') {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if (!empty($search_term)) {
    $where[] = "(customer_name LIKE ? OR order_code LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
    $types .= "ss";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// ======= Đếm tổng số đơn =======
$count_sql = "SELECT COUNT(*) FROM orders $where_sql";
$count_stmt = $conn->prepare($count_sql);
if ($count_stmt === false) die("Lỗi prepare SQL: " . $conn->error);
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_stmt->bind_result($total_orders);
$count_stmt->fetch();
$count_stmt->close();

$total_orders = $total_orders ?? 0;
$total_pages  = max(1, ceil($total_orders / $limit));
$offset       = max(0, $offset);

// ======= Lấy danh sách đơn hàng =======
$sql = "SELECT * FROM orders $where_sql ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) die("Lỗi prepare SQL: " . $conn->error);

if ($types) {
    $types_limit = $types . "ii";
    $params_for_bind = array_merge($params, [$offset, $limit]);
    $stmt->bind_param($types_limit, ...$params_for_bind);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
$paged_orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/order_management.css">
<div class="toast-container" id="toast-container"></div>

<div class="admin-content-wrapper">
    <h1 class="page-title">Quản Lý Đơn Hàng</h1>

    <!-- Thống kê -->
    <div class="stats-grid">
        <?php 
        $stats_labels = [
            'all_orders'=>'Tổng số Đơn Hàng','pending_orders'=>'Đơn chờ xác nhận',
            'confirmed_orders'=>'Đã xác nhận','shipping_orders'=>'Đang giao',
            'completed_orders'=>'Đã hoàn thành','cancelled_orders'=>'Đã hủy',
            'total_revenue'=>'Tổng Doanh Thu'
        ];
        $stats_colors = [
            'all_orders'=>'blue-bg','pending_orders'=>'orange-bg','confirmed_orders'=>'purple-bg',
            'shipping_orders'=>'teal-bg','completed_orders'=>'green-bg','cancelled_orders'=>'gray-bg','total_revenue'=>'red-bg'
        ];
        $stats_icons = [
            'all_orders'=>'fa-box-archive','pending_orders'=>'fa-hourglass-start','confirmed_orders'=>'fa-check-double',
            'shipping_orders'=>'fa-truck','completed_orders'=>'fa-circle-check','cancelled_orders'=>'fa-xmark','total_revenue'=>'fa-sack-dollar'
        ];
        foreach($stats_keys as $key): ?>
        <div class="stat-card <?= $stats_colors[$key] ?>">
            <div class="stat-icon-wrapper white-bg"><i class="fa-solid <?= $stats_icons[$key] ?>"></i></div>
            <div class="stat-info">
                <p><?= $stats_labels[$key] ?></p>
                <span class="stat-value">
                    <?= $key === 'total_revenue' ? number_format($total_stats[$key]).'₫' : number_format($total_stats[$key]) ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter + Search -->
    <div class="shadow-card filter-search-container">
        <form action="admin_dashboard.php" method="GET" class="filter-form">
            <input type="hidden" name="page" value="orders">
            <div class="status-filter-group">
                <label for="status-filter">Trạng thái:</label>
                <select name="status" id="status-filter" class="form-control" onchange="this.form.submit()">
                    <?php 
                    $status_options = ['all'=>'Tất cả','pending'=>'Đang chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao hàng','completed'=>'Đã hoàn thành','cancelled'=>'Đã hủy'];
                    foreach($status_options as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($status_filter === $k ? 'selected' : '') ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Bảng đơn hàng -->
    <div class="table-container">
        <h2>Danh Sách Đơn Hàng</h2>
        <?php if (empty($paged_orders)) : ?>
            <p>Không có đơn hàng nào để hiển thị.</p>
        <?php else : ?>
        <table class="table order-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Tổng Tiền</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paged_orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['order_code'] ?: '#'.$order['id']) ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= number_format($order['total_amount']) ?>₫</td>
                    <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                    <td>
                        <span class="status-label status-<?= $order['status'] ?>">
                            <?= match ($order['status']) {
                                'pending' => 'Đang chờ xác nhận',
                                'confirmed', 'processing' => 'Đã xác nhận',
                                'shipping' => 'Đang giao hàng',
                                'completed' => 'Đã hoàn thành',
                                'cancelled' => 'Đã hủy',
                                default => 'Không rõ'
                            } ?>
                        </span>
                    </td>
                    <td class="action-buttons">
                        <a href="order_detail.php?order_code=<?= urlencode($order['order_code']) ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-search-plus"></i> Xem
                        </a>
                        <?php
                        $statuses = ['pending'=>'Đang chờ xác nhận','confirmed'=>'Đã xác nhận','shipping'=>'Đang giao hàng','completed'=>'Đã hoàn thành','cancelled'=>'Đã hủy'];
                        $is_final = in_array($order['status'], ['completed','cancelled']);
                        ?>
                        <select class="order-status-select form-control" data-order-id="<?= $order['id'] ?>" <?= $is_final ? 'disabled' : '' ?>>
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?= $key ?>" <?= ($order['status'] === $key) ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <div class="pagination-wrap">
    <?php if ($total_pages > 1): ?>
        <nav class="pagination-nav">
            <?php
            $window = 2; // Hiển thị 2 nút trang bên trái và 2 nút trang bên phải trang hiện tại

            // --- Nút Previous ---
            $prev_page = max(1, $page - 1);
            $prev_url = create_page_url($prev_page, $status_filter, $search_term);
            $prev_class = ($page == 1) ? ' disabled' : '';
            echo '<a href="'.$prev_url.'" class="page-link prev-next'.$prev_class.'" title="Trang trước"><i class="fas fa-angle-left"></i></a>';

            // --- Nút trang 1 ---
            $url_1 = create_page_url(1, $status_filter, $search_term);
            echo '<a href="'.$url_1.'" class="page-link ' . (1 == $page ? 'active' : '') . '">1</a>';

            // --- Dấu '...' đầu tiên ---
            // Hiển thị '...' nếu trang hiện tại cách trang 1 hơn 2 bước
            if ($page - $window > 2) {
                echo '<span class="page-link ellipsis">...</span>';
            }

            // --- Các nút ở giữa (Window) ---
            $start_i = max(2, $page - $window);
            $end_i = min($total_pages - 1, $page + $window);
            
            for ($i = $start_i; $i <= $end_i; $i++) {
                $url_i = create_page_url($i, $status_filter, $search_term);
                echo '<a href="'.$url_i.'" class="page-link ' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
            }

            // --- Dấu '...' cuối cùng ---
            // Hiển thị '...' nếu trang hiện tại cách trang cuối hơn 2 bước
            if ($page + $window < $total_pages - 1) {
                echo '<span class="page-link ellipsis">...</span>';
            }
            
            // --- Nút trang cuối cùng ---
            if ($total_pages > 1) {
                 $url_last = create_page_url($total_pages, $status_filter, $search_term);
                 echo '<a href="'.$url_last.'" class="page-link ' . ($total_pages == $page ? 'active' : '') . '">' . $total_pages . '</a>';
            }

            // --- Nút Next ---
            $next_page = min($total_pages, $page + 1);
            $next_url = create_page_url($next_page, $status_filter, $search_term);
            $next_class = ($page == $total_pages) ? ' disabled' : '';
            echo '<a href="'.$next_url.'" class="page-link prev-next'.$next_class.'" title="Trang sau"><i class="fas fa-angle-right"></i></a>';
            ?>
        </nav>
    <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const selects = document.querySelectorAll('.order-status-select');
    selects.forEach(select => {
        select.addEventListener('change', async function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;
            const statusLabel = this.closest('tr').querySelector('.status-label');

            try {
                const response = await fetch('<?= BASE_URL ?>backend/admin/update_order_status.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({order_id: orderId, new_status: newStatus})
                });
                const data = await response.json();
                if (data.success) {
                    // Cập nhật status-label
                    statusLabel.textContent = this.options[this.selectedIndex].text;
                    statusLabel.className = `status-label status-${newStatus}`;
                    if (['completed','cancelled'].includes(newStatus)) this.disabled = true;

                    // Hiển thị toast dựa trên màu trạng thái
                    showStatusToast(orderId, `Đơn hàng đã cập nhật trạng thái: ${statusLabel.textContent}`);
                } else showToast('Cập nhật thất bại.', 'error');
            } catch {
                showToast('Lỗi kết nối hoặc máy chủ.', 'error');
            }
        });
    });

function showStatusToast(orderId, message) {
    const statusLabel = document.querySelector(`.order-status-select[data-order-id="${orderId}"]`)
                             .closest('tr')
                             .querySelector('.status-label');
    if (!statusLabel) return;

    // Lấy statusKey chính xác, bỏ 'status-label'
    const statusKey = Array.from(statusLabel.classList)
                           .find(c => c.startsWith('status-') && c !== 'status-label')
                           ?.replace('status-', '')
                           .toLowerCase() || 'unknown';

    // Map màu nền + chữ theo CSS hiện có
    const statusStyles = {
        pending:   { bg: '#fff3cd', color: '#856404' },
        confirmed: { bg: '#e7f1ff', color: '#0d6efd' },
        processing:{ bg: '#e7f1ff', color: '#0d6efd' },
        shipping:  { bg: '#ede7f6', color: '#6f42c1' },
        completed: { bg: '#e8f9ed', color: 'var(--success)' },
        cancelled: { bg: '#fdeaea', color: 'var(--danger)' },
        unknown:   { bg: '#f0f0f0', color: '#000' }
    };

    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;

    // Áp dụng màu đồng bộ
    const style = statusStyles[statusKey] || statusStyles.unknown;
    toast.style.backgroundColor = style.bg;
    toast.style.color = style.color;

    // Animation mượt
    toast.style.opacity = 0;
    toast.style.transform = 'translateY(-20px)';
    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.transition = 'all 0.3s ease';
        toast.style.opacity = 1;
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = 0;
        toast.style.transform = 'translateY(-20px)';
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
}
});
</script>
