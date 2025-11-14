<?php
// Đường dẫn: /backend/admin/order_module.php
// File này chứa tất cả các hàm liên quan đến việc truy vấn dữ liệu đơn hàng.

// Yêu cầu các file cấu hình và kết nối DB
// Giả định /backend/admin/order_module.php -> lùi 2 cấp để vào /backend/core/
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db_connect.php'; 
/**
 * Lấy các chỉ số thống kê tổng quan về đơn hàng.
 *
 * @param mysqli|null $conn Đối tượng kết nối MySQLi.
 * @return array Mảng chứa tổng số đơn hàng, đơn pending, completed, và tổng doanh thu (chỉ tính đơn completed).
 */
// Lấy thống kê toàn bộ đơn hàng, phân theo trạng thái
function get_order_stats($conn) {
    // Khởi tạo mặc định
    $stats = [
        'all_orders'       => 0,
        'pending_orders'   => 0,
        'confirmed_orders' => 0,
        'shipping_orders'  => 0,
        'completed_orders' => 0,
        'cancelled_orders' => 0,
        'total_revenue'    => 0
    ];

    // 1. Tổng đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM orders");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stats['all_orders'] = (int)($row['cnt'] ?? 0);
        $stmt->close();
    }

    // 2. Tổng đơn theo từng trạng thái
    $status_map = [
        'pending'   => 'pending_orders',
        'confirmed' => 'confirmed_orders',
        'shipping'  => 'shipping_orders',
        'completed' => 'completed_orders',
        'cancelled' => 'cancelled_orders'
    ];

    foreach ($status_map as $status => $key) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM orders WHERE status = ?");
        if ($stmt) {
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stats[$key] = (int)($row['cnt'] ?? 0);
            $stmt->close();
        }
    }

    // 3. Tổng doanh thu
    $stmt = $conn->prepare("SELECT SUM(total_amount) AS total FROM orders WHERE status = 'completed'");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stats['total_revenue'] = (float)($row['total'] ?? 0);
        $stmt->close();
    }

    return $stats;
}

/**
 * Lấy danh sách đơn hàng đã được lọc và tìm kiếm.
 *
 * @param mysqli|null $conn Đối tượng kết nối MySQLi.
 * @param string $status_filter Trạng thái đơn hàng cần lọc ('all', 'pending', 'processing', etc.).
 * @param string $search_term Chuỗi tìm kiếm (Mã ĐH hoặc Tên KH).
 * @return array Danh sách đơn hàng.
 */
function get_filtered_orders($conn, $status_filter, $search_term) {
    if ($conn === null || $conn === false) {
        return [];
    }

    $sql = "SELECT id, customer_name, total_amount, status, order_date FROM orders";
    $where_clauses = [];

    // Lọc theo trạng thái
    if ($status_filter !== 'all') {
        $where_clauses[] = "status = ?";
    }

    // Tìm kiếm (theo id và customer_name, ép id thành chuỗi để tìm kiếm)
    if (!empty($search_term)) {
        // Dùng `CONCAT` hoặc `CAST` để tìm kiếm trên ID cũng như Tên
        $where_clauses[] = "(CAST(id AS CHAR) LIKE ? OR customer_name LIKE ?)";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    // Sắp xếp theo ngày đặt giảm dần
    $sql .= " ORDER BY order_date DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        $types = '';
        $params = [];
        $search_term_db = "%" . $search_term . "%"; // Chỉ cần tính toán một lần

        // 1. Thêm tham số cho Status Filter
        if ($status_filter !== 'all') {
            $types .= 's';
            $params[] = $status_filter;
        }

        // 2. Thêm tham số cho Search Term
        if (!empty($search_term)) {
            $types .= 'ss'; // 2 tham số: cho ID và cho customer_name
            $params[] = $search_term_db;
            $params[] = $search_term_db;
        }

        if (!empty($params)) {
            // Chuẩn bị mảng tham chiếu cho mysqli_stmt_bind_param
            $bind_params = array_merge([$types], $params);
            $refs = [];
            foreach ($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $refs));
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $orders;

    } else {
        error_log("SQL Error (get_filtered_orders): " . mysqli_error($conn));
        return [];
    }
}

/**
 * Hàm hỗ trợ chuyển trạng thái thành CSS class.
 *
 * @param string $status Tên trạng thái.
 * @return string CSS class.
 */
function get_status_badge_class($status) {
    return match ($status) {
        'pending' => 'status-pending',
        'processing' => 'status-processing',
        'shipping' => 'status-shipping',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
        default => 'status-unknown'
    };
}
