<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/admin/admin_auth.php';
require_once __DIR__ . '/../../backend/core/db_connect.php'; 
require_once __DIR__ . '/../../backend/admin/order_module.php';

// Lấy thống kê đơn hàng
$total_stats = get_order_stats($conn);

// Đảm bảo tất cả key tồn tại, tránh Undefined array key
$stats_keys = ['all_orders','pending_orders','confirmed_orders','shipping_orders','completed_orders','cancelled_orders','total_revenue'];
foreach ($stats_keys as $key) {
    if (!isset($total_stats[$key])) {
        $total_stats[$key] = 0;
    }
}

// Lấy filter và search
$status_filter = $_GET['status'] ?? 'all';
$search_term   = trim($_GET['search'] ?? '');

$limit  = 10;
$page   = max(1, intval($_GET['page_num'] ?? 1));
$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];
$params = [];
$types = "";

if ($status_filter !== 'all') {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if (!empty($search_term)) {
    $where[] = "customer_name LIKE ?";
    $params[] = "%$search_term%";
    $types .= "s";
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

    <div class="stats-grid">
        <div class="stat-card blue-bg">
            <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-box-archive"></i></div>
            <div class="stat-info">
                <p>Tổng số Đơn Hàng</p>
                <span class="stat-value"><?= number_format($total_stats['all_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card orange-bg">
            <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-hourglass-start"></i></div>
            <div class="stat-info">
                <p>Đơn chờ xác nhận</p>
                <span class="stat-value"><?= number_format($total_stats['pending_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card purple-bg">
        <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-check-double"></i></div>
        <div class="stat-info">
                <p>Đã xác nhận</p>
                <span class="stat-value"><?= number_format($total_stats['confirmed_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card teal-bg">
        <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-truck"></i></div>
        <div class="stat-info">
                <p>Đang giao</p>
                <span class="stat-value"><?= number_format($total_stats['shipping_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card green-bg">
            <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-info">
                <p>Đã hoàn thành</p>
                <span class="stat-value"><?= number_format($total_stats['completed_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card gray-bg">
        <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-xmark"></i></div>
        <div class="stat-info">
                <p>Đã hủy</p>
                <span class="stat-value"><?= number_format($total_stats['cancelled_orders']) ?></span>
            </div>
        </div>
        <div class="stat-card red-bg">
            <div class="stat-icon-wrapper white-bg"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="stat-info">
                <p>Tổng Doanh Thu</p>
                <span class="stat-value"><?= number_format($total_stats['total_revenue']) ?>₫</span>
            </div>
        </div>
    </div>

    <div class="shadow-card filter-search-container">
        <form action="admin_dashboard.php" method="GET" class="filter-form">
            <input type="hidden" name="page" value="orders">
            <div class="status-filter-group">
                <label for="status-filter">Trạng thái:</label>
                <select name="status" id="status-filter" class="form-control" onchange="this.form.submit()">
                    <option value="all" <?= ($status_filter === 'all' ? 'selected' : '') ?>>Tất cả</option>
                    <option value="pending" <?= ($status_filter === 'pending' ? 'selected' : '') ?>>Đang chờ xác nhận</option>
                    <option value="confirmed" <?= ($status_filter === 'confirmed' ? 'selected' : '') ?>>Đã xác nhận</option>
                    <option value="shipping" <?= ($status_filter === 'shipping' ? 'selected' : '') ?>>Đang giao hàng</option>
                    <option value="completed" <?= ($status_filter === 'completed' ? 'selected' : '') ?>>Đã hoàn thành</option>
                    <option value="cancelled" <?= ($status_filter === 'cancelled' ? 'selected' : '') ?>>Đã hủy</option>
                </select>
            </div>
        </form>
    </div>

    <div class="table-container">
        <h2>Danh Sách Đơn Hàng</h2>
        <?php if (empty($paged_orders)) : ?>
            <p>Không có đơn hàng nào để hiển thị.</p>
        <?php else : ?>
        <table class="table order-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Khách Hàng</th>
                    <th>Tổng Tiền</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng Thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paged_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
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
                        <a href="chitietdonhang.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> Xem
                        </a>

                        <?php
                        $statuses = [
                            'pending' => 'Đang chờ xác nhận',
                            'confirmed' => 'Đã xác nhận',
                            'shipping' => 'Đang giao hàng',
                            'completed' => 'Đã hoàn thành',
                            'cancelled' => 'Đã hủy'
                        ];
                        $is_final = in_array($order['status'], ['completed', 'cancelled']);
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

        <!-- PHÂN TRANG -->
        <div class="pagination-wrap">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=orders&status=<?= $status_filter ?>&page_num=<?= $i ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
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
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({order_id: orderId, new_status: newStatus})
                });
                const data = await response.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    statusLabel.textContent = this.options[this.selectedIndex].text;
                    statusLabel.className = `status-label status-${newStatus}`;
                    if (['completed','cancelled'].includes(newStatus)) this.disabled = true;
                } else {
                    showToast('Cập nhật thất bại.', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối hoặc máy chủ.', 'error');
            }
        });
    });

    function showToast(message, type) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
});
</script>
