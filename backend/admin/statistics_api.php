<?php
// FILE: statistics_api.php

header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . '/../core/config.php'); 
require_once(__DIR__ . '/statistics_queries.php');
require_once(__DIR__ . '/order_module.php');

try {
    // Lấy toàn bộ thống kê từ order_module.php
    $order_stats = get_order_stats($conn);

    $data = [
        'success'       => true,
        'totalRevenue'  => getTotalRevenue($conn),
        
        // bằng tổng số đơn hàng (all_orders) từ order_module.
        'totalOrders'   => $order_stats['all_orders'], 
        
        'topProducts'   => getTopSellingProducts($conn, 5),
        'mostViewed'    => getMostViewedProducts($conn, 5),
        'revenueByDate' => getRevenueByDate($conn, 30)
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