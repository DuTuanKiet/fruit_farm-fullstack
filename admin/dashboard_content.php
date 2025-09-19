<?php

// Lấy các số liệu thống kê cơ bản (đây là ví dụ, bạn sẽ cần các câu lệnh SQL thật)
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];
$total_sales = 0; // Cần bảng orders để tính
$pending_feedback = 0; // Cần bảng feedback để tính
?>
<div class="dashboard-cards">
    <div class="card">
        <div class="card-info">
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>
        <div class="card-icon"><i class="fa-solid fa-users"></i></div>
    </div>
     <div class="card green">
        <div class="card-info">
            <h3><?php echo $total_products; ?></h3>
            <p>Total Products</p>
        </div>
        <div class="card-icon"><i class="fa-solid fa-box-archive"></i></div>
    </div>
     <div class="card yellow">
        <div class="card-info">
            <h3><?php echo number_format($total_sales); ?>₫</h3>
            <p>Total Sales</p>
        </div>
        <div class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
    </div>
     <div class="card red">
        <div class="card-info">
            <h3><?php echo $pending_feedback; ?></h3>
            <p>Pending Feedback</p>
        </div>
        <div class="card-icon"><i class="fa-solid fa-message"></i></div>
    </div>
</div>