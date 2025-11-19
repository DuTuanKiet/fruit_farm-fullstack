<?php
// Lấy các số liệu thống kê cơ bản
$total_users = $conn->query("SELECT COUNT(id) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(id) as count FROM products")->fetch_assoc()['count'];
$result_sales = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch_assoc();
$pending_feedback = $conn->query("SELECT COUNT(id) as count FROM feedback")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(id) as count FROM orders")->fetch_assoc()['count']; 
$total_sales = $result_sales['total'] ?? 0;
?>

<div class="toast-container" id="toast-container"></div>

<!-- Thẻ thống kê -->
<div class="dashboard-cards">
    <a href="?page=accounts" class="card-link">
        <div class="card">
            <div class="card-info">
                <h3><?= $total_users ?></h3>
                <p>Tổng người dùng</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-users"></i></div>
        </div>
    </a>

    <a href="?page=orders" class="card-link">
        <div class="card orange">
            <div class="card-info">
                <h3><?= $pending_orders ?></h3>
                <p>Đơn hàng đang chờ</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-truck-fast"></i></div>
        </div>
    </a>

    <a href="?page=products" class="card-link">
        <div class="card green">
            <div class="card-info">
                <h3><?= $total_products ?></h3>
                <p>Tổng sản phẩm</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-box-archive"></i></div>
        </div>
    </a>

    <a href="?page=feedback" class="card-link">
        <div class="card red">
            <div class="card-info">
                <h3><?= $pending_feedback ?></h3>
                <p>Phản hồi</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-message"></i></div>
        </div>
    </a>

    <a href="?page=reports" class="card-link">
        <div class="card yellow">
            <div class="card-info">
                <h3><?= number_format($total_sales) ?>₫</h3>
                <p>Doanh thu</p>
            </div>
            <div class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        </div>
    </a>
</div>

<!-- Biểu đồ -->
<div class="charts-wrapper">
    <div class="chart-card">
        <h3>Doanh thu theo tháng</h3>
        <canvas id="salesChart" height="250"></canvas>
    </div>
    <div class="chart-card">
        <h3>Đơn hàng theo trạng thái</h3>
        <canvas id="ordersChart" height="250"></canvas>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu theo tháng
const ctxSales = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctxSales, {
    type: 'line',
    data: {
        labels: ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'],
        datasets: [{
            label: 'Doanh thu',
            data: [1200000, 1500000, 1000000, 1800000, 2000000, 1700000, 2200000, 2100000, 2300000, 2500000, 2700000, <?= $total_sales ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 35, 1)',
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Biểu đồ đơn hàng theo trạng thái
const ctxOrders = document.getElementById('ordersChart').getContext('2d');
const ordersChart = new Chart(ctxOrders, {
    type: 'doughnut',
    data: {
        labels: ['Chờ xác nhận','Đã xác nhận','Đang giao','Hoàn thành','Hủy'],
        datasets: [{
            label: 'Số đơn',
            data: [5, 8, 3, 12, 2],
            backgroundColor: ['#f0ad4e','#6f42c1','#17a2b8','#28a745','#dc3545']
        }]
    },
    options: { responsive: true }
});
</script>

<style>
body { 
    background: #f5f7fb; 
    font-family: "Segoe UI", sans-serif; 
    margin: 0; 
    padding: 0; 
}

/* ==================== Dashboard Cards ==================== */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card-link { 
    text-decoration: none; 
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    min-height: 140px; /* Đồng đều tất cả card */
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover { 
    transform: translateY(-4px); 
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}

.card-info h3 { 
    margin: 0; 
    font-size: 26px; 
    font-weight: 700; 
    line-height: 1.2;
    text-align: center;
}

.card-info p { 
    margin: 5px 0 0 0; 
    font-size: 14px; 
    color: #555; 
    text-align: center;
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis;
}

.card-icon {
    font-size: 28px; 
    color: #fff;
    width: 50px; 
    height: 50px; 
    border-radius: 50%; 
    display: flex; 
    align-items: center; 
    justify-content: center;
    margin-top: 12px;
}

/* Màu sắc card */
.card.green { background: #d4edda; color:#155724; }
.card.orange { background: #fff3cd; color:#856404; }
.card.yellow { background: #fff8d6; color:#856404; }
.card.red { background: #f8d7da; color:#721c24; }

/* ==================== Charts Wrapper ==================== */
.charts-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.chart-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 320px; /* Chiều cao cố định cho chart */
}

.chart-card h3 { 
    font-size: 16px; 
    font-weight: 600; 
    margin-bottom: 10px; 
    text-align: center; 
}

/* Canvas chart */
.chart-card canvas {
    width: 100% !important;
    height: 100% !important;
}
</style>
