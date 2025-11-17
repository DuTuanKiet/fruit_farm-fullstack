<?php

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db_connect.php';

/**
 * Lấy các chỉ số thống kê tổng quan về đơn hàng.
 *
 * @param mysqli|null $conn Đối tượng kết nối MySQLi.
 * @return array Mảng chứa tổng số đơn hàng, đơn theo trạng thái, và tổng doanh thu.
 */
function get_order_stats($conn) {
    $stats = [
        'all_orders'       => 0,
        'pending_orders'   => 0,
        'confirmed_orders' => 0,
        'shipping_orders'  => 0,
        'completed_orders' => 0,
        'cancelled_orders' => 0,
        'total_revenue'    => 0
    ];

    // 1️⃣ Tổng đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM orders");
    if ($stmt) {
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stats['all_orders'] = (int)($row['cnt'] ?? 0);
        $stmt->close();
    }

    // 2️⃣ Tổng đơn theo từng trạng thái
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

    // 3️⃣ Tổng doanh thu: **chỉ tính dựa trên status = 'completed'**, không lọc payment_status
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
 * Lấy danh sách đơn hàng đã lọc và tìm kiếm.
 *
 * @param mysqli|null $conn
 * @param string $status_filter Trạng thái ('all', 'pending', 'confirmed', ...).
 * @param string $search_term Chuỗi tìm kiếm (id, customer_name, order_code)
 * @return array
 */
function get_filtered_orders($conn, $status_filter, $search_term) {
    if (!$conn) return [];

    $sql = "SELECT id, order_code, customer_name, total_amount, status, order_date FROM orders";
    $where_clauses = [];

    // Lọc theo trạng thái
    if ($status_filter !== 'all') {
        $where_clauses[] = "status = ?";
    }

    // Tìm kiếm theo ID, customer_name hoặc order_code
    if (!empty($search_term)) {
        $where_clauses[] = "(CAST(id AS CHAR) LIKE ? OR customer_name LIKE ? OR order_code LIKE ?)";
    }

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(" AND ", $where_clauses);
    }

    $sql .= " ORDER BY order_date DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("SQL Error (get_filtered_orders): " . mysqli_error($conn));
        return [];
    }

    $types = '';
    $params = [];
    $search_term_db = "%" . $search_term . "%";

    if ($status_filter !== 'all') {
        $types .= 's';
        $params[] = $status_filter;
    }

    if (!empty($search_term)) {
        $types .= 'sss';
        $params[] = $search_term_db;
        $params[] = $search_term_db;
        $params[] = $search_term_db;
    }

    if (!empty($params)) {
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
}

/**
 * Chuyển trạng thái đơn hàng thành CSS class cho giao diện.
 *
 * @param string $status
 * @return string
 */
function get_status_badge_class($status) {
    return match ($status) {
        'pending' => 'status-pending',
        'confirmed' => 'status-confirmed', 
        'shipping' => 'status-shipping',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
        default => 'status-unknown'
    };
}
