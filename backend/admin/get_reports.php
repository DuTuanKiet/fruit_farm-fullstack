<?php
// backend/admin/get_reports.php

// Trả dữ liệu JSON cho frontend
header('Content-Type: application/json; charset=utf-8');

// 🔗 Import cấu hình và các hàm thống kê
require_once(__DIR__ . '/../core/config.php');
require_once(__DIR__ . '/../statistics_queries.php');

try {
    // ✅ Gọi các hàm thống kê từ statistics_queries.php
    $totalRevenue   = getTotalRevenue($conn);
    $totalOrders    = getTotalOrders($conn);
    $topProducts    = getTopSellingProducts($conn, 5);
    $mostViewed     = getMostViewedProducts($conn, 5);
    $revenueByDate  = getRevenueByDate($conn);

    // ✅ Chuẩn bị dữ liệu phản hồi
    $response = [
        'success' => true,
        'totalRevenue' => number_format($totalRevenue) . ' VNĐ',
        'totalOrders' => $totalOrders,
        'topProducts' => $topProducts,
        'mostViewed' => $mostViewed,
        'revenueChart' => [
            'labels' => array_column($revenueByDate, 'day'),
            'data' => array_column($revenueByDate, 'revenue')
        ]
    ];

} catch (Exception $e) {
    // ❌ Nếu có lỗi, trả thông báo lỗi
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];

} finally {
    // ✅ Đảm bảo đóng kết nối CSDL
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}

// 🧾 Xuất JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>
