<?php
session_start();
require_once __DIR__ . '/../backend/core/config.php';

// Tạo sẵn để tránh kiểm trong trong if bị undefined
$total_orders = 0;
$total_pages = 1;
$result = null;
$requireLogin = false;

if (!isset($_SESSION['user_id'])) {
    $requireLogin = true;
} else {
    $user_id = $_SESSION['user_id'];

// ✅ THIẾT LẬP PHÂN TRANG
$orders_per_page = 9; // Giới hạn 9 đơn hàng mỗi trang

// Lấy số trang hiện tại từ URL, mặc định là trang 1
$current_page = (int) ($_GET['page'] ?? 1);
$offset = ($current_page - 1) * $orders_per_page;

// 1. Lấy TỔNG SỐ ĐƠN HÀNG
$count_sql = "SELECT COUNT(*) AS total FROM orders WHERE user_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $orders_per_page);
$count_stmt->close();

// 2. Lấy đơn hàng cho trang hiện tại
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $orders_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách đơn hàng của bạn - Fruit Farm</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/style.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/xemdonhang.css?v=2" />

</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<main style="padding-top: 100px;">
<div class="order-list" style="padding-top: 50px;"> 
    <h2>Danh Sách Đơn Hàng Của Bạn</h2>

    <?php if ($total_orders > 0): ?>
        
        <div class="order-grid"> 
            <?php while ($order = $result->fetch_assoc()): ?>
                <?php 
                    $status_class = 'status-' . strtolower($order['status']);
                ?>
                <div class="order-item">
                    
                    <div class="order-header">
                        <span class="order-id">Mã đơn hàng: #<?= $order['id'] ?></span>
                        <?php
                            $status_vn = match ($order['status']) {
                                'pending' => 'Đang chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'shipping' => 'Đang giao hàng',
                                'completed' => 'Đã hoàn thành',
                                'cancelled' => 'Đã hủy',
                                default => 'Không rõ'
                            };
                            ?>
                        <span class="order-status <?= $status_class ?>">
                            <?= $status_vn ?>
                        </span>
                    </div>

                    <div class="order-info">
                        <div>
                            <i class="fa fa-calendar"></i> Ngày đặt: 
                            <strong><?= date("d/m/Y H:i", strtotime($order['order_date'])) ?></strong>
                        </div>
                        <div class="total-amount-wrapper">
                            <i class="fa fa-coins"></i> Tổng tiền: 
                            <strong class="total-amount">
                                <?= number_format($order['total_amount'], 0, ',', '.') ?>₫
                            </strong>
                        </div>
                    </div>

                    <a class="btn-detail" href="xemchitietdonhang.php?order_id=<?= $order['id'] ?>">
                        <i class="fa fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            <?php endwhile; ?>
        </div> 

        <?php if ($total_pages > 1): ?>
<div class="pagination">
  <!-- Nút quay lại -->
  <?php if ($current_page > 1): ?>
      <a class="page-link" href="?page=<?= $current_page - 1 ?>"><i class="fa fa-chevron-left"></i></a>
  <?php else: ?>
      <span class="page-link disabled"><i class="fa fa-chevron-left"></i></span>
  <?php endif; ?>

  <!-- Các số trang -->
  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <?php if ($i == $current_page): ?>
          <span class="page-link active"><?= $i ?></span>
      <?php else: ?>
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      <?php endif; ?>
  <?php endfor; ?>

  <!-- Nút kế tiếp -->
  <?php if ($current_page < $total_pages): ?>
      <a class="page-link" href="?page=<?= $current_page + 1 ?>"><i class="fa fa-chevron-right"></i></a>
  <?php else: ?>
      <span class="page-link disabled"><i class="fa fa-chevron-right"></i></span>
  <?php endif; ?>
</div>
        <?php endif; ?>

        <?php else: ?>
            <div class="empty">
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="sanpham.php" class="btn-detail" style="max-width: 300px; margin: 25px auto 0;">
                    <i class="fa fa-shopping-basket"></i> Bắt đầu mua sắm
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>
<div class="toast-container" id="toast-container"></div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>