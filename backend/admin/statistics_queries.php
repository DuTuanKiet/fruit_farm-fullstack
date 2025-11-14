<?php
// Kiểm tra cột tồn tại trong bảng
function columnExists($conn, $table, $column) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ? 
          AND COLUMN_NAME = ?
    ");
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    return $row && intval($row['cnt']) > 0;
}

// Tổng doanh thu
function getTotalRevenue($conn) {
    $sql = "SELECT SUM(total_amount) AS total_revenue FROM orders WHERE status = 'completed'";
    $res = $conn->query($sql);
    return ($res && ($row = $res->fetch_assoc())) ? (float)$row['total_revenue'] : 0.0;
}

// Tổng số đơn hàng đã hoàn thành
function getTotalOrders($conn) {
    $sql = "SELECT COUNT(*) AS total_orders FROM orders WHERE status = 'completed'";
    $res = $conn->query($sql);
    return ($res && ($row = $res->fetch_assoc())) ? (int)$row['total_orders'] : 0;
}

// Ví dụ: Top sản phẩm bán chạy
function getTopSellingProducts($conn, $limit = 5) {
    $sql = "
        SELECT p.id, p.name, SUM(od.quantity) AS total_sold
        FROM order_details od
        JOIN products p ON od.product_id = p.id
        JOIN orders ord ON od.order_id = ord.id
        WHERE ord.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    return [];
}

// Ví dụ: Sản phẩm được xem nhiều nhất
function getMostViewedProducts($conn, $limit = 5) {
    $sql = "
        SELECT p.id, p.name, COUNT(v.id) AS views
        FROM product_views v
        JOIN products p ON v.product_id = p.id
        GROUP BY p.id, p.name
        ORDER BY views DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    return [];
}

// Doanh thu theo ngày
function getRevenueByDate($conn, $days_back = 30) {
    $sql = "
        SELECT 
            DATE(order_date) AS day, 
            COALESCE(SUM(total_amount), 0) AS revenue
        FROM orders
        WHERE status = 'completed'
          AND order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY DATE(order_date)
        ORDER BY DATE(order_date) ASC
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $days_back);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    return [];
}
?>
