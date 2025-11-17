<?php
// FILE: statistics_api.php
// API trả về thống kê tổng quan: tổng đơn hàng, doanh thu, top sản phẩm, sản phẩm xem nhiều

header('Content-Type: application/json; charset=utf-8');

// Khởi tạo session và kiểm tra quyền admin
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Gọi các file cấu hình và module
require_once(__DIR__ . '/../core/config.php'); 
require_once(__DIR__ . '/statistics_queries.php');
require_once(__DIR__ . '/order_module.php');

try {
    // Kiểm tra kết nối DB
    if (!$conn) {
        throw new Exception("Không thể kết nối cơ sở dữ liệu.");
    }

    // Lấy thống kê tổng quan đơn hàng từ order_module.php
    $order_stats = get_order_stats($conn);

    // Chuẩn bị dữ liệu trả về
    $data = [
        'success'       => true,
        'totalRevenue'  => $order_stats['total_revenue'],   // Doanh thu chỉ tính đơn completed
        'totalOrders'   => $order_stats['all_orders'],      // Tổng số đơn hàng
        'topProducts'   => getTopSellingProducts($conn, 5),// Top 5 sản phẩm bán chạy
        'mostViewed'    => getMostViewedProducts($conn, 5),// Top 5 sản phẩm xem nhiều
        'revenueByDate' => getRevenueByDate($conn, 30)     // Doanh thu 30 ngày gần nhất
    ];

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
