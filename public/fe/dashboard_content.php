<?php
// Lấy các số liệu thống kê cơ bản với logic ĐÚNG

// Đếm tổng số người dùng
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];

// Đếm tổng số sản phẩm
$total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];

// Tính tổng doanh thu đã hoàn thành
$result_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc();
$total_sales = $result_sales['total'] ?? 0;

// Đếm số phản hồi đang chờ
$pending_feedback = $conn->query("SELECT COUNT(id) as count FROM feedback ")->fetch_assoc()['count'];

// ĐẾM SỐ ĐƠN HÀNG ĐANG CHỜ XỬ LÝ (Pending Orders)
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders")->fetch_assoc()['count']; 
?>
<div class="toast-container" id="toast-container"></div>
<div class="dashboard-cards">
    <a href="?page=accounts" class="card-link">
        <div class="card">
            <div class="card-info">
                <h3><?php echo $total_users; ?></h3>
                <p>Tổng người dùng</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-users"></i></div>
        </div>
    </a>

    <!-- Thẻ MỚI: Đơn hàng đang chờ xử lý -->
   <a href="?page=orders" class="card-link">
        <div class="card orange">
            <div class="card-info">
                <h3><?php echo $pending_orders; ?></h3>
                <p>Xem các đơn hàng</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-truck-fast"></i></div>
        </div>
    </a>

    <a href="?page=products" class="card-link">
        <div class="card green">
            <div class="card-info">
                <h3><?php echo $total_products; ?></h3>
                <p>Tổng sản phẩm</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-box-archive"></i></div>
        </div>
    </a>

    <a href="?page=reports" class="card-link">
        <div class="card yellow">
            <div class="card-info">
                <h3><?php echo number_format($total_sales); ?>₫</h3>
                <p>Thống kê</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        </div>
    </a>

    <a href="?page=feedback" class="card-link">
        <div class="card red">
            <div class="card-info">
                <h3><?php echo $pending_feedback; ?></h3>
                <p>Đang có phản hồi</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-message"></i></div>
        </div>
    </a>
</div>
