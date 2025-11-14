<?php
// =====================================================
// === PHẦN 0: CẤU HÌNH & KHỞI TẠO SESSION ============
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Kiểm tra đăng nhập admin ---
require_once __DIR__ . '/../../backend/admin/admin_auth.php';

// --- Kết nối CSDL ---
require_once __DIR__ . '/../../backend/core/db_connect.php';

// =====================================================
// === PHẦN 1: LOGIC BACKEND - XỬ LÝ FORM, ACTION ====
// =====================================================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';

    switch ($post_action) {
        case 'save_admin':
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = 'admin';

            $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                header('Location: ?page=accounts&action=add&error=exists');
                exit();
            }
            $stmt_check->close();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt_insert->bind_param("ssss", $username, $email, $hashed_password, $role);
            $stmt_insert->execute();
            $stmt_insert->close();

            header('Location: ?page=accounts&status=add_success');
            exit();

        case 'update_user':
            $id = (int)$_POST['id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'] ?? '';

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $hashed_password, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $id);
            }

            $stmt->execute();
            $stmt->close();

            header('Location: ?page=accounts&status=update_success');
            exit();
    }
}

// =====================================================
// === PHẦN 1.2: ACTION QUA GET (KHÔNG POST) =========
// =====================================================
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- Xử lý cho ACCOUNTS ---
if ($page === 'accounts' && $id > 0) {
    switch ($action) {
        case 'disable':
            if ($id != $_SESSION['user_id']) {
                $stmt = $conn->prepare("UPDATE users SET status = 'disabled' WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: ?page=accounts&status=disabled');
            exit();

        case 'enable':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            header('Location: ?page=accounts&status=enabled');
            exit();
    }
}

// --- Xử lý cho FEEDBACK ---
if ($page === 'feedback' && $id > 0) {
    switch ($action) {
        case 'delete':
            $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            header("Location: ?page=feedback&status=deleted");
            exit();

        case 'mark_read':
            header('Content-Type: application/json');
            $stmt = $conn->prepare("UPDATE feedback SET status = 'read' WHERE id = ? AND status = 'new'");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $stmt->close();
            $conn->close();

            echo json_encode(['success' => $success]);
            exit();
    }
}

// =====================================================
// === PHẦN 2: HIỂN THỊ GIAO DIỆN ADMIN ===============
// =====================================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fruit Farm</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/assets/css/admin.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="toast-container" id="toast-container"></div>
<div class="admin-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🥑 Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="?page=dashboard" class="<?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Trang chủ</a></li>
            <li><a href="?page=accounts" class="<?= $page == 'accounts' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Quản lý tài khoản</a></li>
            <li><a href="?page=products" class="<?= $page == 'products' ? 'active' : '' ?>"><i class="fa-solid fa-box-archive"></i> Quản lý sản phẩm</a></li>
            <li><a href="?page=orders" class="<?= $page == 'orders' ? 'active' : '' ?>"><i class="fas fa-box"></i> Quản lý đơn hàng</a></li>
            <li><a href="?page=reports" class="<?= $page == 'reports' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Quản lý thống kê</a></li>
            <li><a href="?page=feedback" class="<?= $page == 'feedback' ? 'active' : '' ?>"><i class="fa-solid fa-message"></i> Quản lý phản hồi</a></li>
        </ul>
    </div>

    <main class="main-content">
        <header class="main-header">
            <h1>
                <?php
                switch ($page) {
                    case 'orders': echo "Quản lý đơn hàng"; break;
                    case 'accounts': echo "Quản lý tài khoản"; break;
                    case 'products': echo "Quản lý sản phẩm"; break;
                    case 'reports': echo "Báo cáo & Thống kê"; break;
                    case 'feedback': echo "Quản lý phản hồi"; break;
                    default: echo "Trang chủ"; break;
                }
                ?>
            </h1>

            <div class="user-info">
                <span>Xin chào, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong></span>
                <a href="<?= BASE_URL ?>backend/auth/logout.php" class="logout-link-simple">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </header>

        <div class="content-wrapper">
            <?php
            switch ($page) {
                case 'accounts':
                    $action_view = $_GET['action'] ?? 'list';
                    switch ($action_view) {
                        case 'edit': include 'user_edit.php'; break;
                        case 'add': include 'user_add_admin.php'; break;
                        default: include 'accounts.php'; break;
                    }
                    break;
                case 'products': include 'products.php'; break;
                case 'reports': include 'reports.php'; break;
                case 'feedback': include 'feedback.php'; break;
                case 'orders': include 'order_management.php'; break;
                default: include 'dashboard_content.php'; break;
            }
            ?>
        </div>
    </main>
    </div>
</div>
<script>
  const BASE_URL = "<?= rtrim(BASE_URL, '/') ?>/";
  const BASE_URL_JS = BASE_URL; 
</script>
<script src="<?= BASE_URL ?>public/assets/js/script.js"></script>
</body>
</html>
</body>
</html>

