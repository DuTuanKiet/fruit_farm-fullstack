<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../backend/admin/statistics_queries.php';

// DB connect
require_once __DIR__ . '/../../backend/core/db_connect.php';
require_once __DIR__ . '/../../backend/admin/order_module.php'; 

// Lấy dữ liệu để vẽ biểu đồ ban đầu
$revenueByDate = getRevenueByDate($conn) ?? [];
$order_stats = get_order_stats($conn);
$total_orders = $order_stats['all_orders'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Báo cáo & Thống kê</title>
   <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/report.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="toast-container" id="toast-container"></div>
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon-wrapper green">
      <i class="fa-solid fa-sack-dollar"></i>
    </div>
    <div class="stat-info">
      <p>Tổng doanh thu</p>
      <span id="totalRevenueValue">Đang tải...</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon-wrapper blue">
      <i class="fa-solid fa-box-archive"></i>
    </div>
    <div class="stat-info">
      <p>Tổng số đơn hàng</p>
      <span id="totalOrdersValue">Đang tải...</span>
    </div>
  </div>
</div>

<div class="report-main-grid">
  <div class="report-widget">
    <div class="widget-header">
      <h3><i class="fa-solid fa-chart-line"></i> Doanh thu theo ngày</h3>
    </div>
    <div class="chart-wrapper">
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <div class="report-widget">
    <div class="widget-header">
      <h3><i class="fa-solid fa-trophy"></i> Top Sản phẩm Bán chạy</h3>
    </div>
    <table class="styled-table">
      <thead>
        <tr>
          <th>Tên sản phẩm</th>
          <th style="text-align: right;">Đã bán</th>
        </tr>
      </thead>
      <tbody id="topProductsTableBody"></tbody>
    </table>

    <div class="widget-header" style="margin-top: 30px;">
      <h3><i class="fa-solid fa-eye"></i> Top Sản phẩm Xem nhiều</h3>
    </div>
    <table class="styled-table">
      <thead>
        <tr>
          <th>Tên sản phẩm</th>
          <th style="text-align: right;">Lượt xem</th>
        </tr>
      </thead>
      <tbody id="mostViewedTableBody"></tbody>
    </table>
  </div>
</div>

<script>
const initialChartData = {
  labels: <?php echo json_encode(array_column($revenueByDate, 'day')); ?>,
  revenues: <?php echo json_encode(array_column($revenueByDate, 'revenue')); ?>
};

// Khởi tạo ChartJS
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: initialChartData.labels,
    datasets: [{
      label: 'Doanh thu (VNĐ)',
      data: initialChartData.revenues,
      borderColor: '#0d6efd',
      backgroundColor: 'rgba(13, 110, 253, 0.1)',
      fill: true,
      tension: 0.4,
      borderWidth: 2
    }]
  },
  options: { responsive: true, maintainAspectRatio: false }
});

// Gọi API lấy dữ liệu khác
fetch("<?= BASE_URL ?>backend/admin/statistics_api.php")
  .then(res => res.json())
  .then(data => {
    console.log("API data:", data);
    
    document.getElementById("totalRevenueValue").textContent =
      new Intl.NumberFormat("vi-VN").format(data.totalRevenue) + " VNĐ";
      
    document.getElementById("totalOrdersValue").textContent = 
      new Intl.NumberFormat("vi-VN").format(data.totalOrders); // Thêm định dạng số

    const topProductsBody = document.getElementById("topProductsTableBody");
    topProductsBody.innerHTML = data.topProducts.map(p =>
      `<tr><td>${p.name}</td><td style="text-align:right">${p.total_sold}</td></tr>`
    ).join("");

    const mostViewedBody = document.getElementById("mostViewedTableBody");

    // Thêm kiểm tra dữ liệu tồn tại
    if (data.mostViewed && Array.isArray(data.mostViewed) && data.mostViewed.length > 0) {
      mostViewedBody.innerHTML = data.mostViewed.map(p =>
        `<tr><td>${p.name}</td><td style="text-align:right">${p.views}</td></tr>` 
      ).join("");
    } else {
        // Hiển thị thông báo nếu không có dữ liệu
        mostViewedBody.innerHTML = '<tr><td colspan="2">Chưa có dữ liệu lượt xem.</td></tr>';
    }
    
  })
  .catch(error => {
      console.error("Lỗi khi gọi API thống kê:", error);
      // Hiển thị toast thông báo lỗi cho Admin
      showToast("Không thể tải dữ liệu thống kê. Vui lòng kiểm tra console.", "error"); 
  });
</script>
</body>
</html>